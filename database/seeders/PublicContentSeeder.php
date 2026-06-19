<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\AppealComment;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSlide;
use App\Models\NewsPost;
use App\Models\PublicAppeal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

final class PublicContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password123'),
                'is_admin' => true,
                'notifications_enabled' => true,
            ],
        );

        $this->seedCategories();
        $this->seedHomepageSlides();
        $this->seedAdvertisements();
        $this->seedNews();
        $this->seedAppeals();
        $this->seedComments($admin);
    }

    private function seedCategories(): void
    {
        foreach ($this->categoryGroups() as $groupIndex => $groupPayload) {
            $group = CategoryGroup::query()->updateOrCreate(
                ['slug' => $groupPayload['slug']],
                [
                    'title' => $groupPayload['title'],
                    'sort_order' => $groupIndex + 1,
                    'is_active' => true,
                ],
            );

            foreach ($groupPayload['categories'] as $categoryIndex => $categoryPayload) {
                Category::query()->updateOrCreate(
                    ['slug' => $categoryPayload['slug']],
                    [
                        'category_group_id' => $group->id,
                        'title' => $categoryPayload['title'],
                        'description' => $categoryPayload['description'],
                        'icon' => $categoryPayload['icon'],
                        'sort_order' => $categoryIndex + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    private function seedHomepageSlides(): void
    {
        foreach ($this->homepageSlides() as $index => $payload) {
            HomepageSlide::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                [
                    ...$payload,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedAdvertisements(): void
    {
        foreach ($this->advertisements() as $index => $payload) {
            Advertisement::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                [
                    ...$payload,
                    'placement' => 'home_promo',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedNews(): void
    {
        foreach ($this->news() as $payload) {
            NewsPost::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                $payload,
            );
        }
    }

    private function seedAppeals(): void
    {
        foreach ($this->appeals() as $payload) {
            $appeal = PublicAppeal::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                Arr::except($payload, ['attachments', 'timeline', 'documents', 'official_response']),
            );

            $appeal->attachments()->delete();
            $appeal->timelineItems()->delete();
            $appeal->documents()->delete();

            foreach ($payload['attachments'] as $index => $attachment) {
                $appeal->attachments()->create([
                    ...$attachment,
                    'sort_order' => $index + 1,
                ]);
            }

            foreach ($payload['timeline'] as $index => $timelineItem) {
                $appeal->timelineItems()->create([
                    ...$timelineItem,
                    'sort_order' => $index + 1,
                ]);
            }

            foreach ($payload['documents'] as $index => $document) {
                $appeal->documents()->create([
                    ...$document,
                    'sort_order' => $index + 1,
                ]);
            }

            if ($payload['official_response'] === null) {
                $appeal->officialResponse()->delete();

                continue;
            }

            $appeal->officialResponse()->updateOrCreate(
                ['public_appeal_id' => $appeal->id],
                $payload['official_response'],
            );
        }
    }

    private function seedComments(User $admin): void
    {
        $comments = [
            [
                'appeal_slug' => 'road-pits-after-repair',
                'user_id' => $admin->id,
                'status' => 'published',
                'type' => 'public',
                'comment' => 'Проблема действительно повторяется после каждого ремонта.',
                'has_media' => false,
                'created_at' => '2026-06-15T12:00:00+03:00',
                'updated_at' => '2026-06-15T12:00:00+03:00',
            ],
            [
                'appeal_slug' => 'road-pits-after-repair',
                'user_id' => $admin->id,
                'status' => 'published',
                'type' => 'official',
                'comment' => 'Материалы направлены на проверку.',
                'has_media' => false,
                'created_at' => '2026-06-16T09:00:00+03:00',
                'updated_at' => '2026-06-16T09:00:00+03:00',
            ],
            [
                'appeal_slug' => 'entrance-roof-leak-after-rain',
                'user_id' => $admin->id,
                'status' => 'published',
                'type' => 'public',
                'comment' => 'После дождя вода течет по стене второго этажа.',
                'has_media' => true,
                'created_at' => '2026-06-13T11:00:00+03:00',
                'updated_at' => '2026-06-13T11:00:00+03:00',
            ],
            [
                'appeal_slug' => 'construction-waste-in-yard',
                'user_id' => $admin->id,
                'status' => 'published',
                'type' => 'public',
                'comment' => 'Спасибо, мусор действительно вывезли.',
                'has_media' => false,
                'created_at' => '2026-06-16T15:00:00+03:00',
                'updated_at' => '2026-06-16T15:00:00+03:00',
            ],
        ];

        foreach ($comments as $comment) {
            AppealComment::query()->updateOrCreate(
                [
                    'appeal_slug' => $comment['appeal_slug'],
                    'type' => $comment['type'],
                    'comment' => $comment['comment'],
                ],
                $comment,
            );
        }
    }

    /**
     * @return list<array{slug: string, title: string, categories: list<array{slug: string, title: string, description: string, icon: string}>}>
     */
    private function categoryGroups(): array
    {
        return [
            [
                'slug' => 'city',
                'title' => 'Дом и город',
                'categories' => [
                    ['slug' => 'zhkh', 'title' => 'ЖКХ', 'description' => 'Управляющие компании, подъезды, отопление, вода, лифты', 'icon' => 'home'],
                    ['slug' => 'roads', 'title' => 'Дороги', 'description' => 'Ямы, тротуары, освещение, дорожная безопасность', 'icon' => 'road'],
                    ['slug' => 'transport', 'title' => 'Транспорт', 'description' => 'Общественный транспорт, остановки, маршруты, доступность поездок', 'icon' => 'road'],
                    ['slug' => 'improvement', 'title' => 'Благоустройство', 'description' => 'Дворы, парки, мусор, детские и спортивные площадки', 'icon' => 'tree'],
                ],
            ],
            [
                'slug' => 'social',
                'title' => 'Социальная сфера',
                'categories' => [
                    ['slug' => 'healthcare', 'title' => 'Здравоохранение', 'description' => 'Поликлиники, больницы, лекарства, доступность помощи', 'icon' => 'medical'],
                    ['slug' => 'education', 'title' => 'Образование', 'description' => 'Школы, детские сады, колледжи, кружки и безопасность', 'icon' => 'book'],
                    ['slug' => 'social-support', 'title' => 'Социальная защита', 'description' => 'Льготы, выплаты, пособия, поддержка семей и пожилых людей', 'icon' => 'wallet'],
                    ['slug' => 'ecology', 'title' => 'Экология', 'description' => 'Загрязнение, вырубка, отходы, состояние воздуха и воды', 'icon' => 'tree'],
                ],
            ],
            [
                'slug' => 'rights',
                'title' => 'Права и безопасность',
                'categories' => [
                    ['slug' => 'safety', 'title' => 'Безопасность', 'description' => 'Угрозы, опасные места, нарушения общественного порядка', 'icon' => 'shield'],
                    ['slug' => 'public-services', 'title' => 'Госуслуги и документы', 'description' => 'Справки, заявления, задержки, отказы и ошибки в документах', 'icon' => 'file'],
                    ['slug' => 'land-real-estate', 'title' => 'Земля и недвижимость', 'description' => 'Земельные вопросы, жилье, кадастр, собственность и аренда', 'icon' => 'building'],
                    ['slug' => 'authorities', 'title' => 'Работа органов власти', 'description' => 'Бездействие, сроки ответа, качество работы ведомств и чиновников', 'icon' => 'scale'],
                ],
            ],
            [
                'slug' => 'services',
                'title' => 'Услуги и инфраструктура',
                'categories' => [
                    ['slug' => 'telecom', 'title' => 'Связь и интернет', 'description' => 'Мобильная связь, интернет, перебои, качество цифровых сервисов', 'icon' => 'phone'],
                    ['slug' => 'commerce-services', 'title' => 'Торговля и услуги', 'description' => 'Магазины, бытовые услуги, нарушения прав потребителей', 'icon' => 'shop'],
                    ['slug' => 'other', 'title' => 'Другое', 'description' => 'Иные обращения, которые требуют общественного внимания', 'icon' => 'more'],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function homepageSlides(): array
    {
        return [
            [
                'slug' => 'public-appeals-platform',
                'label' => 'Платформа обращений и жалоб граждан',
                'title' => 'Вместе решаем проблемы людей и защищаем их права',
                'lead' => 'Помогаем гражданам с обращениями и жалобами, добиваемся справедливости и поддержки, защищаем права и интересы людей.',
                'note' => 'Лично доставляем жалобы депутатам Госдумы РФ, руководителям МВД, АП, губернаторам, сенаторам. С представителем правозащитников в суд.',
                'image_url' => '/assets/hero-civic-flag.png',
                'primary_cta_label' => 'Подать обращение / жалобу',
                'primary_cta_url' => '/appeal/new',
                'secondary_cta_label' => 'Смотреть обращения',
                'secondary_cta_url' => '/appeals',
            ],
            [
                'slug' => 'public-control-support',
                'label' => 'Общественный контроль и поддержка',
                'title' => 'Помогаем обращениям получить официальный ход',
                'lead' => 'Фиксируем проблему, готовим документы и сопровождаем обращение до понятного статуса и результата.',
                'note' => 'Проверяем факты, собираем подтверждения и направляем жалобы в профильные ведомства и общественные приемные.',
                'image_url' => '/assets/hero-legal-consultation.png',
                'primary_cta_label' => 'Подать обращение / жалобу',
                'primary_cta_url' => '/appeal/new',
                'secondary_cta_label' => 'Смотреть обращения',
                'secondary_cta_url' => '/appeals',
            ],
            [
                'slug' => 'transparent-appeals-register',
                'label' => 'Прозрачный реестр обращений',
                'title' => 'Показываем ход работы и результаты решений',
                'lead' => 'Публикуем проверенные обращения, статусы и важные обновления, чтобы граждане видели движение по проблеме.',
                'note' => 'Каждое обращение проходит модерацию, получает категорию и становится частью открытой системы общественного контроля.',
                'image_url' => '/assets/hero-community-meeting.png',
                'primary_cta_label' => 'Подать обращение / жалобу',
                'primary_cta_url' => '/appeal/new',
                'secondary_cta_label' => 'Смотреть обращения',
                'secondary_cta_url' => '/appeals',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function advertisements(): array
    {
        return [
            [
                'slug' => 'contract-service-main',
                'title' => 'Контрактная служба',
                'label' => 'Партнерский блок',
                'description' => 'Поступай на контрактную службу в Вооруженные силы России.',
                'image_url' => '/assets/114a584a-17bb-4ecb-95fd-c338df16704e.png',
                'alt' => 'Поступай на контрактную службу в Вооруженные силы России',
                'target_url' => 'https://contract.gosuslugi.ru/',
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'slug' => 'contract-service-duty',
                'title' => 'Время служить Родине',
                'label' => 'Партнерский блок',
                'description' => 'Контрактная служба в Вооруженных силах Российской Федерации.',
                'image_url' => '/assets/6c216b42-b479-4748-a9d2-e67cfe37976d.png',
                'alt' => 'Время служить Родине, контрактная служба в Вооруженных силах Российской Федерации',
                'target_url' => 'https://contract.gosuslugi.ru/',
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'slug' => 'contract-service-russia',
                'title' => 'Служи России',
                'label' => 'Партнерский блок',
                'description' => 'Служи России, защищай Родину.',
                'image_url' => '/assets/ec8b05b4-0bb3-4ce5-bdaf-6a964aeb2438.png',
                'alt' => 'Служи России, защищай Родину',
                'target_url' => 'https://contract.gosuslugi.ru/',
                'starts_at' => null,
                'ends_at' => null,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function news(): array
    {
        return [
            [
                'slug' => 'legal-support-consultations',
                'title' => 'Открыта запись на консультации по обращениям в суд',
                'excerpt' => 'Юристы помогают подготовить документы, уточнить сроки и выбрать порядок подачи жалобы.',
                'content' => "Команда проекта открыла предварительную запись на консультации для граждан, которым нужна помощь с обращениями в суд.\n\nСпециалисты помогают проверить документы, уточнить сроки и выбрать порядок подачи жалобы. Эта услуга предназначена для ситуаций, где обычное обращение в ведомство не дало результата или требуется дополнительная правовая оценка.",
                'published_at' => '2026-06-17T00:00:00+03:00',
                'category' => 'Правовая поддержка',
                'image_url' => '/assets/hero-legal-consultation.png',
                'status' => 'published',
            ],
            [
                'slug' => 'public-control-visits',
                'title' => 'Запущены выездные приемы по проблемам ЖКХ',
                'excerpt' => 'Команда собирает обращения жителей и фиксирует системные проблемы для публичного реестра.',
                'content' => "Выездные приемы помогают собрать обращения жителей на месте и быстрее понять масштаб проблемы.\n\nМатериалы передаются на проверку, после чего резонансные обращения могут попасть в публичный реестр.",
                'published_at' => '2026-06-14T00:00:00+03:00',
                'category' => 'Общественный контроль',
                'image_url' => '/assets/hero-community-meeting.png',
                'status' => 'published',
            ],
            [
                'slug' => 'may-appeals-results-report',
                'title' => 'Опубликован отчет по обращениям за май',
                'excerpt' => 'В отчете собраны статусы, решенные вопросы и обращения, которые требуют дополнительной проверки.',
                'content' => "Майский отчет показывает, какие обращения были решены, какие находятся в работе и где требуется дополнительная проверка.\n\nПубличная статистика помогает видеть реальные результаты и повторяющиеся проблемы.",
                'published_at' => '2026-06-10T00:00:00+03:00',
                'category' => 'Результаты',
                'image_url' => '/assets/hero-hands-heart.png',
                'status' => 'published',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function appeals(): array
    {
        return [
            [
                'slug' => 'entrance-roof-leak-after-rain',
                'title' => 'Протекает крыша в подъезде после дождя',
                'excerpt' => 'Жители просят проверить кровлю и работу управляющей компании.',
                'description' => 'После каждого сильного дождя вода попадает в подъезд, на стенах появилась сырость и плесень. Жители несколько раз обращались в управляющую компанию, но ремонт не начался.',
                'status' => 'checking',
                'status_label' => 'На проверке',
                'city' => 'Брянск',
                'district' => 'Советский район',
                'category' => 'ЖКХ',
                'location' => 'г. Брянск, Советский район',
                'published_at' => '2026-06-12T14:30:00+03:00',
                'support_count' => 64,
                'views_count' => 1245,
                'comments_count' => 23,
                'image_url' => '/assets/issue-entrance-roof.png',
                'is_public' => true,
                'attachments' => [
                    ['type' => 'image', 'url' => '/assets/issue-entrance-roof.png', 'title' => 'Фото протечки в подъезде'],
                ],
                'timeline' => [
                    ['status' => 'published', 'title' => 'Обращение опубликовано', 'happened_at' => '2026-06-12T14:30:00+03:00', 'text' => 'Материалы прошли предварительную модерацию.'],
                    ['status' => 'checking', 'title' => 'Факты проверяются', 'happened_at' => '2026-06-14T10:00:00+03:00', 'text' => 'Команда уточняет адрес и историю обращений в УК.'],
                ],
                'official_response' => ['title' => 'Официальный ответ ожидается', 'text' => 'Запрос готовится в ответственную организацию.', 'received_at' => null],
                'documents' => [
                    ['title' => 'Черновик обращения в УК', 'url' => '#'],
                ],
            ],
            [
                'slug' => 'road-pits-after-repair',
                'title' => 'Ямы на дороге после ремонта',
                'excerpt' => 'После недавнего ремонта на дороге снова появились глубокие ямы.',
                'description' => 'Участок дороги был отремонтирован весной, но покрытие быстро разрушилось. Жители просят проверить качество работ и восстановить безопасный проезд.',
                'status' => 'active',
                'status_label' => 'В работе',
                'city' => 'Москва',
                'district' => 'ЮВАО',
                'category' => 'Дороги',
                'location' => 'г. Москва, ЮВАО',
                'published_at' => '2026-06-14T10:15:00+03:00',
                'support_count' => 118,
                'views_count' => 2134,
                'comments_count' => 37,
                'image_url' => '/assets/issue-road.png',
                'is_public' => true,
                'attachments' => [
                    ['type' => 'image', 'url' => '/assets/issue-road.png', 'title' => 'Фото дорожного покрытия'],
                ],
                'timeline' => [
                    ['status' => 'published', 'title' => 'Обращение опубликовано', 'happened_at' => '2026-06-14T10:15:00+03:00', 'text' => 'Проблема добавлена в публичный реестр.'],
                    ['status' => 'active', 'title' => 'Запрос направлен', 'happened_at' => '2026-06-15T09:00:00+03:00', 'text' => 'Материалы направлены для проверки качества ремонта.'],
                ],
                'official_response' => ['title' => 'Ответ в работе', 'text' => 'Ожидается реакция ответственного ведомства.', 'received_at' => null],
                'documents' => [
                    ['title' => 'Фотофиксация дорожных дефектов', 'url' => '#'],
                ],
            ],
            [
                'slug' => 'construction-waste-in-yard',
                'title' => 'Свалка строительного мусора во дворе',
                'excerpt' => 'Во дворе долгое время не вывозят строительный мусор.',
                'description' => 'После ремонта рядом с домом осталась куча строительного мусора. Жители просят организовать вывоз и проверить подрядчика.',
                'status' => 'resolved',
                'status_label' => 'Решено',
                'city' => 'Санкт-Петербург',
                'district' => 'Невский район',
                'category' => 'Благоустройство',
                'location' => 'г. Санкт-Петербург, Невский район',
                'published_at' => '2026-06-05T11:20:00+03:00',
                'support_count' => 41,
                'views_count' => 892,
                'comments_count' => 18,
                'image_url' => '/assets/issue-sport-roof.png',
                'is_public' => true,
                'attachments' => [
                    ['type' => 'image', 'url' => '/assets/issue-sport-roof.png', 'title' => 'Фото дворовой территории'],
                ],
                'timeline' => [
                    ['status' => 'published', 'title' => 'Обращение опубликовано', 'happened_at' => '2026-06-05T11:20:00+03:00', 'text' => 'Материалы приняты в работу.'],
                    ['status' => 'resolved', 'title' => 'Мусор вывезен', 'happened_at' => '2026-06-16T12:00:00+03:00', 'text' => 'Жители подтвердили устранение проблемы.'],
                ],
                'official_response' => ['title' => 'Проблема устранена', 'text' => 'Подрядчик организовал вывоз мусора.', 'received_at' => '2026-06-16T12:00:00+03:00'],
                'documents' => [
                    ['title' => 'Ответ управляющей организации', 'url' => '#'],
                ],
            ],
            [
                'slug' => 'private-draft-example',
                'title' => 'Непубличный черновик',
                'excerpt' => 'Не должен отдаваться публично.',
                'description' => 'Private.',
                'status' => 'draft',
                'status_label' => 'Черновик',
                'city' => 'Москва',
                'district' => 'ЦАО',
                'category' => 'Другое',
                'location' => 'Москва',
                'published_at' => '2026-06-01T00:00:00+03:00',
                'support_count' => 0,
                'views_count' => 0,
                'comments_count' => 0,
                'image_url' => '/assets/issue-road.png',
                'is_public' => false,
                'attachments' => [],
                'timeline' => [],
                'official_response' => null,
                'documents' => [],
            ],
        ];
    }
}
