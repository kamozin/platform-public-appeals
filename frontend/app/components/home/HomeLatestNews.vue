<script setup lang="ts">
type NewsPreview = {
  title: string;
  text: string;
  image: string;
  imageAlt: string;
  date: string;
  datetime: string;
  category: string;
  to: string;
  featured?: boolean;
};

const news: NewsPreview[] = [
  {
    title: 'Открыта запись на консультации по обращениям в суд',
    text: 'Юристы помогают подготовить документы, уточнить сроки и выбрать порядок подачи жалобы.',
    image: '/assets/hero-legal-consultation.png',
    imageAlt: 'Юридическая консультация для граждан',
    date: '17 июня 2026',
    datetime: '2026-06-17',
    category: 'Правовая поддержка',
    to: '/news/legal-support-consultations',
    featured: true,
  },
  {
    title: 'Запущены выездные приемы по проблемам ЖКХ',
    text: 'Команда собирает обращения жителей и фиксирует системные проблемы для публичного реестра.',
    image: '/assets/hero-community-meeting.png',
    imageAlt: 'Встреча с жителями по общественному контролю',
    date: '14 июня 2026',
    datetime: '2026-06-14',
    category: 'Общественный контроль',
    to: '/news/public-control-visits',
  },
  {
    title: 'Опубликован отчет по обращениям за май',
    text: 'В отчете собраны статусы, решенные вопросы и обращения, которые требуют дополнительной проверки.',
    image: '/assets/hero-hands-heart.png',
    imageAlt: 'Поддержка граждан и результаты работы',
    date: '10 июня 2026',
    datetime: '2026-06-10',
    category: 'Результаты',
    to: '/news/may-appeals-results-report',
  },
];
</script>

<template>
  <section id="news" class="container latest-news" aria-labelledby="latest-news-title">
    <div class="section-head inline">
      <div class="latest-news-heading">
        <h2 id="latest-news-title">Последние новости</h2>
        <p>Коротко о новых инициативах, результатах обращений и работе команды «Рука добра».</p>
      </div>
      <NuxtLink to="/news">
        Все новости
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
    </div>

    <div class="latest-news-grid">
      <article
        v-for="item in news"
        :key="item.title"
        class="news-card"
        :class="{ 'news-card-featured': item.featured }"
      >
        <NuxtLink class="news-card-media" :to="item.to">
          <img :src="item.image" :alt="item.imageAlt" width="1536" height="864" loading="lazy" decoding="async">
        </NuxtLink>
        <div class="news-card-body">
          <div class="news-meta">
            <time :datetime="item.datetime">{{ item.date }}</time>
            <span>{{ item.category }}</span>
          </div>
          <h3>
            <NuxtLink :to="item.to">
              {{ item.title }}
            </NuxtLink>
          </h3>
          <p>{{ item.text }}</p>
        </div>
      </article>
    </div>
  </section>
</template>
