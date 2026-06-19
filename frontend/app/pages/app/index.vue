<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealsIndexDto, HomeContentDto } from '~/types/api/public-content';
import { formatRuNumber } from '~/utils/formatters';

definePageMeta({
  layout: 'pwa',
});

useNoindexSeo();
useHead({
  title: 'Приложение | Рука добра',
});

const api = useApi();
const auth = useAuth();

const { data: homeContent, pending: homePending } = await useAsyncData(
  'pwa-home-content',
  async () => {
    try {
      const response = await api.get<ApiDataEnvelope<HomeContentDto>>('/public-content/home');

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
const { data: appealsContent, pending: appealsPending } = await useAsyncData(
  'pwa-home-appeals',
  async () => {
    try {
      const response = await api.get<ApiDataEnvelope<AppealsIndexDto>>('/appeals', {
        query: {
          page: 1,
          per_page: 3,
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

const userName = computed(() => auth.user.value?.name ?? 'Гость');
const latestAppeals = computed(() => appealsContent.value?.items ?? []);
const summary = computed(() => appealsContent.value?.summary ?? {
  activeCount: 0,
  publishedCount: 0,
  resolvedCount: 0,
  supportCount: 0,
});
const primaryCategories = computed(() => {
  return homeContent.value?.categoryGroups
    .flatMap((group) => group.categories)
    .slice(0, 6) ?? [];
});
const isLoading = computed(() => homePending.value || appealsPending.value);
</script>

<template>
  <section class="pwa-screen" aria-labelledby="pwa-home-title">
    <div class="pwa-hero-panel">
      <div>
        <p class="pwa-eyebrow">
          {{ userName }}
        </p>
        <h1 id="pwa-home-title">
          Обращения и поддержка
        </h1>
      </div>
      <NuxtLink class="pwa-primary-action" to="/app/new">
        <LayoutAppIcon name="plus" />
        Подать
      </NuxtLink>
    </div>

    <div class="pwa-stat-grid" aria-label="Статистика обращений">
      <div class="pwa-stat">
        <span>{{ formatRuNumber(summary.publishedCount) }}</span>
        <small>опубликовано</small>
      </div>
      <div class="pwa-stat">
        <span>{{ formatRuNumber(summary.activeCount) }}</span>
        <small>в работе</small>
      </div>
      <div class="pwa-stat">
        <span>{{ formatRuNumber(summary.resolvedCount) }}</span>
        <small>решено</small>
      </div>
    </div>

    <section class="pwa-panel" aria-labelledby="pwa-actions-title">
      <div class="pwa-section-heading">
        <h2 id="pwa-actions-title">
          Быстрые действия
        </h2>
      </div>
      <div class="pwa-action-list">
        <NuxtLink class="pwa-action-row" to="/app/new">
          <span class="pwa-action-row__icon pwa-action-row__icon--red">
            <LayoutAppIcon name="edit" />
          </span>
          <span>
            <strong>Новое обращение</strong>
            <small>ЖКХ, дороги, медицина, благоустройство</small>
          </span>
          <LayoutAppIcon name="arrow" />
        </NuxtLink>
        <NuxtLink class="pwa-action-row" to="/app/feed">
          <span class="pwa-action-row__icon pwa-action-row__icon--blue">
            <LayoutAppIcon name="file" />
          </span>
          <span>
            <strong>Лента обращений</strong>
            <small>Последние опубликованные карточки</small>
          </span>
          <LayoutAppIcon name="arrow" />
        </NuxtLink>
      </div>
    </section>

    <section class="pwa-panel" aria-labelledby="pwa-categories-title">
      <div class="pwa-section-heading">
        <h2 id="pwa-categories-title">
          Категории
        </h2>
      </div>
      <div v-if="primaryCategories.length" class="pwa-chip-grid">
        <NuxtLink
          v-for="category in primaryCategories"
          :key="category.id"
          class="pwa-chip"
          :to="`/categories#${category.slug}`"
        >
          {{ category.title }}
        </NuxtLink>
      </div>
      <p v-else class="pwa-muted">
        {{ isLoading ? 'Загрузка категорий...' : 'Категории временно недоступны.' }}
      </p>
    </section>

    <section class="pwa-panel" aria-labelledby="pwa-latest-title">
      <div class="pwa-section-heading">
        <h2 id="pwa-latest-title">
          Последние обращения
        </h2>
        <NuxtLink to="/app/feed">
          Все
        </NuxtLink>
      </div>
      <div v-if="latestAppeals.length" class="pwa-mini-feed">
        <article v-for="appeal in latestAppeals" :key="appeal.id" class="pwa-mini-card">
          <span>{{ appeal.statusLabel }}</span>
          <h3>{{ appeal.title }}</h3>
          <p>{{ appeal.city }} · {{ appeal.category }}</p>
        </article>
      </div>
      <p v-else class="pwa-muted">
        {{ appealsPending ? 'Загрузка ленты...' : 'Лента временно недоступна.' }}
      </p>
    </section>
  </section>
</template>
