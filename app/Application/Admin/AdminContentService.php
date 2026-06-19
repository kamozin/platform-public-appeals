<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSlide;
use App\Models\NewsPost;
use App\Models\PublicAppeal;
use App\Models\PublicAppealAttachment;
use App\Models\PublicAppealDocument;
use App\Models\PublicAppealOfficialResponse;
use App\Models\PublicAppealTimelineItem;
use App\Support\Api\ApiProblemException;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class AdminContentService
{
    /**
     * @return array{groups: list<array<string, mixed>>}
     */
    public function categories(): array
    {
        return [
            'groups' => CategoryGroup::query()
                ->with('categories')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
                ->map(fn (CategoryGroup $group): array => $this->categoryGroupPayload($group))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createCategory(array $payload): array
    {
        $group = $this->resolveCategoryGroup($payload);

        $category = Category::query()->create([
            'category_group_id' => $group->id,
            'slug' => $payload['slug'],
            'title' => $payload['title'],
            'description' => $payload['description'],
            'icon' => $payload['icon'] ?? 'file',
            'sort_order' => $payload['sort_order'] ?? 0,
            'is_active' => $payload['is_active'] ?? true,
        ]);

        return $this->categoryPayload($category->load('group'));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateCategory(string $id, array $payload): array
    {
        $category = $this->findCategory($id);
        $values = [];

        foreach (['slug', 'title', 'description', 'icon', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $payload)) {
                $values[$field] = $payload[$field];
            }
        }

        if (array_key_exists('group_slug', $payload)) {
            $values['category_group_id'] = $this->resolveCategoryGroup($payload)->id;
        }

        $category->update($values);

        return $this->categoryPayload($category->refresh()->load('group'));
    }

    public function deleteCategory(string $id): void
    {
        $this->findCategory($id)->delete();
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function news(): array
    {
        return [
            'items' => NewsPost::query()
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (NewsPost $post): array => $this->newsPayload($post))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createNews(array $payload): array
    {
        $post = NewsPost::query()->create($this->newsValues($payload));

        return $this->newsPayload($post->refresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateNews(string $id, array $payload): array
    {
        $post = $this->findNews($id);
        $post->update($this->newsValues($payload, $post));

        return $this->newsPayload($post->refresh());
    }

    public function deleteNews(string $id): void
    {
        $this->findNews($id)->delete();
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function appeals(): array
    {
        return [
            'items' => PublicAppeal::query()
                ->with(['attachments', 'timelineItems', 'documents', 'officialResponse'])
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (PublicAppeal $appeal): array => $this->appealPayload($appeal))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createAppeal(array $payload): array
    {
        $appeal = PublicAppeal::query()->create($this->appealValues($payload));
        $this->syncAppealRelations($appeal, $payload);

        return $this->appealPayload($appeal->refresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateAppeal(string $id, array $payload): array
    {
        $appeal = $this->findAppeal($id);
        $appeal->update($this->appealValues($payload, $appeal));
        $this->syncAppealRelations($appeal, $payload);

        return $this->appealPayload($appeal->refresh());
    }

    public function deleteAppeal(string $id): void
    {
        $this->findAppeal($id)->delete();
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function homepageSlides(): array
    {
        return [
            'items' => HomepageSlide::query()
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get()
                ->map(fn (HomepageSlide $slide): array => $this->homepageSlidePayload($slide))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createHomepageSlide(array $payload): array
    {
        $slide = HomepageSlide::query()->create($this->homepageSlideValues($payload));

        return $this->homepageSlidePayload($slide->refresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateHomepageSlide(string $id, array $payload): array
    {
        $slide = $this->findHomepageSlide($id);
        $slide->update($this->homepageSlideValues($payload));

        return $this->homepageSlidePayload($slide->refresh());
    }

    public function deleteHomepageSlide(string $id): void
    {
        $this->findHomepageSlide($id)->delete();
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function advertisements(): array
    {
        return [
            'items' => Advertisement::query()
                ->orderBy('placement')
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get()
                ->map(fn (Advertisement $advertisement): array => $this->advertisementPayload($advertisement))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createAdvertisement(array $payload): array
    {
        $advertisement = Advertisement::query()->create($this->advertisementValues($payload));

        return $this->advertisementPayload($advertisement->refresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateAdvertisement(string $id, array $payload): array
    {
        $advertisement = $this->findAdvertisement($id);
        $advertisement->update($this->advertisementValues($payload));

        return $this->advertisementPayload($advertisement->refresh());
    }

    public function deleteAdvertisement(string $id): void
    {
        $this->findAdvertisement($id)->delete();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveCategoryGroup(array $payload): CategoryGroup
    {
        $slug = (string) $payload['group_slug'];
        $fallbackTitle = Str::of($slug)->replace('-', ' ')->headline()->toString();
        $title = (string) ($payload['group_title'] ?? $fallbackTitle);

        $group = CategoryGroup::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        if (array_key_exists('group_title', $payload)) {
            $group->update(['title' => $title]);
        }

        return $group;
    }

    private function findCategory(string $id): Category
    {
        $category = Category::query()->find($id);

        if (! $category instanceof Category) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $category;
    }

    private function findNews(string $id): NewsPost
    {
        $post = NewsPost::query()->find($id);

        if (! $post instanceof NewsPost) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $post;
    }

    private function findAppeal(string $id): PublicAppeal
    {
        $appeal = PublicAppeal::query()
            ->with(['attachments', 'timelineItems', 'documents', 'officialResponse'])
            ->find($id);

        if (! $appeal instanceof PublicAppeal) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $appeal;
    }

    private function findHomepageSlide(string $id): HomepageSlide
    {
        $slide = HomepageSlide::query()->find($id);

        if (! $slide instanceof HomepageSlide) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $slide;
    }

    private function findAdvertisement(string $id): Advertisement
    {
        $advertisement = Advertisement::query()->find($id);

        if (! $advertisement instanceof Advertisement) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $advertisement;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function newsValues(array $payload, ?NewsPost $post = null): array
    {
        $values = [];

        foreach (['slug', 'title', 'excerpt', 'content', 'category', 'image_url', 'status', 'published_at'] as $field) {
            if (array_key_exists($field, $payload)) {
                $values[$field] = $payload[$field];
            }
        }

        $status = (string) ($values['status'] ?? 'draft');
        $publishedAt = $values['published_at'] ?? null;

        if ($post instanceof NewsPost) {
            $status = (string) ($values['status'] ?? $post->status);
            $publishedAt = $values['published_at'] ?? $post->published_at;
        }

        if ($status === 'published' && $publishedAt === null) {
            $values['published_at'] = now();
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function appealValues(array $payload, ?PublicAppeal $appeal = null): array
    {
        $values = [];

        foreach (
            [
                'slug',
                'title',
                'excerpt',
                'description',
                'status',
                'status_label',
                'city',
                'district',
                'category',
                'location',
                'published_at',
                'support_count',
                'views_count',
                'comments_count',
                'image_url',
                'is_public',
            ] as $field
        ) {
            if (array_key_exists($field, $payload)) {
                $values[$field] = $payload[$field];
            }
        }

        $isPublic = (bool) ($values['is_public'] ?? false);
        $publishedAt = $values['published_at'] ?? null;

        if ($appeal instanceof PublicAppeal) {
            $isPublic = (bool) ($values['is_public'] ?? $appeal->is_public);
            $publishedAt = $values['published_at'] ?? $appeal->published_at;
        }

        if ($isPublic && $publishedAt === null) {
            $values['published_at'] = now();
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function homepageSlideValues(array $payload): array
    {
        $values = [];

        foreach (
            [
                'slug',
                'label',
                'title',
                'lead',
                'note',
                'image_url',
                'primary_cta_label',
                'primary_cta_url',
                'secondary_cta_label',
                'secondary_cta_url',
                'sort_order',
                'is_active',
            ] as $field
        ) {
            if (array_key_exists($field, $payload)) {
                $values[$field] = $payload[$field];
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function advertisementValues(array $payload): array
    {
        $values = [];

        foreach (
            [
                'slug',
                'placement',
                'title',
                'label',
                'description',
                'image_url',
                'alt',
                'target_url',
                'sort_order',
                'starts_at',
                'ends_at',
                'is_active',
            ] as $field
        ) {
            if (array_key_exists($field, $payload)) {
                $values[$field] = $payload[$field];
            }
        }

        if (! array_key_exists('placement', $values)) {
            $values['placement'] = 'home_promo';
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function syncAppealRelations(PublicAppeal $appeal, array $payload): void
    {
        if (array_key_exists('attachments', $payload)) {
            $appeal->attachments()->delete();

            foreach ($payload['attachments'] as $index => $attachment) {
                $appeal->attachments()->create([
                    ...$attachment,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if (array_key_exists('timeline', $payload)) {
            $appeal->timelineItems()->delete();

            foreach ($payload['timeline'] as $index => $timelineItem) {
                $appeal->timelineItems()->create([
                    ...$timelineItem,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if (array_key_exists('documents', $payload)) {
            $appeal->documents()->delete();

            foreach ($payload['documents'] as $index => $document) {
                $appeal->documents()->create([
                    ...$document,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if (! array_key_exists('official_response', $payload)) {
            return;
        }

        if ($payload['official_response'] === null) {
            $appeal->officialResponse()->delete();

            return;
        }

        $appeal->officialResponse()->updateOrCreate(
            ['public_appeal_id' => $appeal->id],
            $payload['official_response'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryGroupPayload(CategoryGroup $group): array
    {
        return [
            'id' => $group->id,
            'slug' => $group->slug,
            'title' => $group->title,
            'sortOrder' => $group->sort_order,
            'isActive' => (bool) $group->is_active,
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
        $group = $category->group;

        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'title' => $category->title,
            'description' => $category->description,
            'icon' => $category->icon,
            'sortOrder' => $category->sort_order,
            'isActive' => (bool) $category->is_active,
            'group' => $group instanceof CategoryGroup ? [
                'id' => $group->id,
                'slug' => $group->slug,
                'title' => $group->title,
            ] : null,
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
            'category' => $post->category,
            'imageUrl' => $post->image_url,
            'status' => $post->status,
            'publishedAt' => $this->nullableAtom($post->published_at),
            'createdAt' => $this->nullableAtom($post->created_at),
            'updatedAt' => $this->nullableAtom($post->updated_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appealPayload(PublicAppeal $appeal): array
    {
        $appeal->loadMissing(['attachments', 'timelineItems', 'documents', 'officialResponse']);

        return [
            'id' => $appeal->id,
            'slug' => $appeal->slug,
            'title' => $appeal->title,
            'excerpt' => $appeal->excerpt,
            'description' => $appeal->description,
            'status' => $appeal->status,
            'statusLabel' => $appeal->status_label,
            'city' => $appeal->city,
            'district' => $appeal->district,
            'category' => $appeal->category,
            'location' => $appeal->location,
            'publishedAt' => $this->nullableAtom($appeal->published_at),
            'supportCount' => $appeal->support_count,
            'viewsCount' => $appeal->views_count,
            'commentsCount' => $appeal->comments_count,
            'imageUrl' => $appeal->image_url,
            'isPublic' => (bool) $appeal->is_public,
            'attachments' => $appeal->attachments
                ->map(fn (PublicAppealAttachment $attachment): array => [
                    'type' => $attachment->type,
                    'url' => $attachment->url,
                    'title' => $attachment->title,
                ])
                ->values()
                ->all(),
            'timeline' => $appeal->timelineItems
                ->map(fn (PublicAppealTimelineItem $timelineItem): array => [
                    'status' => $timelineItem->status,
                    'title' => $timelineItem->title,
                    'date' => $this->nullableAtom($timelineItem->happened_at),
                    'text' => $timelineItem->text,
                ])
                ->values()
                ->all(),
            'documents' => $appeal->documents
                ->map(fn (PublicAppealDocument $document): array => [
                    'title' => $document->title,
                    'url' => $document->url,
                ])
                ->values()
                ->all(),
            'officialResponse' => $appeal->officialResponse instanceof PublicAppealOfficialResponse
                ? [
                    'title' => $appeal->officialResponse->title,
                    'text' => $appeal->officialResponse->text,
                    'receivedAt' => $this->nullableAtom($appeal->officialResponse->received_at),
                ]
                : null,
            'createdAt' => $this->nullableAtom($appeal->created_at),
            'updatedAt' => $this->nullableAtom($appeal->updated_at),
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
            'sortOrder' => $slide->sort_order,
            'isActive' => (bool) $slide->is_active,
            'createdAt' => $this->nullableAtom($slide->created_at),
            'updatedAt' => $this->nullableAtom($slide->updated_at),
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
            'sortOrder' => $advertisement->sort_order,
            'startsAt' => $this->nullableAtom($advertisement->starts_at),
            'endsAt' => $this->nullableAtom($advertisement->ends_at),
            'isActive' => (bool) $advertisement->is_active,
            'createdAt' => $this->nullableAtom($advertisement->created_at),
            'updatedAt' => $this->nullableAtom($advertisement->updated_at),
        ];
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
