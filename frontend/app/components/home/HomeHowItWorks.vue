<script setup lang="ts">
const steps = [
  { icon: 'grid', text: 'Выберите категорию' },
  { icon: 'file', text: 'Опишите проблему или жалобу' },
  { icon: 'camera', text: 'Добавьте документы и фото/видео' },
  { icon: 'arrow', text: 'Подайте обращение или жалобу' },
  { icon: 'bell', text: 'Получайте статус' },
  { icon: 'check', text: 'Дождитесь результата' },
] as const;
</script>

<template>
  <section id="how" class="container how-section" aria-labelledby="how-title">
    <h2 id="how-title">Как это работает</h2>
    <ol class="steps">
      <li v-for="(step, index) in steps" :key="step.text">
        <span class="step-num">{{ index + 1 }}</span>
        <span class="step-icon">
          <LayoutAppIcon :name="step.icon" />
        </span>
        <strong>{{ step.text }}</strong>
      </li>
    </ol>
  </section>
</template>
