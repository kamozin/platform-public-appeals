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
const paragraphs = page.content.split(/\n{2,}/).filter(Boolean);

usePageSeo({
  title: page.seo?.title ?? page.title,
  description: page.seo?.description ?? page.excerpt,
  path: `/news/${page.slug}`,
  robots: page.seo?.robots ?? 'index,follow',
  ogImageUrl: page.seo?.ogImageUrl ?? page.imageUrl,
});
</script>

<template>
  <main class="content-page public-page">
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

      <article class="article-page">
        <header class="content-hero public-hero article-hero">
          <p class="content-eyebrow">{{ page.category }}</p>
          <h1>{{ page.title }}</h1>
          <p>{{ page.excerpt }}</p>
          <time :datetime="page.publishedAt">{{ formatRuDate(page.publishedAt) }}</time>
        </header>

        <img class="article-cover" :src="page.imageUrl" :alt="page.title" width="1536" height="864">

        <div class="content-card article-body">
          <section>
            <p v-for="paragraph in paragraphs" :key="paragraph">
              {{ paragraph }}
            </p>
          </section>
        </div>
      </article>
    </div>
  </main>
</template>
