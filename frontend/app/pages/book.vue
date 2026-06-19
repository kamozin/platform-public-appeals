<script setup lang="ts">
const principles = [
  'Актуальные и резонансные проблемы граждан',
  'Проверенные факты, документы и публичные статусы',
  'Истории обращений, которые помогают увидеть системные проблемы',
  'Открытый подход к результатам и дальнейшим действиям',
] as const;

const sections = [
  {
    title: 'Что попадает в книгу',
    text: 'В книгу могут попадать обращения, которые прошли модерацию и представляют общественный интерес: проблемы ЖКХ, дорог, благоустройства, образования, здравоохранения и защиты прав граждан.',
  },
  {
    title: 'Как используется информация',
    text: 'Материалы помогают фиксировать повторяющиеся проблемы, готовить обращения в ответственные органы и показывать гражданам, как движется работа по похожим ситуациям.',
  },
  {
    title: 'Что будет дальше',
    text: 'Полноценный публичный реестр и фильтры будут подключены отдельными задачами после появления backend API для обращений и категорий.',
  },
] as const;

usePageSeo({
  title: 'Народная жалобная книга',
  description: 'Публичная страница народной жалобной книги проекта Рука добра: открытый реестр системных проблем и обращений граждан.',
  path: '/book',
});
</script>

<template>
  <article class="content-page book-page">
    <div class="container content-page__inner">
      <nav class="content-breadcrumb" aria-label="Хлебные крошки">
        <NuxtLink to="/">
          Главная
        </NuxtLink>
        <span>Народная жалобная книга</span>
      </nav>

      <header class="content-hero book-hero">
        <div>
          <p class="content-eyebrow">
            Открытый реестр
          </p>
          <h1>Народная жалобная книга</h1>
          <p>Открытый реестр системных проблем и жалоб граждан. Ваш голос важен. Ваше обращение может изменить ситуацию.</p>
          <div class="book-hero-actions" aria-label="Действия с жалобной книгой">
            <NuxtLink class="btn btn-red" to="/appeals">
              Смотреть обращения
            </NuxtLink>
            <NuxtLink class="btn btn-outline" to="/appeal/new">
              Подать обращение
            </NuxtLink>
          </div>
        </div>
        <img src="/assets/complaint-book-cover-clean.png" alt="Обложка народной жалобной книги" width="827" height="1203">
      </header>

      <section class="content-notice" aria-label="Статус страницы">
        <strong>Предварительная публичная страница</strong>
        <p>Сейчас это статичная SSR-страница без backend API. Реальный реестр обращений будет подключён отдельной задачей.</p>
      </section>

      <section class="book-principles" aria-label="Принципы жалобной книги">
        <article v-for="principle in principles" :key="principle">
          <LayoutAppIcon name="check" />
          <span>{{ principle }}</span>
        </article>
      </section>

      <div class="content-card book-sections">
        <section v-for="section in sections" :key="section.title">
          <h2>{{ section.title }}</h2>
          <p>{{ section.text }}</p>
        </section>
      </div>
    </div>
  </article>
</template>
