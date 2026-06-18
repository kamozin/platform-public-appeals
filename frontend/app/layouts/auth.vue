<script setup lang="ts">
const route = useRoute();

const isPasswordReset = computed(() => route.path === '/password-reset');
const shellClasses = computed(() => [
  'app-shell',
  'auth-page',
  route.path === '/register' ? 'auth-page-register' : '',
  isPasswordReset.value ? 'screen-page flow-screen password-reset-screen' : '',
]);
</script>

<template>
  <div :class="shellClasses">
    <LayoutAppSvgSprite />
    <NuxtLink class="skip-link" to="#main-content">
      Перейти к содержимому
    </NuxtLink>
    <LayoutAuthHeader />
    <slot />
    <LayoutAuthFooter :screen="isPasswordReset" />
    <UiScrollTopButton />
  </div>
</template>
