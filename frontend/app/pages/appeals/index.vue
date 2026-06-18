<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealListItemDto, AppealStatus, AppealsIndexDto } from '~/types/api/public-content';
import { formatRuDateTime, formatRuNumber } from '~/utils/formatters';

type QueryValue = string | number | null | undefined;

const route = useRoute();
const api = useApi();

const asString = (value: unknown): string => {
  if (Array.isArray(value)) {
    return value[0] ?? '';
  }

  if (typeof value === 'string') {
    return value;
  }

  return '';
};

const cleanQuery = (query: Record<string, QueryValue>): Record<string, string> => {
  const result: Record<string, string> = {};

  for (const [key, value] of Object.entries(query)) {
    if (value === null || value === undefined || value === '') {
      continue;
    }

    result[key] = String(value);
  }

  return result;
};

const search = ref(asString(route.query.search));
const status = ref(asString(route.query.status));
const city = ref(asString(route.query.city));
const category = ref(asString(route.query.category));
const sort = ref(asString(route.query.sort) || 'newest');

const queryOptions = computed(() => cleanQuery({
  search: asString(route.query.search),
  status: asString(route.query.status),
  city: asString(route.query.city),
  category: asString(route.query.category),
  sort: asString(route.query.sort),
  page: asString(route.query.page),
}));

const { data: response, error, pending } = await useAsyncData('appeals-index-page', () => {
  return api.get<ApiDataEnvelope<AppealsIndexDto>>('/appeals', {
    query: queryOptions.value,
  });
}, {
  watch: [queryOptions],
});

if (error.value) {
  throw createError({
    statusCode: 500,
    statusMessage: 'Appeals page is unavailable',
  });
}

const initialPayload = response.value;

if (!initialPayload) {
  throw createError({
    statusCode: 500,
    statusMessage: 'Appeals page is unavailable',
  });
}

const page = computed(() => response.value?.data);

const syncFiltersFromRoute = (): void => {
  search.value = asString(route.query.search);
  status.value = asString(route.query.status);
  city.value = asString(route.query.city);
  category.value = asString(route.query.category);
  sort.value = asString(route.query.sort) || 'newest';
};

watch(() => route.query, syncFiltersFromRoute);

const submitFilters = async (): Promise<void> => {
  await navigateTo({
    path: '/appeals',
    query: cleanQuery({
      search: search.value,
      status: status.value,
      city: city.value,
      category: category.value,
      sort: sort.value === 'newest' ? undefined : sort.value,
    }),
  });
};

const resetFilters = async (): Promise<void> => {
  await navigateTo('/appeals');
};

const pageLink = (pageNumber: number) => {
  return {
    path: '/appeals',
    query: cleanQuery({
      ...queryOptions.value,
      page: pageNumber > 1 ? pageNumber : undefined,
    }),
  };
};

const statusClasses: Record<AppealStatus, string> = {
  active: 'status-orange',
  checking: 'status-blue',
  draft: 'status-blue',
  resolved: 'status-green',
};

const statusClass = (appeal: AppealListItemDto): string => {
  return statusClasses[appeal.status];
};

const currentPage = computed(() => page.value?.pagination.currentPage ?? 1);
const lastPage = computed(() => page.value?.pagination.lastPage ?? 1);
const visiblePages = computed(() => {
  return Array.from({ length: lastPage.value }, (_, index) => index + 1);
});

usePageSeo({
  title: initialPayload.data.seo.title,
  description: initialPayload.data.seo.description,
  path: '/appeals',
  robots: initialPayload.data.seo.robots,
  ogImageUrl: initialPayload.data.seo.ogImageUrl ?? undefined,
});
</script>

<template>
  <main class="content-page public-page">
    <div class="container content-page__inner">
      <nav class="content-breadcrumb" aria-label="Навигационная цепочка">
        <NuxtLink to="/">
          Главная
        </NuxtLink>
        <span>Обращения</span>
      </nav>

      <section class="content-hero public-hero" aria-labelledby="appeals-page-title">
        <p class="content-eyebrow">Публичный реестр</p>
        <h1 id="appeals-page-title">Обращения и жалобы граждан</h1>
        <p>Следите за статусами, поддерживайте важные проблемы и смотрите, какие обращения уже получили результат.</p>
      </section>

      <section v-if="page" class="appeals-summary" aria-label="Сводка обращений">
        <article>
          <strong>{{ formatRuNumber(page.summary.publishedCount) }}</strong>
          <span>опубликовано</span>
        </article>
        <article>
          <strong>{{ formatRuNumber(page.summary.resolvedCount) }}</strong>
          <span>решено</span>
        </article>
        <article>
          <strong>{{ formatRuNumber(page.summary.activeCount) }}</strong>
          <span>в работе</span>
        </article>
        <article>
          <strong>{{ formatRuNumber(page.summary.supportCount) }}</strong>
          <span>поддержек</span>
        </article>
      </section>

      <section class="appeals-layout" aria-label="Фильтр и список обращений">
        <form class="appeals-filter" @submit.prevent="submitFilters">
          <label>
            <span>Поиск</span>
            <input v-model="search" type="search" name="search" placeholder="Ключевые слова">
          </label>

          <label>
            <span>Статус</span>
            <select v-model="status" name="status">
              <option value="">Все статусы</option>
              <option value="checking">На проверке</option>
              <option value="active">В работе</option>
              <option value="resolved">Решено</option>
            </select>
          </label>

          <label>
            <span>Город</span>
            <select v-model="city" name="city">
              <option value="">Все города</option>
              <option value="Брянск">Брянск</option>
              <option value="Москва">Москва</option>
              <option value="Санкт-Петербург">Санкт-Петербург</option>
            </select>
          </label>

          <label>
            <span>Категория</span>
            <select v-model="category" name="category">
              <option value="">Все категории</option>
              <option value="ЖКХ">ЖКХ</option>
              <option value="Дороги">Дороги</option>
              <option value="Благоустройство">Благоустройство</option>
            </select>
          </label>

          <label>
            <span>Сортировка</span>
            <select v-model="sort" name="sort">
              <option value="newest">Сначала новые</option>
              <option value="popular">По поддержке</option>
              <option value="resolved">Сначала решенные</option>
            </select>
          </label>

          <div class="appeals-filter-actions">
            <button class="btn btn-red" type="submit">
              Применить
            </button>
            <button class="btn btn-outline" type="button" @click="resetFilters">
              Сбросить
            </button>
          </div>
        </form>

        <div class="appeals-list-wrap">
          <div class="public-section-head">
            <h2>Найденные обращения</h2>
            <span v-if="page">{{ page.pagination.total }} в реестре</span>
          </div>

          <div v-if="pending" class="content-notice">
            <strong>Обновляем список</strong>
            <p>Загружаем обращения по выбранным фильтрам.</p>
          </div>

          <div v-if="page && page.items.length > 0" class="appeals-list">
            <article
              v-for="appeal in page.items"
              :key="appeal.id"
              class="appeal-card appeal-list-card"
            >
              <span class="status" :class="statusClass(appeal)">{{ appeal.statusLabel }}</span>
              <NuxtLink :to="`/appeals/${appeal.slug}`" class="appeal-list-media">
                <img :src="appeal.imageUrl" :alt="appeal.title" width="960" height="640" loading="lazy" decoding="async">
              </NuxtLink>
              <div class="appeal-copy">
                <span>{{ appeal.category }}</span>
                <h3>
                  <NuxtLink :to="`/appeals/${appeal.slug}`">
                    {{ appeal.title }}
                  </NuxtLink>
                </h3>
                <p>{{ appeal.city }}, {{ appeal.district }}<br>{{ formatRuDateTime(appeal.publishedAt) }}</p>
                <p>{{ appeal.excerpt }}</p>
              </div>
              <footer>
                <span><LayoutAppIcon name="eye" />{{ formatRuNumber(appeal.viewsCount) }}</span>
                <span class="appeal-like"><LayoutAppIcon name="heart" />{{ formatRuNumber(appeal.supportCount) }}</span>
                <span><LayoutAppIcon name="comment" />{{ formatRuNumber(appeal.commentsCount) }}</span>
              </footer>
            </article>
          </div>

          <div v-else-if="page" class="content-notice">
            <strong>Ничего не найдено</strong>
            <p>Попробуйте изменить фильтры или сбросить поиск.</p>
          </div>

          <nav v-if="page && lastPage > 1" class="list-pagination" aria-label="Навигация по страницам обращений">
            <span class="list-pagination-status">
              Страница {{ currentPage }} из {{ lastPage }}
            </span>
            <div class="list-pagination-controls">
              <NuxtLink
                class="pagination-link pagination-step"
                :class="{ 'is-disabled': currentPage === 1 }"
                :to="pageLink(Math.max(1, currentPage - 1))"
              >
                Назад
              </NuxtLink>
              <NuxtLink
                v-for="pageNumber in visiblePages"
                :key="pageNumber"
                class="pagination-link"
                :class="{ 'is-active': pageNumber === currentPage }"
                :to="pageLink(pageNumber)"
              >
                {{ pageNumber }}
              </NuxtLink>
              <NuxtLink
                class="pagination-link pagination-step"
                :class="{ 'is-disabled': currentPage === lastPage }"
                :to="pageLink(Math.min(lastPage, currentPage + 1))"
              >
                Далее
              </NuxtLink>
            </div>
          </nav>
        </div>
      </section>
    </div>
  </main>
</template>
