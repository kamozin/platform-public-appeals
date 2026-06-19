<script setup lang="ts">
import type { AppIconName } from '~/components/layout/AppIcon.vue';

type PwaNavItem = {
  path: string;
  label: string;
  icon: AppIconName;
};

const route = useRoute();

const items: readonly PwaNavItem[] = [
  { path: '/app', label: 'Главная', icon: 'home' },
  { path: '/app/feed', label: 'Лента', icon: 'file' },
  { path: '/app/new', label: 'Подать', icon: 'plus' },
  { path: '/app/profile', label: 'Профиль', icon: 'user' },
];

const isActive = (path: string): boolean => {
  if (path === '/app') {
    return route.path === path;
  }

  return route.path === path || route.path.startsWith(`${path}/`);
};
</script>

<template>
  <nav class="pwa-bottom-nav" aria-label="Навигация приложения">
    <NuxtLink
      v-for="item in items"
      :key="item.path"
      class="pwa-bottom-nav__item"
      :class="{ 'pwa-bottom-nav__item--active': isActive(item.path) }"
      :to="item.path"
      :aria-current="isActive(item.path) ? 'page' : undefined"
    >
      <LayoutAppIcon :name="item.icon" />
      <span>{{ item.label }}</span>
    </NuxtLink>
  </nav>
</template>
