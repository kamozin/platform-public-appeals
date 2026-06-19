<script setup lang="ts">
import type { HomeSlideDto } from '~/types/api/public-content';

const props = defineProps<{
  slides: HomeSlideDto[];
}>();

const currentSlideIndex = ref(0);
const currentSlide = computed<HomeSlideDto | null>(() => props.slides[currentSlideIndex.value] ?? props.slides[0] ?? null);
const isHeroPaused = ref(false);
const autoplayMs = 6500;
const heroStyle = computed<Record<string, string>>(() => ({
  '--hero-autoplay-duration': `${autoplayMs}ms`,
}));

const getHeroImageStyle = (slide: HomeSlideDto): Record<string, string> => ({
  '--hero-image': `url("${slide.imageUrl}")`,
});

let autoplayTimer: number | undefined;

const setSlide = (index: number): void => {
  if (props.slides.length === 0) {
    currentSlideIndex.value = 0;

    return;
  }

  const nextIndex = (index + props.slides.length) % props.slides.length;

  if (nextIndex === currentSlideIndex.value) {
    return;
  }

  currentSlideIndex.value = nextIndex;
};

const stopAutoplay = (): void => {
  if (autoplayTimer === undefined) {
    return;
  }

  window.clearInterval(autoplayTimer);
  autoplayTimer = undefined;
};

const startAutoplay = (): void => {
  if (props.slides.length < 2) {
    return;
  }

  stopAutoplay();
  autoplayTimer = window.setInterval(() => {
    if (isHeroPaused.value) {
      return;
    }

    setSlide(currentSlideIndex.value + 1);
  }, autoplayMs);
};

const restartAutoplay = (): void => {
  if (autoplayTimer === undefined) {
    return;
  }

  startAutoplay();
};

const showSlide = (index: number): void => {
  setSlide(index);
  restartAutoplay();
};

const pauseHero = (): void => {
  isHeroPaused.value = true;
};

const resumeHero = (): void => {
  isHeroPaused.value = false;
};

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  startAutoplay();
});

watch(
  () => props.slides.length,
  () => {
    currentSlideIndex.value = 0;
    restartAutoplay();
  },
);

onBeforeUnmount(stopAutoplay);
</script>

<template>
  <section v-if="currentSlide" class="home-hero-wrap">
    <div class="container">
      <div class="hero-shell">
        <button
          class="slider-arrow slider-prev"
          type="button"
          aria-label="Предыдущий слайд"
          @click="showSlide(currentSlideIndex - 1)"
        >
          <LayoutAppIcon name="back" />
        </button>

        <section
          id="about"
          class="hero"
          :class="{ 'is-paused': isHeroPaused }"
          :style="heroStyle"
          aria-labelledby="hero-title"
          @mouseenter="pauseHero"
          @mouseleave="resumeHero"
          @focusin="pauseHero"
          @focusout="resumeHero"
        >
          <div class="hero-media-stack" aria-hidden="true">
            <div
              v-for="(slide, index) in props.slides"
              :key="slide.id"
              class="hero-media"
              :class="{ 'is-active': index === currentSlideIndex }"
              :style="getHeroImageStyle(slide)"
            />
          </div>

          <div class="hero-effects" aria-hidden="true" />

          <div class="hero-content">
            <div class="hero-copy-frame" aria-live="polite" aria-atomic="true">
              <Transition name="hero-copy" mode="out-in">
                <div :key="currentSlideIndex" class="hero-copy">
                  <div class="hero-label">
                    <LayoutAppIcon name="shield" />
                    <span>{{ currentSlide.label }}</span>
                  </div>

                  <h1 id="hero-title">
                    {{ currentSlide.title }}
                  </h1>
                  <p class="hero-lead">
                    {{ currentSlide.lead }}
                  </p>

                  <div v-if="currentSlide.note" class="hero-note">
                    <span class="hero-note-icon">
                      <LayoutAppIcon name="shield" />
                    </span>
                    <strong>{{ currentSlide.note }}</strong>
                  </div>
                </div>
              </Transition>
            </div>

            <div class="phone-line">
              <LayoutAppIcon name="phone" />
              <a href="tel:+79102357746">8 910 235-77-46</a>
              <span>Звоните ежедневно с 9:00 до 20:00</span>
            </div>

            <div id="submit" class="hero-actions">
              <NuxtLink v-if="currentSlide.primaryCtaUrl && currentSlide.primaryCtaLabel" class="btn btn-red" :to="currentSlide.primaryCtaUrl">
                <LayoutAppIcon name="file" />
                {{ currentSlide.primaryCtaLabel }}
              </NuxtLink>
              <NuxtLink v-if="currentSlide.secondaryCtaUrl && currentSlide.secondaryCtaLabel" class="btn btn-outline" :to="currentSlide.secondaryCtaUrl" external>
                <LayoutAppIcon name="comment" />
                {{ currentSlide.secondaryCtaLabel }}
              </NuxtLink>
            </div>
          </div>

          <div class="slider-dots" role="tablist" aria-label="Слайды главного баннера">
            <button
              v-for="(slide, index) in props.slides"
              :key="slide.title"
              type="button"
              :class="{ active: index === currentSlideIndex }"
              :aria-label="`Слайд ${index + 1}`"
              :aria-current="index === currentSlideIndex ? 'true' : undefined"
              @click="showSlide(index)"
            />
          </div>
        </section>

        <button
          class="slider-arrow slider-next"
          type="button"
          aria-label="Следующий слайд"
          @click="showSlide(currentSlideIndex + 1)"
        >
          <LayoutAppIcon name="arrow" />
        </button>
      </div>
    </div>
  </section>
</template>
