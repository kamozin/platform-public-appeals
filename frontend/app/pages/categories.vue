<script setup lang="ts">
import type { CategoriesPageDto } from '~/types/api/public-content';
import type { AppIconName } from '~/components/layout/AppIcon.vue';

const allowedIcons = new Set<AppIconName>([
  'book',
  'building',
  'education',
  'file',
  'flag',
  'heart',
  'home',
  'medical',
  'more',
  'phone',
  'road',
  'scale',
  'shield',
  'shop',
  'trend',
  'tree',
  'users',
  'wallet',
]);

const resolveIcon = (icon: string): AppIconName => {
  const iconName = icon as AppIconName;

  if (allowedIcons.has(iconName)) {
    return iconName;
  }

  return 'file';
};

const categoriesApi = useCategoriesContent();

const { data: categoriesPage, error: categoriesError } = await useAsyncData('categories-page', () => {
  return categoriesApi.fetchCategories();
});

if (categoriesError.value || !categoriesPage.value) {
  throw createError({
    statusCode: 500,
    statusMessage: 'Categories page is unavailable',
  });
}

const page = computed<CategoriesPageDto>(() => categoriesPage.value as CategoriesPageDto);
const groups = computed(() => page.value.groups);
const totalCategories = computed(() => {
  return groups.value.reduce((count, group) => count + group.categories.length, 0);
});

usePageSeo({
  title: page.value.seo.title,
  description: page.value.seo.description,
  path: '/categories',
  robots: page.value.seo.robots,
  ogImageUrl: page.value.seo.ogImageUrl ?? undefined,
});
</script>

<template>
  <main class="content-page public-page">
    <div class="container content-page__inner">
      <nav class="content-breadcrumb" aria-label="Навигационная цепочка">
        <NuxtLink to="/">
          Главная
        </NuxtLink>
        <span>Категории</span>
      </nav>

      <section class="content-hero public-hero" aria-labelledby="categories-page-title">
        <p class="content-eyebrow">Категории обращений</p>
        <h1 id="categories-page-title">Выберите тему обращения</h1>
        <p>
          {{ totalCategories }} направлений помогают сразу определить контекст проблемы и подготовить обращение для проверки.
        </p>
      </section>

      <section
        v-if="groups.length === 0"
        class="public-empty"
        aria-labelledby="categories-empty-title"
      >
        <span>
          <LayoutAppIcon name="grid" />
        </span>
        <h2 id="categories-empty-title">Категории пока не опубликованы</h2>
        <p>Администратор может добавить активные категории в разделе управления контентом.</p>
      </section>

      <section
        v-for="group in groups"
        :key="group.slug"
        class="public-section"
        :aria-labelledby="`category-group-${group.slug}`"
      >
        <div class="public-section-head">
          <h2 :id="`category-group-${group.slug}`">{{ group.title }}</h2>
        </div>

        <div class="public-card-grid public-card-grid--three">
          <article
            v-for="category in group.categories"
            :key="category.id"
            class="public-card category-page-card"
          >
            <LayoutAppIcon :name="resolveIcon(category.icon)" />
            <div>
              <h3>{{ category.title }}</h3>
              <p>{{ category.description }}</p>
              <NuxtLink :to="`/appeal/new?category=${category.slug}`">
                Подать обращение
                <LayoutAppIcon name="arrow" />
              </NuxtLink>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>
