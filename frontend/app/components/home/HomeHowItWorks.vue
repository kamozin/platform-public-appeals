<script setup lang="ts">
const steps = [
  {
    icon: 'grid',
    title: 'Выберите тему',
    text: 'Подскажем, куда направить жалобу и какие данные нужны.',
  },
  {
    icon: 'edit',
    title: 'Опишите проблему',
    text: 'Добавьте адрес, факты, даты и контакты для обратной связи.',
  },
  {
    icon: 'camera',
    title: 'Приложите доказательства',
    text: 'Фото, видео и документы помогут быстрее подтвердить ситуацию.',
  },
  {
    icon: 'bell',
    title: 'Следите за результатом',
    text: 'Статус обращения, обновления и ответы будут видны на платформе.',
  },
] as const;
</script>

<template>
  <section id="how" class="container how-section" aria-labelledby="how-title">
    <div class="how-head">
      <div>
        <span class="section-eyebrow">
          <LayoutAppIcon name="bolt" />
          Быстрый старт
        </span>
        <h2 id="how-title">Как подать обращение за несколько минут</h2>
        <p>Не нужно разбираться в ведомствах заранее. Начните с проблемы, а мы проведём по понятному маршруту.</p>
      </div>
      <NuxtLink class="btn btn-red how-cta" to="/appeal/new">
        Начать обращение
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
    </div>

    <ol class="steps">
      <li v-for="(step, index) in steps" :key="step.title">
        <span class="step-num">{{ String(index + 1).padStart(2, '0') }}</span>
        <span class="step-icon">
          <LayoutAppIcon :name="step.icon" />
        </span>
        <strong>{{ step.title }}</strong>
        <p>{{ step.text }}</p>
      </li>
    </ol>

    <div class="how-assurance" aria-label="Преимущества подачи обращения">
      <span><LayoutAppIcon name="check" />Бесплатно</span>
      <span><LayoutAppIcon name="shield" />Проверяем факты</span>
      <span><LayoutAppIcon name="clock" />Статус онлайн</span>
    </div>
  </section>
</template>
