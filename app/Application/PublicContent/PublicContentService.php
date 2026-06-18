<?php

declare(strict_types=1);

namespace App\Application\PublicContent;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PublicContentService
{
    /**
     * @return array{groups: list<array<string, mixed>>, seo: array<string, mixed>}
     */
    public function categories(): array
    {
        return [
            'groups' => self::categoryGroups(),
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
        $items = collect(self::newsItems())
            ->sortByDesc('publishedAt')
            ->values();

        $paginator = $this->paginate($items, $page, $perPage);

        return [
            'items' => array_values($paginator->items()),
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
        $item = collect(self::newsItems())->firstWhere('slug', $slug);

        if (! is_array($item)) {
            return null;
        }

        $item['seo'] = $this->seo(
            title: (string) $item['title'],
            description: (string) $item['excerpt'],
            path: "/news/$slug",
            lastModifiedAt: (string) $item['publishedAt'],
        );

        return $item;
    }

    /**
     * @param  array{search?: string|null, status?: string|null, city?: string|null, category?: string|null, sort?: string|null, page?: int, perPage?: int}  $filters
     * @return array{items: list<array<string, mixed>>, pagination: array<string, int>, summary: array<string, int>, seo: array<string, mixed>}
     */
    public function appealsIndex(array $filters = []): array
    {
        $items = collect(self::appealItems())
            ->where('public', true)
            ->values();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $items = $items
                ->filter(fn (array $item): bool => Str::contains(
                    Str::lower((string) $item['title'].' '.(string) $item['description'].' '.(string) $item['city'].' '.(string) $item['category']),
                    Str::lower($search),
                ))
                ->values();
        }

        foreach (['status', 'city', 'category'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $items = $items
                ->filter(fn (array $item): bool => Str::lower((string) $item[$field]) === Str::lower($value))
                ->values();
        }

        $sort = (string) ($filters['sort'] ?? 'newest');
        $items = match ($sort) {
            'popular' => $items->sortByDesc('supportCount')->values(),
            'resolved' => $items->sortByDesc(fn (array $item): int => $item['status'] === 'resolved' ? 1 : 0)->values(),
            default => $items->sortByDesc('publishedAt')->values(),
        };

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(24, (int) ($filters['perPage'] ?? 6)));
        $paginator = $this->paginate($items, $page, $perPage);

        return [
            'items' => array_map(fn (array $item): array => $this->appealListItem($item), array_values($paginator->items())),
            'pagination' => $this->pagination($paginator),
            'summary' => [
                'publishedCount' => 1284,
                'resolvedCount' => 327,
                'activeCount' => 89,
                'supportCount' => 48216,
            ],
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
        $item = collect(self::appealItems())
            ->first(fn (array $appeal): bool => $appeal['slug'] === $slug && $appeal['public'] === true);

        if (! is_array($item)) {
            return null;
        }

        $item['seo'] = $this->seo(
            title: (string) $item['title'],
            description: (string) $item['excerpt'],
            path: "/appeals/$slug",
            lastModifiedAt: (string) $item['updatedAt'],
        );
        $item['commentsPreview'] = self::commentsByAppeal()[$slug] ?? [];

        return $item;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function appealComments(string $slug): ?array
    {
        if ($this->appealShow($slug) === null) {
            return null;
        }

        return self::commentsByAppeal()[$slug] ?? [];
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

        foreach (self::newsItems() as $item) {
            $urls[] = [
                'loc' => $this->absoluteUrl('/news/'.$item['slug']),
                'lastmod' => $item['publishedAt'],
            ];
        }

        foreach (self::appealItems() as $item) {
            if ($item['public'] !== true) {
                continue;
            }

            $urls[] = [
                'loc' => $this->absoluteUrl('/appeals/'.$item['slug']),
                'lastmod' => $item['updatedAt'],
            ];
        }

        return $urls;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return LengthAwarePaginator<array<string, mixed>>
     */
    private function paginate(Collection $items, int $page, int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items: $items->forPage($page, $perPage)->values()->all(),
            total: $items->count(),
            perPage: $perPage,
            currentPage: $page,
        );
    }

    /**
     * @param  LengthAwarePaginator<array<string, mixed>>  $paginator
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
     * @return array<string, mixed>
     */
    private function appealListItem(array $item): array
    {
        return [
            'id' => $item['id'],
            'slug' => $item['slug'],
            'title' => $item['title'],
            'status' => $item['status'],
            'statusLabel' => $item['statusLabel'],
            'city' => $item['city'],
            'district' => $item['district'],
            'category' => $item['category'],
            'publishedAt' => $item['publishedAt'],
            'supportCount' => $item['supportCount'],
            'viewsCount' => $item['viewsCount'],
            'commentsCount' => $item['commentsCount'],
            'imageUrl' => $item['imageUrl'],
            'excerpt' => $item['excerpt'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seo(string $title, string $description, string $path, string $robots = 'index,follow', ?string $lastModifiedAt = null): array
    {
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

    /**
     * @return list<array<string, mixed>>
     */
    private static function categoryGroups(): array
    {
        return [
            [
                'slug' => 'city',
                'title' => 'Дом и город',
                'categories' => [
                    ['id' => '00000000-0000-4000-8000-000000000101', 'slug' => 'zhkh', 'title' => 'ЖКХ', 'description' => 'Управляющие компании, подъезды, отопление, вода, лифты', 'icon' => 'home'],
                    ['id' => '00000000-0000-4000-8000-000000000102', 'slug' => 'roads', 'title' => 'Дороги', 'description' => 'Ямы, тротуары, освещение, дорожная безопасность', 'icon' => 'trend'],
                    ['id' => '00000000-0000-4000-8000-000000000103', 'slug' => 'improvement', 'title' => 'Благоустройство', 'description' => 'Дворы, парки, мусор, детские и спортивные площадки', 'icon' => 'users'],
                ],
            ],
            [
                'slug' => 'social',
                'title' => 'Социальная сфера',
                'categories' => [
                    ['id' => '00000000-0000-4000-8000-000000000201', 'slug' => 'healthcare', 'title' => 'Здравоохранение', 'description' => 'Поликлиники, больницы, лекарства, доступность помощи', 'icon' => 'shield'],
                    ['id' => '00000000-0000-4000-8000-000000000202', 'slug' => 'education', 'title' => 'Образование', 'description' => 'Школы, детские сады, колледжи, кружки и безопасность', 'icon' => 'book'],
                    ['id' => '00000000-0000-4000-8000-000000000203', 'slug' => 'veterans', 'title' => 'СВО / Ветераны', 'description' => 'Поддержка бойцов, ветеранов и их семей', 'icon' => 'flag'],
                ],
            ],
            [
                'slug' => 'rights',
                'title' => 'Права и безопасность',
                'categories' => [
                    ['id' => '00000000-0000-4000-8000-000000000301', 'slug' => 'citizen-rights', 'title' => 'Права граждан', 'description' => 'Защита прав, обращения в ведомства и суд', 'icon' => 'scale'],
                    ['id' => '00000000-0000-4000-8000-000000000302', 'slug' => 'corruption', 'title' => 'Коррупция', 'description' => 'Сообщения о злоупотреблениях и конфликте интересов', 'icon' => 'heart'],
                    ['id' => '00000000-0000-4000-8000-000000000303', 'slug' => 'other', 'title' => 'Другое', 'description' => 'Иные обращения, которые требуют общественного внимания', 'icon' => 'file'],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function newsItems(): array
    {
        return [
            [
                'id' => '00000000-0000-4000-8000-000000001001',
                'slug' => 'legal-support-consultations',
                'title' => 'Открыта запись на консультации по обращениям в суд',
                'excerpt' => 'Юристы помогают подготовить документы, уточнить сроки и выбрать порядок подачи жалобы.',
                'content' => "Команда проекта открыла предварительную запись на консультации для граждан, которым нужна помощь с обращениями в суд.\n\nСпециалисты помогают проверить документы, уточнить сроки и выбрать порядок подачи жалобы. Эта услуга предназначена для ситуаций, где обычное обращение в ведомство не дало результата или требуется дополнительная правовая оценка.",
                'publishedAt' => '2026-06-17T00:00:00+03:00',
                'category' => 'Правовая поддержка',
                'imageUrl' => '/assets/hero-legal-consultation.png',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000001002',
                'slug' => 'public-control-visits',
                'title' => 'Запущены выездные приемы по проблемам ЖКХ',
                'excerpt' => 'Команда собирает обращения жителей и фиксирует системные проблемы для публичного реестра.',
                'content' => "Выездные приемы помогают собрать обращения жителей на месте и быстрее понять масштаб проблемы.\n\nМатериалы передаются на проверку, после чего резонансные обращения могут попасть в публичный реестр.",
                'publishedAt' => '2026-06-14T00:00:00+03:00',
                'category' => 'Общественный контроль',
                'imageUrl' => '/assets/hero-community-meeting.png',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000001003',
                'slug' => 'may-appeals-results-report',
                'title' => 'Опубликован отчет по обращениям за май',
                'excerpt' => 'В отчете собраны статусы, решенные вопросы и обращения, которые требуют дополнительной проверки.',
                'content' => "Майский отчет показывает, какие обращения были решены, какие находятся в работе и где требуется дополнительная проверка.\n\nПубличная статистика помогает видеть реальные результаты и повторяющиеся проблемы.",
                'publishedAt' => '2026-06-10T00:00:00+03:00',
                'category' => 'Результаты',
                'imageUrl' => '/assets/hero-hands-heart.png',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function appealItems(): array
    {
        return [
            [
                'id' => '00000000-0000-4000-8000-000000002001',
                'slug' => 'entrance-roof-leak-after-rain',
                'title' => 'Протекает крыша в подъезде после дождя',
                'excerpt' => 'Жители просят проверить кровлю и работу управляющей компании.',
                'description' => 'После каждого сильного дождя вода попадает в подъезд, на стенах появилась сырость и плесень. Жители несколько раз обращались в управляющую компанию, но ремонт не начался.',
                'status' => 'checking',
                'statusLabel' => 'На проверке',
                'city' => 'Брянск',
                'district' => 'Советский район',
                'category' => 'ЖКХ',
                'location' => 'г. Брянск, Советский район',
                'publishedAt' => '2026-06-12T14:30:00+03:00',
                'updatedAt' => '2026-06-17T12:00:00+03:00',
                'supportCount' => 64,
                'viewsCount' => 1245,
                'commentsCount' => 23,
                'imageUrl' => '/assets/issue-entrance-roof.png',
                'public' => true,
                'attachments' => [
                    ['type' => 'image', 'url' => '/assets/issue-entrance-roof.png', 'title' => 'Фото протечки в подъезде'],
                ],
                'timeline' => [
                    ['status' => 'published', 'title' => 'Обращение опубликовано', 'date' => '2026-06-12T14:30:00+03:00', 'text' => 'Материалы прошли предварительную модерацию.'],
                    ['status' => 'checking', 'title' => 'Факты проверяются', 'date' => '2026-06-14T10:00:00+03:00', 'text' => 'Команда уточняет адрес и историю обращений в УК.'],
                ],
                'officialResponse' => ['title' => 'Официальный ответ ожидается', 'text' => 'Запрос готовится в ответственную организацию.', 'receivedAt' => null],
                'documents' => [
                    ['title' => 'Черновик обращения в УК', 'url' => '#'],
                ],
            ],
            [
                'id' => '00000000-0000-4000-8000-000000002002',
                'slug' => 'road-pits-after-repair',
                'title' => 'Ямы на дороге после ремонта',
                'excerpt' => 'После недавнего ремонта на дороге снова появились глубокие ямы.',
                'description' => 'Участок дороги был отремонтирован весной, но покрытие быстро разрушилось. Жители просят проверить качество работ и восстановить безопасный проезд.',
                'status' => 'active',
                'statusLabel' => 'В работе',
                'city' => 'Москва',
                'district' => 'ЮВАО',
                'category' => 'Дороги',
                'location' => 'г. Москва, ЮВАО',
                'publishedAt' => '2026-06-14T10:15:00+03:00',
                'updatedAt' => '2026-06-17T12:00:00+03:00',
                'supportCount' => 118,
                'viewsCount' => 2134,
                'commentsCount' => 37,
                'imageUrl' => '/assets/issue-road.png',
                'public' => true,
                'attachments' => [
                    ['type' => 'image', 'url' => '/assets/issue-road.png', 'title' => 'Фото дорожного покрытия'],
                ],
                'timeline' => [
                    ['status' => 'published', 'title' => 'Обращение опубликовано', 'date' => '2026-06-14T10:15:00+03:00', 'text' => 'Проблема добавлена в публичный реестр.'],
                    ['status' => 'active', 'title' => 'Запрос направлен', 'date' => '2026-06-15T09:00:00+03:00', 'text' => 'Материалы направлены для проверки качества ремонта.'],
                ],
                'officialResponse' => ['title' => 'Ответ в работе', 'text' => 'Ожидается реакция ответственного ведомства.', 'receivedAt' => null],
                'documents' => [
                    ['title' => 'Фотофиксация дорожных дефектов', 'url' => '#'],
                ],
            ],
            [
                'id' => '00000000-0000-4000-8000-000000002003',
                'slug' => 'construction-waste-in-yard',
                'title' => 'Свалка строительного мусора во дворе',
                'excerpt' => 'Во дворе долгое время не вывозят строительный мусор.',
                'description' => 'После ремонта рядом с домом осталась куча строительного мусора. Жители просят организовать вывоз и проверить подрядчика.',
                'status' => 'resolved',
                'statusLabel' => 'Решено',
                'city' => 'Санкт-Петербург',
                'district' => 'Невский район',
                'category' => 'Благоустройство',
                'location' => 'г. Санкт-Петербург, Невский район',
                'publishedAt' => '2026-06-05T11:20:00+03:00',
                'updatedAt' => '2026-06-16T12:00:00+03:00',
                'supportCount' => 41,
                'viewsCount' => 892,
                'commentsCount' => 18,
                'imageUrl' => '/assets/issue-sport-roof.png',
                'public' => true,
                'attachments' => [
                    ['type' => 'image', 'url' => '/assets/issue-sport-roof.png', 'title' => 'Фото дворовой территории'],
                ],
                'timeline' => [
                    ['status' => 'published', 'title' => 'Обращение опубликовано', 'date' => '2026-06-05T11:20:00+03:00', 'text' => 'Материалы приняты в работу.'],
                    ['status' => 'resolved', 'title' => 'Мусор вывезен', 'date' => '2026-06-16T12:00:00+03:00', 'text' => 'Жители подтвердили устранение проблемы.'],
                ],
                'officialResponse' => ['title' => 'Проблема устранена', 'text' => 'Подрядчик организовал вывоз мусора.', 'receivedAt' => '2026-06-16T12:00:00+03:00'],
                'documents' => [
                    ['title' => 'Ответ управляющей организации', 'url' => '#'],
                ],
            ],
            [
                'id' => '00000000-0000-4000-8000-000000002004',
                'slug' => 'private-draft-example',
                'title' => 'Непубличный черновик',
                'excerpt' => 'Не должен отдаваться публично.',
                'description' => 'Private.',
                'status' => 'draft',
                'statusLabel' => 'Черновик',
                'city' => 'Москва',
                'district' => 'ЦАО',
                'category' => 'Другое',
                'location' => 'Москва',
                'publishedAt' => '2026-06-01T00:00:00+03:00',
                'updatedAt' => '2026-06-01T00:00:00+03:00',
                'supportCount' => 0,
                'viewsCount' => 0,
                'commentsCount' => 0,
                'imageUrl' => '/assets/issue-road.png',
                'public' => false,
                'attachments' => [],
                'timeline' => [],
                'officialResponse' => null,
                'documents' => [],
            ],
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private static function commentsByAppeal(): array
    {
        return [
            'road-pits-after-repair' => [
                ['id' => '00000000-0000-4000-8000-000000003001', 'authorName' => 'Анна', 'status' => 'published', 'type' => 'public', 'comment' => 'Проблема действительно повторяется после каждого ремонта.', 'createdAt' => '2026-06-15T12:00:00+03:00', 'hasMedia' => false],
                ['id' => '00000000-0000-4000-8000-000000003002', 'authorName' => 'Команда Рука добра', 'status' => 'published', 'type' => 'official', 'comment' => 'Материалы направлены на проверку.', 'createdAt' => '2026-06-16T09:00:00+03:00', 'hasMedia' => false],
            ],
            'entrance-roof-leak-after-rain' => [
                ['id' => '00000000-0000-4000-8000-000000003003', 'authorName' => 'Житель дома', 'status' => 'published', 'type' => 'public', 'comment' => 'После дождя вода течет по стене второго этажа.', 'createdAt' => '2026-06-13T11:00:00+03:00', 'hasMedia' => true],
            ],
            'construction-waste-in-yard' => [
                ['id' => '00000000-0000-4000-8000-000000003004', 'authorName' => 'Мария', 'status' => 'published', 'type' => 'public', 'comment' => 'Спасибо, мусор действительно вывезли.', 'createdAt' => '2026-06-16T15:00:00+03:00', 'hasMedia' => false],
            ],
        ];
    }
}
