<script setup lang="ts">
const isVisible = ref(false);
const buttonRef = ref<HTMLButtonElement | null>(null);

let isTicking = false;

const updateVisibility = (): void => {
  isTicking = false;
  isVisible.value = window.scrollY > 420;
};

const requestVisibilityUpdate = (): void => {
  if (isTicking) {
    return;
  }

  isTicking = true;
  window.requestAnimationFrame(updateVisibility);
};

const scrollToTop = (): void => {
  buttonRef.value?.animate(
    [
      { transform: 'scale(1)' },
      { transform: 'scale(0.94)' },
      { transform: 'scale(1)' },
    ],
    { duration: 170, easing: 'ease-out' },
  );

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  window.scrollTo({
    top: 0,
    behavior: prefersReducedMotion ? 'auto' : 'smooth',
  });
};

onMounted(() => {
  window.addEventListener('scroll', requestVisibilityUpdate, { passive: true });
  updateVisibility();
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', requestVisibilityUpdate);
});
</script>

<template>
  <button
    ref="buttonRef"
    class="scroll-top-button"
    :class="{ 'is-visible': isVisible }"
    type="button"
    :tabindex="isVisible ? 0 : -1"
    aria-label="Наверх"
    :aria-hidden="!isVisible"
    data-scroll-top
    @click="scrollToTop"
  >
    <LayoutAppIcon name="arrow-up" />
  </button>
</template>
