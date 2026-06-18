<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    variant?: 'header' | 'footer';
  }>(),
  {
    variant: 'header',
  },
);

const logoWidth = computed(() => props.variant === 'footer' ? 238 : 292);
const logoHeight = computed(() => props.variant === 'footer' ? 58 : 71);
</script>

<template>
  <NuxtLink
    class="brand"
    :class="`brand--${props.variant}`"
    to="/"
    aria-label="Рука добра"
  >
    <img
      class="brand-logo"
      src="/assets/logo-ruka-dobra.svg"
      alt="НКО Рука добра"
      :width="logoWidth"
      :height="logoHeight"
    >
  </NuxtLink>
</template>
