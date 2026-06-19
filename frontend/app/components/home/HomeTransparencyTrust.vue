<script setup lang="ts">
const isTransparencyVisible = false;

const trustItems = [
  {
    icon: 'shield',
    title: 'Правовая поддержка',
    text: 'Наши юристы помогают гражданам в сложных ситуациях, готовят обращения и представляют интересы в суде.',
    to: '/news/legal-support',
    aria: 'Подробнее о правовой поддержке',
  },
  {
    icon: 'users',
    title: 'Общественный резонанс',
    text: 'Привлекаем внимание СМИ и общественности к важным проблемам, чтобы добиться реальных изменений.',
    to: '/news/public-control',
    aria: 'Подробнее об общественном резонансе',
  },
  {
    icon: 'pin',
    title: 'Работаем в Брянске, Москве и Санкт-Петербурге',
    text: 'Единая платформа для граждан в трех ключевых городах. Вместе мы сильнее.',
    to: '/news/regions',
    aria: 'Подробнее о городах работы',
  },
] as const;
</script>

<template>
  <section v-if="isTransparencyVisible" class="container transparency" aria-labelledby="transparency-title">
    <div class="transparency-main">
      <div class="transparency-icon">
        <LayoutAppIcon name="flag" />
      </div>
      <div class="transparency-copy">
        <h2 id="transparency-title">Публичность и прозрачность</h2>
        <p>Проверенные обращения и жалобы публикуются на платформе. Вы можете отслеживать ход рассмотрения и видеть результаты работы с вашей проблемой.</p>
      </div>
    </div>
    <ul class="check-list compact">
      <li>Модерация и проверка обращений и жалоб</li>
      <li>Публичный реестр обращений и жалоб</li>
      <li>Открытые статусы и результаты</li>
    </ul>
  </section>

  <section class="container trust-grid">
    <article v-for="item in trustItems" :key="item.title">
      <LayoutAppIcon :name="item.icon" />
      <div>
        <h3>{{ item.title }}</h3>
        <p>{{ item.text }}</p>
        <NuxtLink :to="item.to" :aria-label="item.aria" external>
          Подробнее
          <LayoutAppIcon name="arrow" />
        </NuxtLink>
      </div>
    </article>
  </section>
</template>
