<script setup lang="ts">
type AdBanner = {
  src: string;
  alt: string;
};

const adTargetUrl = 'https://contract.gosuslugi.ru/';

const adBanners: [AdBanner, ...AdBanner[]] = [
  {
    src: '/assets/114a584a-17bb-4ecb-95fd-c338df16704e.png',
    alt: 'Поступай на контрактную службу в Вооруженные силы России',
  },
  {
    src: '/assets/6c216b42-b479-4748-a9d2-e67cfe37976d.png',
    alt: 'Время служить Родине, контрактная служба в Вооруженных силах Российской Федерации',
  },
  {
    src: '/assets/ec8b05b4-0bb3-4ce5-bdaf-6a964aeb2438.png',
    alt: 'Служи России, защищай Родину',
  },
];

const currentAdBannerIndex = ref(0);
const isChanging = ref(false);
let rotationTimer: number | null = null;
let animationTimer: number | null = null;

const currentAdBanner = computed<AdBanner>(() => adBanners[currentAdBannerIndex.value] ?? adBanners[0]);

function getRandomAdBannerIndex(): number {
  if (adBanners.length <= 1) {
    return 0;
  }

  let nextIndex = currentAdBannerIndex.value;

  while (nextIndex === currentAdBannerIndex.value) {
    nextIndex = Math.floor(Math.random() * adBanners.length);
  }

  return nextIndex;
}

function setAdBanner(index: number): void {
  currentAdBannerIndex.value = (index + adBanners.length) % adBanners.length;
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
  adBanners.forEach((banner) => {
    const image = new Image();
    image.src = banner.src;
  });

  setAdBanner(Math.floor(Math.random() * adBanners.length));

  rotationTimer = window.setInterval(() => {
    setAdBanner(getRandomAdBannerIndex());
  }, 15000);
});

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
  <section class="container promo-section" aria-labelledby="promo-section-title">
    <div class="promo-section-head">
      <h2 id="promo-section-title">Реклама</h2>
      <span>Партнерский блок</span>
    </div>
    <a
      :class="['promo-banner', { 'is-changing': isChanging }]"
      :href="adTargetUrl"
      :aria-label="`${currentAdBanner.alt}. Откроется в новом окне`"
      target="_blank"
      rel="noopener noreferrer"
      data-promo-rotator
      @click="handleAdClick"
    >
      <img
        :src="currentAdBanner.src"
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
