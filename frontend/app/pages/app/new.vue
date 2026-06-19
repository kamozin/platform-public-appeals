<script setup lang="ts">
definePageMeta({
  layout: 'pwa',
});

useNoindexSeo();
useHead({
  title: 'Новое обращение | Рука добра',
});

const commonCategories = [
  'ЖКХ',
  'Дороги',
  'Медицина',
  'Благоустройство',
  'Образование',
  'Социальная защита',
] as const;
</script>

<template>
  <section class="pwa-screen" aria-labelledby="pwa-new-title">
    <div class="pwa-hero-panel pwa-hero-panel--red">
      <div>
        <p class="pwa-eyebrow">
          Новое обращение
        </p>
        <h1 id="pwa-new-title">
          Сообщить о проблеме
        </h1>
      </div>
      <LayoutAppIcon name="edit" />
    </div>

    <section class="pwa-panel" aria-labelledby="pwa-new-categories-title">
      <div class="pwa-section-heading">
        <h2 id="pwa-new-categories-title">
          Частые категории
        </h2>
      </div>
      <div class="pwa-chip-grid">
        <span v-for="category in commonCategories" :key="category" class="pwa-chip">
          {{ category }}
        </span>
      </div>
    </section>

    <section class="pwa-panel" aria-labelledby="pwa-new-flow-title">
      <div class="pwa-section-heading">
        <h2 id="pwa-new-flow-title">
          Заявка
        </h2>
      </div>
      <div class="pwa-step-list">
        <div class="pwa-step">
          <span>1</span>
          <p>Опишите ситуацию и место.</p>
        </div>
        <div class="pwa-step">
          <span>2</span>
          <p>Добавьте фото или документы.</p>
        </div>
        <div class="pwa-step">
          <span>3</span>
          <p>Отправьте обращение на проверку.</p>
        </div>
      </div>
    </section>

    <NuxtLink class="pwa-wide-action" to="/appeal/new">
      <LayoutAppIcon name="plus" />
      Продолжить
    </NuxtLink>
  </section>
</template>
