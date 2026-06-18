<template>
  <section class="container top-panels">
    <article class="book-panel">
      <div class="book-cover">
        <img src="/assets/complaint-book-cover-clean.png" alt="Обложка народной жалобной книги" width="827" height="1203">
      </div>
      <div class="book-copy">
        <div class="title-row">
          <h2>Народная жалобная книга</h2>
          <span class="green-badge">Открытый реестр</span>
        </div>
        <p>Открытый реестр системных проблем и жалоб граждан. Ваш голос важен. Ваше обращение может изменить ситуацию.</p>
        <ul class="check-list">
          <li>Актуальные и резонансные проблемы</li>
          <li>Реальные истории и документы</li>
          <li>Статусы и результаты решений</li>
          <li>Влияние на общественный порядок</li>
        </ul>
        <NuxtLink class="btn btn-outline book-btn" to="/book">
          Перейти к книге
        </NuxtLink>
      </div>
    </article>

    <article class="geo-panel">
      <div class="geo-copy">
        <h2>География нашей работы</h2>
        <p>Мы работаем и защищаем права граждан в крупнейших городах России.</p>
        <strong>Работаем в Брянске, Москве и Санкт-Петербурге</strong>
      </div>
      <div class="russia-map">
        <img src="/assets/geo-russia-map.png" alt="Карта работы НКО: Брянск, Москва и Санкт-Петербург" width="1448" height="1086">
      </div>
    </article>
  </section>
</template>
