<?php

declare(strict_types=1);

namespace App\Application\PublicContent;

use App\Models\Advertisement;
use App\Models\AppealComment;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSlide;
use App\Models\NewsPost;
use App\Models\PublicAppeal;
use App\Models\PublicAppealAttachment;
use App\Models\PublicAppealDocument;
use App\Models\PublicAppealOfficialResponse;
use App\Models\PublicAppealTimelineItem;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class PublicContentService
{
    /**
     * @return array{slides: list<array<string, mixed>>, advertisements: list<array<string, mixed>>, categoryGroups: list<array<string, mixed>>, seo: array<string, mixed>}
     */
    public function home(): array
    {
        $now = now();

        return [
            'slides' => HomepageSlide::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get()
                ->map(fn (HomepageSlide $slide): array => $this->homepageSlidePayload($slide))
                ->values()
                ->all(),
            'advertisements' => Advertisement::query()
                ->where('placement', 'home_promo')
                ->where('is_active', true)
                ->where(function (Builder $query) use ($now): void {
                    $query
                        ->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $now);
                })
                ->where(function (Builder $query) use ($now): void {
                    $query
                        ->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $now);
                })
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get()
                ->map(fn (Advertisement $advertisement): array => $this->advertisementPayload($advertisement))
                ->values()
                ->all(),
            'categoryGroups' => CategoryGroup::query()
                ->with(['categories' => fn ($query) => $query->where('is_active', true)])
                ->where('is_active', true)
                ->whereHas('categories', function (Builder $query): void {
                    $query->where('is_active', true);
                })
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
                ->map(fn (CategoryGroup $group): array => $this->categoryGroupPayload($group))
                ->values()
                ->all(),
            'seo' => $this->seo(
                title: 'Рука добра - платформа обращений и жалоб граждан',
                description: 'Общественная платформа для обращений и жалоб граждан. Помощь, поддержка, общественный контроль и прозрачные результаты.',
                path: '/',
            ),
        ];
    }

    /**
     * @return array{groups: list<array<string, mixed>>, seo: array<string, mixed>}
     */
    public function categories(): array
    {
        $groups = CategoryGroup::query()
            ->with(['categories' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('categories', function (Builder $query): void {
                $query->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (CategoryGroup $group): array => $this->categoryGroupPayload($group))
            ->values()
            ->all();

        return [
            'groups' => $groups,
            'seo' => $this->seo(
                title: 'Все категории обращений',
                description: 'Полный список категорий обращений и жалоб.',
                path: '/categories',
            ),
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, pagination: array<string, int>, seo: array<string, mixed>}
     */
    public function newsIndex(int $page = 1, int $perPage = 6): array
    {
        $paginator = NewsPost::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->paginate(
                perPage: $perPage,
                page: $page,
            );

        return [
            'items' => $paginator->getCollection()
                ->map(fn (NewsPost $post): array => $this->newsPayload($post))
                ->values()
                ->all(),
            'pagination' => $this->pagination($paginator),
            'seo' => $this->seo(
                title: 'Новости',
                description: 'Новости проекта Рука добра: правовая поддержка, общественный контроль и результаты обращений.',
                path: '/news',
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function newsShow(string $slug): ?array
    {
        $post = NewsPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $post instanceof NewsPost) {
            return null;
        }

        $payload = $this->newsPayload($post);
        $payload['seo'] = $this->seo(
            title: (string) $post->title,
            description: (string) $post->excerpt,
            path: "/news/$slug",
            lastModifiedAt: $this->nullableAtom($post->updated_at),
        );

        return $payload;
    }

    /**
     * @param  array{search?: string|null, status?: string|null, city?: string|null, category?: string|null, sort?: string|null, page?: int, perPage?: int}  $filters
     * @return array{items: list<array<string, mixed>>, pagination: array<string, int>, summary: array<string, int>, seo: array<string, mixed>}
     */
    public function appealsIndex(array $filters = []): array
    {
        $query = PublicAppeal::query()
            ->where('is_public', true);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $like = "%$search%";

                $query
                    ->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('category', 'like', $like);
            });
        }

        foreach (['status', 'city', 'category'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $query->where($field, $value);
        }

        $sort = (string) ($filters['sort'] ?? 'newest');
        match ($sort) {
            'popular' => $query->orderByDesc('support_count')->orderByDesc('published_at'),
            'resolved' => $query->orderByRaw("status = 'resolved' desc")->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(24, (int) ($filters['perPage'] ?? 6)));
        $paginator = $query->paginate(
            perPage: $perPage,
            page: $page,
        );

        return [
            'items' => $paginator->getCollection()
                ->map(fn (PublicAppeal $appeal): array => $this->appealListItem($appeal))
                ->values()
                ->all(),
            'pagination' => $this->pagination($paginator),
            'summary' => $this->appealsSummary(),
            'seo' => $this->seo(
                title: 'Все обращения и жалобы',
                description: 'Публичный реестр обращений и жалоб граждан с фильтрами по статусу, городу и категории.',
                path: '/appeals',
                robots: $this->hasPublicAppealFilters($filters) ? 'noindex,follow' : 'index,follow',
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function appealShow(string $slug): ?array
    {
        $appeal = PublicAppeal::query()
            ->with(['attachments', 'timelineItems', 'documents', 'officialResponse'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->first();

        if (! $appeal instanceof PublicAppeal) {
            return null;
        }

        $payload = [
            ...$this->appealListItem($appeal),
            'description' => $appeal->description,
            'location' => $appeal->location,
            'updatedAt' => $this->atom($appeal->updated_at),
            'attachments' => $appeal->attachments
                ->map(fn (PublicAppealAttachment $attachment): array => $this->attachmentPayload($attachment))
                ->values()
                ->all(),
            'timeline' => $appeal->timelineItems
                ->map(fn (PublicAppealTimelineItem $timelineItem): array => $this->timelinePayload($timelineItem))
                ->values()
                ->all(),
            'officialResponse' => $appeal->officialResponse instanceof PublicAppealOfficialResponse
                ? $this->officialResponsePayload($appeal->officialResponse)
                : null,
            'documents' => $appeal->documents
                ->map(fn (PublicAppealDocument $document): array => $this->documentPayload($document))
                ->values()
                ->all(),
            'commentsPreview' => $this->appealComments($slug) ?? [],
        ];

        $payload['seo'] = $this->seo(
            title: (string) $appeal->title,
            description: (string) $appeal->excerpt,
            path: "/appeals/$slug",
            lastModifiedAt: $this->nullableAtom($appeal->updated_at),
        );

        return $payload;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function appealComments(string $slug): ?array
    {
        $exists = PublicAppeal::query()
            ->where('slug', $slug)
            ->where('is_public', true)
            ->exists();

        if (! $exists) {
            return null;
        }

        return AppealComment::query()
            ->with('user')
            ->where('appeal_slug', $slug)
            ->latest()
            ->get()
            ->map(fn (AppealComment $comment): array => $this->commentPayload($comment))
            ->values()
            ->all();
    }

    /**
     * @return list<array{loc: string, lastmod: string|null}>
     */
    public function sitemapUrls(): array
    {
        $urls = [
            ['loc' => $this->absoluteUrl('/'), 'lastmod' => null],
            ['loc' => $this->absoluteUrl('/categories'), 'lastmod' => null],
            ['loc' => $this->absoluteUrl('/news'), 'lastmod' => null],
            ['loc' => $this->absoluteUrl('/appeals'), 'lastmod' => null],
            ['loc' => $this->absoluteUrl('/privacy'), 'lastmod' => null],
            ['loc' => $this->absoluteUrl('/agreement'), 'lastmod' => null],
            ['loc' => $this->absoluteUrl('/book'), 'lastmod' => null],
        ];

        NewsPost::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->get()
            ->each(function (NewsPost $post) use (&$urls): void {
                $urls[] = [
                    'loc' => $this->absoluteUrl('/news/'.$post->slug),
                    'lastmod' => $this->nullableAtom($post->updated_at),
                ];
            });

        PublicAppeal::query()
            ->where('is_public', true)
            ->orderByDesc('published_at')
            ->get()
            ->each(function (PublicAppeal $appeal) use (&$urls): void {
                $urls[] = [
                    'loc' => $this->absoluteUrl('/appeals/'.$appeal->slug),
                    'lastmod' => $this->nullableAtom($appeal->updated_at),
                ];
            });

        return $urls;
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryGroupPayload(CategoryGroup $group): array
    {
        return [
            'slug' => $group->slug,
            'title' => $group->title,
            'categories' => $group->categories
                ->map(fn (Category $category): array => $this->categoryPayload($category))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryPayload(Category $category): array
    {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'title' => $category->title,
            'description' => $category->description,
            'icon' => $category->icon,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function homepageSlidePayload(HomepageSlide $slide): array
    {
        return [
            'id' => $slide->id,
            'slug' => $slide->slug,
            'label' => $slide->label,
            'title' => $slide->title,
            'lead' => $slide->lead,
            'note' => $slide->note,
            'imageUrl' => $slide->image_url,
            'primaryCtaLabel' => $slide->primary_cta_label,
            'primaryCtaUrl' => $slide->primary_cta_url,
            'secondaryCtaLabel' => $slide->secondary_cta_label,
            'secondaryCtaUrl' => $slide->secondary_cta_url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function advertisementPayload(Advertisement $advertisement): array
    {
        return [
            'id' => $advertisement->id,
            'slug' => $advertisement->slug,
            'placement' => $advertisement->placement,
            'title' => $advertisement->title,
            'label' => $advertisement->label,
            'description' => $advertisement->description,
            'imageUrl' => $advertisement->image_url,
            'alt' => $advertisement->alt,
            'targetUrl' => $advertisement->target_url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsPayload(NewsPost $post): array
    {
        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'publishedAt' => $this->atom($post->published_at),
            'category' => $post->category,
            'imageUrl' => $post->image_url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appealListItem(PublicAppeal $appeal): array
    {
        return [
            'id' => $appeal->id,
            'slug' => $appeal->slug,
            'title' => $appeal->title,
            'status' => $appeal->status,
            'statusLabel' => $appeal->status_label,
            'city' => $appeal->city,
            'district' => $appeal->district,
            'category' => $appeal->category,
            'publishedAt' => $this->atom($appeal->published_at),
            'supportCount' => $appeal->support_count,
            'viewsCount' => $appeal->views_count,
            'commentsCount' => $appeal->comments_count,
            'imageUrl' => $appeal->image_url,
            'excerpt' => $appeal->excerpt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attachmentPayload(PublicAppealAttachment $attachment): array
    {
        return [
            'type' => $attachment->type,
            'url' => $attachment->url,
            'title' => $attachment->title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function timelinePayload(PublicAppealTimelineItem $timelineItem): array
    {
        return [
            'status' => $timelineItem->status,
            'title' => $timelineItem->title,
            'date' => $this->atom($timelineItem->happened_at),
            'text' => $timelineItem->text,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function officialResponsePayload(PublicAppealOfficialResponse $response): array
    {
        return [
            'title' => $response->title,
            'text' => $response->text,
            'receivedAt' => $this->nullableAtom($response->received_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function documentPayload(PublicAppealDocument $document): array
    {
        return [
            'title' => $document->title,
            'url' => $document->url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commentPayload(AppealComment $comment): array
    {
        $user = $comment->user;

        return [
            'id' => $comment->id,
            'appealSlug' => $comment->appeal_slug,
            'authorName' => $user instanceof User ? $user->name : 'Пользователь',
            'status' => $comment->status,
            'type' => $comment->type,
            'comment' => $comment->comment,
            'createdAt' => $this->atom($comment->created_at),
            'hasMedia' => (bool) $comment->has_media,
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return array{currentPage: int, perPage: int, total: int, lastPage: int}
     */
    private function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    /**
     * @return array{publishedCount: int, resolvedCount: int, activeCount: int, supportCount: int}
     */
    private function appealsSummary(): array
    {
        $query = PublicAppeal::query()->where('is_public', true);

        return [
            'publishedCount' => (clone $query)->count(),
            'resolvedCount' => (clone $query)->where('status', 'resolved')->count(),
            'activeCount' => (clone $query)->where('status', 'active')->count(),
            'supportCount' => (int) (clone $query)->sum('support_count'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seo(
        string $title,
        string $description,
        string $path,
        string $robots = 'index,follow',
        ?string $lastModifiedAt = null,
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => $this->absoluteUrl($path),
            'robots' => $robots,
            'ogImageUrl' => null,
            'lastModifiedAt' => $lastModifiedAt,
        ];
    }

    private function absoluteUrl(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function hasPublicAppealFilters(array $filters): bool
    {
        foreach (['search', 'status', 'city', 'category', 'sort'] as $field) {
            if (trim((string) ($filters[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function atom(DateTimeInterface|string|null $date): string
    {
        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date)->toAtomString();
        }

        if (is_string($date)) {
            return Carbon::parse($date)->toAtomString();
        }

        return now()->toAtomString();
    }

    private function nullableAtom(DateTimeInterface|string|null $date): ?string
    {
        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date)->toAtomString();
        }

        if (is_string($date)) {
            return Carbon::parse($date)->toAtomString();
        }

        return null;
    }
}
