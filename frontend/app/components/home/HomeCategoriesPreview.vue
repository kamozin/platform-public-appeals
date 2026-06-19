<script setup lang="ts">
import type { AppIconName } from '~/components/layout/AppIcon.vue';
import type { CategoryGroupDto } from '~/types/api/public-content';

type CategoryPreviewItem = {
  label: string;
  icon: AppIconName;
  to: string;
  badge: string | null;
  description: string;
};

const props = defineProps<{
  categoryGroups: CategoryGroupDto[];
}>();

const categoryIconNames = new Set<AppIconName>([
  'building',
  'book',
  'file',
  'home',
  'medical',
  'more',
  'phone',
  'road',
  'scale',
  'shield',
  'shop',
  'tree',
  'wallet',
]);

const resolveCategoryIcon = (icon: string): AppIconName => {
  const iconName = icon as AppIconName;

  if (categoryIconNames.has(iconName)) {
    return iconName;
  }

  return 'file';
};

const categories = computed<CategoryPreviewItem[]>(() => {
  return props.categoryGroups.flatMap((group) => {
    return group.categories.map((category, index) => ({
      label: category.title,
      icon: resolveCategoryIcon(category.icon),
      to: `/appeal/new?category=${category.slug}`,
      badge: index === 0 ? group.title : null,
      description: category.description,
    }));
  });
});

const primaryCategories = computed(() => categories.value.slice(0, 3));
const secondaryCategories = computed(() => categories.value.slice(3));
</script>

<template>
  <section id="appeals" class="container categories" aria-labelledby="categories-title">
    <div class="section-head inline categories-head">
      <div>
        <span class="section-eyebrow">
          <LayoutAppIcon name="grid" />
          Выберите направление
        </span>
        <h2 id="categories-title">Куда направить обращение или жалобу</h2>
        <p>Начните с темы проблемы. Форма сразу подстроится под выбранную категорию.</p>
      </div>
      <NuxtLink class="section-link" to="/categories">
        Все категории
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
    </div>
    <div class="category-router">
      <div class="category-router-main">
        <span class="category-router-kicker">Чаще всего выбирают</span>
        <h3>Начните с понятной темы</h3>
        <p>Не нужно искать ведомство заранее. Выберите проблему, а форма подскажет следующий шаг.</p>

        <div class="category-route-list">
          <NuxtLink
            v-for="(category, index) in primaryCategories"
            :key="category.label"
            :to="category.to"
            class="category-route"
            :class="{ 'category-route--accent': index === 0 }"
          >
            <span class="category-route-icon">
              <LayoutAppIcon :name="category.icon" />
            </span>
            <span class="category-route-copy">
              <span v-if="category.badge" class="category-badge">{{ category.badge }}</span>
              <strong>{{ category.label }}</strong>
              <small>{{ category.description }}</small>
            </span>
            <span class="category-route-action">
              Подать
              <LayoutAppIcon name="arrow" />
            </span>
          </NuxtLink>
        </div>
      </div>

      <div class="category-router-side">
        <div class="category-router-side-head">
          <div>
            <strong>Остальные направления</strong>
            <span>Выберите ближайшую тему или откройте полный список.</span>
          </div>
          <NuxtLink to="/categories">
            Все
            <LayoutAppIcon name="arrow" />
          </NuxtLink>
        </div>

        <div class="category-compact-list">
          <NuxtLink
            v-for="category in secondaryCategories"
            :key="category.label"
            :to="category.to"
            class="category-compact"
          >
            <span class="category-compact-icon">
              <LayoutAppIcon :name="category.icon" />
            </span>
            <span class="category-compact-copy">
              <span v-if="category.badge" class="category-badge">{{ category.badge }}</span>
              <strong>{{ category.label }}</strong>
              <small>{{ category.description }}</small>
            </span>
            <LayoutAppIcon name="arrow" />
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>
