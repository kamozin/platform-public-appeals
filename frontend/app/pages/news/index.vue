<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { NewsIndexDto } from '~/types/api/public-content';
import { formatRuDate } from '~/utils/formatters';

const api = useApi();
const { handleImageError, resolveImageUrl } = useImageFallback('/assets/hero-legal-consultation.png');

const { data: response, error } = await useAsyncData('news-index-page', () => {
  return api.get<ApiDataEnvelope<NewsIndexDto>>('/news');
});

if (error.value) {
  throw createError({
    statusCode: 500,
    statusMessage: 'News page is unavailable',
  });
}

const payload = response.value;

if (!payload) {
  throw createError({
    statusCode: 500,
    statusMessage: 'News page is unavailable',
  });
}

const page = payload.data;

usePageSeo({
  title: page.seo.title,
  description: page.seo.description,
  path: '/news',
  robots: page.seo.robots,
  ogImageUrl: page.seo.ogImageUrl ?? undefined,
});
</script>

<template>
  <main class="content-page public-page">
    <div class="container content-page__inner">
      <nav class="content-breadcrumb" aria-label="Навигационная цепочка">
        <NuxtLink to="/">
          Главная
        </NuxtLink>
        <span>Новости</span>
      </nav>

      <section class="content-hero public-hero" aria-labelledby="news-page-title">
        <p class="content-eyebrow">Новости</p>
        <h1 id="news-page-title">Новости проекта</h1>
        <p>Правовая поддержка, общественный контроль и результаты работы с обращениями граждан.</p>
      </section>

      <section class="public-section" aria-labelledby="news-list-title">
        <div class="public-section-head">
          <h2 id="news-list-title">Последние публикации</h2>
          <span>{{ page.pagination.total }} материала</span>
        </div>

        <div class="public-card-grid public-card-grid--three">
          <article
            v-for="item in page.items"
            :key="item.id"
            class="news-card public-news-card"
          >
            <NuxtLink class="news-card-media" :to="`/news/${item.slug}`">
              <img
                :src="resolveImageUrl(item.imageUrl)"
                :alt="item.title"
                width="1536"
                height="864"
                loading="lazy"
                decoding="async"
                @error="handleImageError"
              >
            </NuxtLink>
            <div class="news-card-body">
              <div class="news-meta">
                <time :datetime="item.publishedAt">{{ formatRuDate(item.publishedAt) }}</time>
                <span>{{ item.category }}</span>
              </div>
              <h3>
                <NuxtLink :to="`/news/${item.slug}`">
                  {{ item.title }}
                </NuxtLink>
              </h3>
              <p>{{ item.excerpt }}</p>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>
