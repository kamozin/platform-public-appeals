<script setup lang="ts">
import type { HomeAdvertisementDto } from '~/types/api/public-content';

const props = defineProps<{
  advertisements: HomeAdvertisementDto[];
}>();

const currentAdBannerIndex = ref(0);
const isChanging = ref(false);
let rotationTimer: number | null = null;
let animationTimer: number | null = null;

const currentAdBanner = computed<HomeAdvertisementDto | null>(() => {
  return props.advertisements[currentAdBannerIndex.value] ?? props.advertisements[0] ?? null;
});

function getRandomAdBannerIndex(): number {
  if (props.advertisements.length <= 1) {
    return 0;
  }

  let nextIndex = currentAdBannerIndex.value;

  while (nextIndex === currentAdBannerIndex.value) {
    nextIndex = Math.floor(Math.random() * props.advertisements.length);
  }

  return nextIndex;
}

function setAdBanner(index: number): void {
  if (props.advertisements.length === 0) {
    currentAdBannerIndex.value = 0;

    return;
  }

  currentAdBannerIndex.value = (index + props.advertisements.length) % props.advertisements.length;
  isChanging.value = false;

  window.requestAnimationFrame(() => {
    isChanging.value = true;

    if (animationTimer !== null) {
      window.clearTimeout(animationTimer);
    }

    animationTimer = window.setTimeout(() => {
      isChanging.value = false;
    }, 240);
  });
}

function handleAdClick(event: MouseEvent): void {
  const shouldOpen = window.confirm('Вы действительно хотите перейти на сайт рекламодателя?');

  if (!shouldOpen) {
    event.preventDefault();
  }
}

onMounted(() => {
  props.advertisements.forEach((banner) => {
    const image = new Image();
    image.src = banner.imageUrl;
  });

  setAdBanner(Math.floor(Math.random() * props.advertisements.length));

  rotationTimer = window.setInterval(() => {
    setAdBanner(getRandomAdBannerIndex());
  }, 15000);
});

watch(
  () => props.advertisements.length,
  () => {
    setAdBanner(0);
  },
);

onBeforeUnmount(() => {
  if (rotationTimer !== null) {
    window.clearInterval(rotationTimer);
  }

  if (animationTimer !== null) {
    window.clearTimeout(animationTimer);
  }
});
</script>

<template>
  <section v-if="currentAdBanner" class="container promo-section" aria-labelledby="promo-section-title">
    <div class="promo-section-head">
      <h2 id="promo-section-title">{{ currentAdBanner.title }}</h2>
      <span>{{ currentAdBanner.label || 'Реклама' }}</span>
    </div>
    <a
      :class="['promo-banner', { 'is-changing': isChanging }]"
      :href="currentAdBanner.targetUrl"
      :aria-label="`${currentAdBanner.alt}. Откроется в новом окне`"
      target="_blank"
      rel="noopener noreferrer"
      data-promo-rotator
      @click="handleAdClick"
    >
      <img
        :src="currentAdBanner.imageUrl"
        :alt="currentAdBanner.alt"
        width="2172"
        height="724"
        loading="lazy"
        decoding="async"
        data-promo-image
      >
    </a>
  </section>
</template>
