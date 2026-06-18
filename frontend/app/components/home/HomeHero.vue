<script setup lang="ts">
type HeroSlide = {
  label: string;
  title: string;
  lead: string;
  note: string;
  image: string;
};

const slides: HeroSlide[] = [
  {
    label: 'Платформа обращений и жалоб граждан',
    title: 'Вместе решаем проблемы людей и защищаем их права',
    lead: 'Помогаем гражданам с обращениями и жалобами, добиваемся справедливости и поддержки, защищаем права и интересы людей.',
    note: 'Лично доставляем жалобы депутатам Госдумы РФ, руководителям МВД, АП, губернаторам, сенаторам. С представителем правозащитников в суд.',
    image: '/assets/hero-civic-flag.png',
  },
  {
    label: 'Общественный контроль и поддержка',
    title: 'Помогаем обращениям получить официальный ход',
    lead: 'Фиксируем проблему, готовим документы и сопровождаем обращение до понятного статуса и результата.',
    note: 'Проверяем факты, собираем подтверждения и направляем жалобы в профильные ведомства и общественные приемные.',
    image: '/assets/hero-legal-consultation.png',
  },
  {
    label: 'Прозрачный реестр обращений',
    title: 'Показываем ход работы и результаты решений',
    lead: 'Публикуем проверенные обращения, статусы и важные обновления, чтобы граждане видели движение по проблеме.',
    note: 'Каждое обращение проходит модерацию, получает категорию и становится частью открытой системы общественного контроля.',
    image: '/assets/hero-community-meeting.png',
  },
];

const currentSlideIndex = ref(0);
const currentSlide = computed<HeroSlide>(() => slides[currentSlideIndex.value] ?? slides[0]!);
const heroStyle = computed(() => ({ '--hero-image': `url("${currentSlide.value.image}")` }));

const setSlide = (index: number): void => {
  currentSlideIndex.value = (index + slides.length) % slides.length;
};
</script>

<template>
  <section class="home-hero-wrap">
    <button
      class="slider-arrow slider-prev"
      type="button"
      aria-label="Предыдущий слайд"
      @click="setSlide(currentSlideIndex - 1)"
    >
      <LayoutAppIcon name="back" />
    </button>

    <div class="container">
      <section
        id="about"
        class="hero"
        :style="heroStyle"
        aria-labelledby="hero-title"
      >
        <div class="hero-content">
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

          <div class="hero-note">
            <span class="hero-note-icon">
              <LayoutAppIcon name="shield" />
            </span>
            <strong>{{ currentSlide.note }}</strong>
          </div>

          <div class="phone-line">
            <LayoutAppIcon name="phone" />
            <a href="tel:+79102357746">8 910 235-77-46</a>
            <span>Звоните ежедневно с 9:00 до 20:00</span>
          </div>

          <div id="submit" class="hero-actions">
            <NuxtLink class="btn btn-red" to="/appeal/new">
              <LayoutAppIcon name="file" />
              Подать обращение / жалобу
            </NuxtLink>
            <NuxtLink class="btn btn-outline" to="/appeals" external>
              <LayoutAppIcon name="comment" />
              Смотреть обращения
            </NuxtLink>
          </div>
        </div>

        <div class="slider-dots" role="tablist" aria-label="Слайды главного баннера">
          <button
            v-for="(slide, index) in slides"
            :key="slide.title"
            type="button"
            :class="{ active: index === currentSlideIndex }"
            :aria-label="`Слайд ${index + 1}`"
            :aria-current="index === currentSlideIndex ? 'true' : undefined"
            @click="setSlide(index)"
          />
        </div>
      </section>
    </div>

    <button
      class="slider-arrow slider-next"
      type="button"
      aria-label="Следующий слайд"
      @click="setSlide(currentSlideIndex + 1)"
    >
      <LayoutAppIcon name="arrow" />
    </button>
  </section>
</template>
