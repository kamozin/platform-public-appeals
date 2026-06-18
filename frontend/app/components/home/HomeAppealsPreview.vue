<script setup lang="ts">
type AppealPreview = {
  status: string;
  statusClass: string;
  image: string;
  imageAlt: string;
  category: string;
  title: string;
  place: string;
  date: string;
  views: string;
  likes: string;
  comments: string;
  to: string;
};

const appeals: AppealPreview[] = [
  {
    status: 'На проверке',
    statusClass: 'status-blue',
    image: '/assets/issue-entrance-roof.png',
    imageAlt: 'Протекает крыша в подъезде после дождя',
    category: 'ЖКХ',
    title: 'Протекает крыша в подъезде после дождя',
    place: 'г. Брянск, Советский район',
    date: '12 июня 2026, 14:30',
    views: '1 245',
    likes: '64',
    comments: '23',
    to: '/appeals/entrance-roof-leak-after-rain',
  },
  {
    status: 'В работе',
    statusClass: 'status-orange',
    image: '/assets/issue-road.png',
    imageAlt: 'Ямы на дороге после ремонта',
    category: 'Дороги',
    title: 'Ямы на дороге после ремонта',
    place: 'г. Москва, ЮВАО',
    date: '14 июня 2026, 10:15',
    views: '2 134',
    likes: '118',
    comments: '37',
    to: '/appeals/road-pits-after-repair',
  },
  {
    status: 'Решено',
    statusClass: 'status-green',
    image: '/assets/issue-sport-roof.png',
    imageAlt: 'Свалка строительного мусора во дворе',
    category: 'Благоустройство',
    title: 'Свалка строительного мусора во дворе',
    place: 'г. Санкт-Петербург, Невский район',
    date: '5 июня 2026, 11:20',
    views: '892',
    likes: '41',
    comments: '18',
    to: '/appeals/construction-waste-in-yard',
  },
];

const trackRef = ref<HTMLElement | null>(null);

const scrollCards = (direction: 'next' | 'prev'): void => {
  const track = trackRef.value;

  if (!track) {
    return;
  }

  const step = track.clientWidth * 0.9;
  track.scrollBy({
    left: direction === 'next' ? step : -step,
    behavior: 'smooth',
  });
};
</script>

<template>
  <section class="container feed-section" aria-labelledby="feed-title">
    <div class="section-head inline">
      <div class="feed-heading">
        <h2 id="feed-title">Обращения из городов работы</h2>
        <p>Проверенные ситуации из Брянска, Москвы и Санкт-Петербурга с понятным статусом и общественным откликом.</p>
      </div>
      <div class="feed-actions">
        <div class="feed-city-list" aria-label="Города работы">
          <span><LayoutAppIcon name="pin" />Брянск</span>
          <span><LayoutAppIcon name="pin" />Москва</span>
          <span><LayoutAppIcon name="pin" />Санкт-Петербург</span>
        </div>
        <div class="feed-nav-row">
          <div class="feed-carousel-controls" aria-label="Навигация по обращениям">
            <button type="button" aria-label="Предыдущие обращения" @click="scrollCards('prev')">
              <LayoutAppIcon name="back" />
            </button>
            <button type="button" aria-label="Следующие обращения" @click="scrollCards('next')">
              <LayoutAppIcon name="arrow" />
            </button>
          </div>
          <NuxtLink to="/appeals">
            Смотреть все обращения
            <LayoutAppIcon name="arrow" />
          </NuxtLink>
        </div>
      </div>
    </div>

    <div class="appeal-carousel" aria-label="Лента обращений из городов работы">
      <div ref="trackRef" class="appeal-cards" tabindex="0">
        <article v-for="appeal in appeals" :key="appeal.title" class="appeal-card">
          <span class="status" :class="appeal.statusClass">{{ appeal.status }}</span>
          <NuxtLink :to="appeal.to">
            <img :src="appeal.image" :alt="appeal.imageAlt" loading="lazy" decoding="async">
          </NuxtLink>
          <div class="appeal-copy">
            <span>{{ appeal.category }}</span>
            <h3>
              <NuxtLink :to="appeal.to">
                {{ appeal.title }}
              </NuxtLink>
            </h3>
            <p>{{ appeal.place }}<br>{{ appeal.date }}</p>
          </div>
          <footer>
            <span><LayoutAppIcon name="eye" />{{ appeal.views }}</span>
            <span class="appeal-like"><LayoutAppIcon name="heart" />{{ appeal.likes }}</span>
            <span><LayoutAppIcon name="comment" />{{ appeal.comments }}</span>
            <button type="button" aria-label="Поделиться">
              <LayoutAppIcon name="share" />
            </button>
          </footer>
        </article>
      </div>
    </div>
  </section>
</template>
