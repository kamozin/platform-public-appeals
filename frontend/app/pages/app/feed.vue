<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealsIndexDto } from '~/types/api/public-content';
import { formatRuNumber } from '~/utils/formatters';

definePageMeta({
  layout: 'pwa',
});

useNoindexSeo();
useHead({
  title: 'Лента | Рука добра',
});

const api = useApi();
const { data: appealsContent, pending } = await useAsyncData(
  'pwa-feed-appeals',
  async () => {
    try {
      const response = await api.get<ApiDataEnvelope<AppealsIndexDto>>('/appeals', {
        query: {
          page: 1,
          per_page: 12,
        },
      });

      return response.data;
    } catch {
      return null;
    }
  },
  {
    server: false,
    default: () => null,
  },
);

const appeals = computed(() => appealsContent.value?.items ?? []);
</script>

<template>
  <section class="pwa-screen" aria-labelledby="pwa-feed-title">
    <div class="pwa-section-heading pwa-section-heading--top">
      <div>
        <p class="pwa-eyebrow">
          Общественная лента
        </p>
        <h1 id="pwa-feed-title">
          Последние обращения
        </h1>
      </div>
      <NuxtLink class="pwa-icon-button" to="/app/new" aria-label="Подать обращение">
        <LayoutAppIcon name="plus" />
      </NuxtLink>
    </div>

    <div v-if="appeals.length" class="pwa-feed-list">
      <article v-for="appeal in appeals" :key="appeal.id" class="pwa-feed-card">
        <img :src="appeal.imageUrl" :alt="appeal.title" loading="lazy">
        <div class="pwa-feed-card__body">
          <div class="pwa-feed-card__meta">
            <span>{{ appeal.statusLabel }}</span>
            <span>{{ appeal.city }}</span>
          </div>
          <h2>{{ appeal.title }}</h2>
          <p>{{ appeal.excerpt }}</p>
          <div class="pwa-feed-card__footer">
            <span>
              <LayoutAppIcon name="heart" />
              {{ formatRuNumber(appeal.supportCount) }}
            </span>
            <span>
              <LayoutAppIcon name="eye" />
              {{ formatRuNumber(appeal.viewsCount) }}
            </span>
            <NuxtLink :to="`/appeals/${appeal.slug}`">
              Открыть
            </NuxtLink>
          </div>
        </div>
      </article>
    </div>

    <section v-else class="pwa-empty-panel">
      <LayoutAppIcon name="file" />
      <h2>{{ pending ? 'Загрузка ленты...' : 'Лента временно недоступна' }}</h2>
      <p>Попробуйте обновить экран после восстановления соединения.</p>
    </section>
  </section>
</template>
