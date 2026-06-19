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
  {
    status: 'В работе',
    statusClass: 'status-orange',
    image: '/assets/issue-entrance-roof.png',
    imageAlt: 'Сырость и плесень в подъезде',
    category: 'ЖКХ',
    title: 'Сырость и плесень в подъезде',
    place: 'г. Брянск, Фокинский район',
    date: '4 июня 2026, 16:40',
    views: '734',
    likes: '52',
    comments: '15',
    to: '/appeals/damp-and-mold-in-entrance',
  },
  {
    status: 'На проверке',
    statusClass: 'status-blue',
    image: '/assets/issue-road.png',
    imageAlt: 'Разбитый тротуар возле остановки',
    category: 'Дороги',
    title: 'Разбитый тротуар возле остановки',
    place: 'г. Москва, район Люблино',
    date: '2 июня 2026, 09:25',
    views: '1 487',
    likes: '86',
    comments: '31',
    to: '/appeals/broken-sidewalk-near-stop',
  },
  {
    status: 'Решено',
    statusClass: 'status-green',
    image: '/assets/issue-sport-roof.png',
    imageAlt: 'Повреждённое покрытие на спортивной площадке',
    category: 'Спорт',
    title: 'Повреждённое покрытие на площадке',
    place: 'г. Санкт-Петербург, Калининский район',
    date: '28 мая 2026, 12:10',
    views: '963',
    likes: '47',
    comments: '12',
    to: '/appeals/damaged-playground-surface',
  },
];

const trackRef = ref<HTMLElement | null>(null);
const activePage = ref(0);
const pageCount = ref(1);
let resizeObserver: ResizeObserver | null = null;

const getCardStep = (): number => {
  const track = trackRef.value;
  const firstCard = track?.querySelector<HTMLElement>('.appeal-card');
  const secondCard = firstCard?.nextElementSibling;

  if (firstCard && secondCard instanceof HTMLElement) {
    return secondCard.offsetLeft - firstCard.offsetLeft;
  }

  return firstCard?.getBoundingClientRect().width ?? 0;
};

const getVisibleCount = (): number => {
  const track = trackRef.value;
  const step = getCardStep();

  if (!track || step <= 0) {
    return 1;
  }

  return Math.max(1, Math.round(track.clientWidth / step));
};

const syncCarousel = (): void => {
  const track = trackRef.value;
  const step = getCardStep();
  const visibleCount = getVisibleCount();

  pageCount.value = Math.max(1, Math.ceil(appeals.length / visibleCount));

  if (!track || step <= 0) {
    activePage.value = 0;

    return;
  }

  const activeIndex = Math.round(track.scrollLeft / step);
  activePage.value = Math.max(0, Math.min(Math.round(activeIndex / visibleCount), pageCount.value - 1));
};

const scrollToPage = (page: number, behavior: ScrollBehavior = 'smooth'): void => {
  const track = trackRef.value;
  const firstCard = track?.querySelector<HTMLElement>('.appeal-card');
  const visibleCount = getVisibleCount();
  const targetPage = Math.max(0, Math.min(page, pageCount.value - 1));

  if (!track || !firstCard) {
    return;
  }

  const targetIndex = Math.min(targetPage * visibleCount, appeals.length - 1);
  const targetCard = track.querySelectorAll<HTMLElement>('.appeal-card')[targetIndex];

  if (!targetCard) {
    return;
  }

  track.scrollTo({
    left: targetCard.offsetLeft - firstCard.offsetLeft,
    behavior,
  });

  activePage.value = targetPage;
};

const scrollCards = (direction: 'next' | 'prev'): void => {
  scrollToPage(activePage.value + (direction === 'next' ? 1 : -1));
};

onMounted(() => {
  syncCarousel();

  if (trackRef.value) {
    resizeObserver = new ResizeObserver(() => {
      syncCarousel();
      scrollToPage(activePage.value, 'auto');
    });
    resizeObserver.observe(trackRef.value);
  }
});

onBeforeUnmount(() => {
  resizeObserver?.disconnect();
});
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
            <button type="button" aria-label="Предыдущие обращения" :disabled="activePage === 0" @click="scrollCards('prev')">
              <LayoutAppIcon name="back" />
            </button>
            <button type="button" aria-label="Следующие обращения" :disabled="activePage >= pageCount - 1" @click="scrollCards('next')">
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
      <div ref="trackRef" class="appeal-cards" tabindex="0" @scroll.passive="syncCarousel">
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
      <div class="appeal-carousel-dots" aria-label="Страницы ленты обращений">
        <button
          v-for="page in pageCount"
          :key="page"
          type="button"
          :class="{ active: page - 1 === activePage }"
          :aria-label="`Страница ${page}`"
          :aria-current="page - 1 === activePage ? 'true' : undefined"
          @click="scrollToPage(page - 1)"
        />
      </div>
    </div>
  </section>
</template>
