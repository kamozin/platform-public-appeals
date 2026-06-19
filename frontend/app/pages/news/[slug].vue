<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { NewsItemDto } from '~/types/api/public-content';
import { formatRuDate } from '~/utils/formatters';

const route = useRoute();
const routeSlug = route.params.slug;
const slug = Array.isArray(routeSlug) ? routeSlug[0] : routeSlug;

if (!slug) {
  throw createError({
    statusCode: 404,
    statusMessage: 'News item not found',
  });
}

const api = useApi();
const { handleImageError, resolveImageUrl } = useImageFallback('/assets/hero-legal-consultation.png');

const { data: response, error } = await useAsyncData(`news-detail-${slug}`, () => {
  return api.get<ApiDataEnvelope<NewsItemDto>>(`/news/${slug}`);
});

if (error.value) {
  throw createError({
    statusCode: 404,
    statusMessage: 'News item not found',
  });
}

const payload = response.value;

if (!payload) {
  throw createError({
    statusCode: 404,
    statusMessage: 'News item not found',
  });
}

const page = payload.data;
const articleParagraphs = page.content
  .split(/\n{2,}/)
  .map((paragraph) => paragraph.trim())
  .filter((paragraph) => paragraph.length > 0);
const paragraphs = articleParagraphs.length > 0 ? articleParagraphs : [page.excerpt];

const pluralizeRu = (value: number, one: string, few: string, many: string): string => {
  const mod10 = value % 10;
  const mod100 = value % 100;

  if (mod10 === 1 && mod100 !== 11) {
    return one;
  }

  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
    return few;
  }

  return many;
};

const articleText = [page.title, page.excerpt, ...paragraphs].join(' ');
const wordCount = articleText.split(/\s+/).filter((word) => word.length > 0).length;
const readingMinutes = Math.max(1, Math.ceil(wordCount / 180));
const readingTimeLabel = `${readingMinutes} ${pluralizeRu(readingMinutes, 'минута', 'минуты', 'минут')} чтения`;
const coverCaption = `Материал проекта «Рука добра»: ${page.category.toLocaleLowerCase('ru-RU')}.`;
const sidebarFacts = [
  { label: 'Тема', value: page.category },
  { label: 'Дата', value: formatRuDate(page.publishedAt) },
  { label: 'Формат', value: 'Новость проекта' },
  { label: 'Время чтения', value: readingTimeLabel },
];
const importantItems = [
  `Материал относится к направлению «${page.category}».`,
  'Ключевые детали и контекст собраны в тексте новости.',
  'Если тема касается вашей ситуации, можно подать обращение или перейти к публичному реестру.',
];

usePageSeo({
  title: page.seo?.title ?? page.title,
  description: page.seo?.description ?? page.excerpt,
  path: `/news/${page.slug}`,
  robots: page.seo?.robots ?? 'index,follow',
  ogImageUrl: page.seo?.ogImageUrl ?? page.imageUrl,
});
</script>

<template>
  <main class="content-page public-page news-detail-page">
    <div class="container content-page__inner">
      <nav class="content-breadcrumb" aria-label="Навигационная цепочка">
        <NuxtLink to="/">
          Главная
        </NuxtLink>
        <NuxtLink to="/news">
          Новости
        </NuxtLink>
        <span>{{ page.title }}</span>
      </nav>

      <article class="news-detail-layout" aria-labelledby="news-detail-title">
        <header class="news-detail-header">
          <div class="news-detail-kicker">
            <span>{{ page.category }}</span>
            <time :datetime="page.publishedAt">
              <LayoutAppIcon name="clock" />
              {{ formatRuDate(page.publishedAt) }}
            </time>
            <span>
              <LayoutAppIcon name="file" />
              {{ readingTimeLabel }}
            </span>
          </div>

          <h1 id="news-detail-title">{{ page.title }}</h1>
          <p>{{ page.excerpt }}</p>

          <div class="news-detail-actions">
            <NuxtLink class="btn btn-red" to="/appeal/new">
              <LayoutAppIcon name="file" />
              Подать обращение
            </NuxtLink>
            <NuxtLink class="btn btn-outline" to="/news">
              <LayoutAppIcon name="back" />
              Все новости
            </NuxtLink>
          </div>
        </header>

        <figure class="news-detail-cover">
          <img
            :src="resolveImageUrl(page.imageUrl)"
            :alt="page.title"
            width="1536"
            height="864"
            @error="handleImageError"
          >
          <figcaption>{{ coverCaption }}</figcaption>
        </figure>

        <div class="news-detail-grid">
          <div class="news-detail-article">
            <p v-for="paragraph in paragraphs" :key="paragraph">
              {{ paragraph }}
            </p>

            <section class="news-detail-note" aria-labelledby="news-important-title">
              <h2 id="news-important-title">Что важно</h2>
              <ul>
                <li v-for="item in importantItems" :key="item">
                  {{ item }}
                </li>
              </ul>
            </section>
          </div>

          <aside class="news-detail-side" aria-label="Информация о новости">
            <section class="news-detail-info news-detail-info--accent">
              <h2>Коротко</h2>
              <dl>
                <div v-for="fact in sidebarFacts" :key="fact.label">
                  <dt>{{ fact.label }}</dt>
                  <dd>{{ fact.value }}</dd>
                </div>
              </dl>
            </section>

            <section class="news-detail-info">
              <h2>Действия</h2>
              <NuxtLink class="btn btn-red" to="/appeal/new">
                <LayoutAppIcon name="file" />
                Подать обращение
              </NuxtLink>
              <NuxtLink class="btn btn-outline" to="/news">
                <LayoutAppIcon name="back" />
                Все новости
              </NuxtLink>
            </section>

            <section class="news-detail-info news-detail-related">
              <h2>Продолжить</h2>
              <NuxtLink to="/appeals">
                Публичный реестр обращений
              </NuxtLink>
              <NuxtLink to="/categories">
                Категории обращений
              </NuxtLink>
              <NuxtLink to="/book">
                Народная жалобная книга
              </NuxtLink>
            </section>
          </aside>
        </div>
      </article>
    </div>
  </main>
</template>
