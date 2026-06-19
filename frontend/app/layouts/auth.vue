<script setup lang="ts">
const route = useRoute();

const isPasswordReset = computed(() => route.path === '/password-reset');
const isVerification = computed(() => route.path === '/verification');
const isScreenFlow = computed(() => isPasswordReset.value || isVerification.value);
const shellClasses = computed(() => [
  'app-shell',
  'auth-page',
  route.path === '/register' ? 'auth-page-register' : '',
  isScreenFlow.value ? 'screen-page flow-screen' : '',
  isPasswordReset.value ? 'password-reset-screen' : '',
  isVerification.value ? 'verification-screen' : '',
]);
</script>

<template>
  <div :class="shellClasses">
    <LayoutAppSvgSprite />
    <NuxtLink class="skip-link" to="#main-content">
      Перейти к содержимому
    </NuxtLink>
    <LayoutAppHeader />
    <slot />
    <LayoutAppFooter />
    <UiScrollTopButton />
  </div>
</template>
