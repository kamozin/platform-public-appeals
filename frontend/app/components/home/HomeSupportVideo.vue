<script setup lang="ts">
type SupportVideo = {
  title: string;
  text: string;
  image: string;
  badge: string;
  featured?: boolean;
};

const videos: SupportVideo[] = [
  {
    title: 'Слова поддержки нашим бойцам',
    text: 'Личные обращения граждан с благодарностью за мужество, службу и защиту людей.',
    image: '/assets/hero-hands-heart.png',
    badge: 'Видео от граждан',
    featured: true,
  },
  {
    title: 'Обращения жителей',
    text: 'Видео с тёплыми словами для бойцов СВО, ветеранов и их семей.',
    image: '/assets/hero-community-meeting.png',
    badge: 'Скоро',
  },
  {
    title: 'Спасибо ветеранам',
    text: 'Короткие ролики о поддержке, уважении и помощи тем, кто прошёл службу.',
    image: '/assets/hero-civic-flag.png',
    badge: 'Скоро',
  },
];

const api = useApi();
const email = ref('');
const file = ref<File | null>(null);
const status = ref('');
const error = ref('');

const handleFileChange = (event: Event): void => {
  const input = event.target;

  if (!(input instanceof HTMLInputElement)) {
    return;
  }

  file.value = input.files?.[0] ?? null;
};

const submitVideo = async (): Promise<void> => {
  status.value = '';
  error.value = '';

  if (!file.value) {
    error.value = 'Выберите MP4 или MOV файл.';

    return;
  }

  const body = new FormData();
  body.append('video', file.value);

  if (email.value !== '') {
    body.append('email', email.value);
  }

  try {
    await api.request('/support-videos', 'POST', { body });
    file.value = null;
    email.value = '';
    status.value = 'Видео отправлено на модерацию.';
  } catch {
    error.value = 'Не удалось отправить видео. Проверьте тип и размер файла.';
  }
};
</script>

<template>
  <section class="container support-video-section" aria-labelledby="support-video-title">
    <div class="support-video-head">
      <div class="support-video-heading">
        <span class="support-video-eyebrow">
          <LayoutAppIcon name="camera" />
          Видео от граждан
        </span>
        <h2 id="support-video-title">В поддержку бойцов СВО и ветеранов</h2>
        <p>Собираем короткие видеообращения со словами благодарности, поддержки и уважения от жителей разных городов.</p>
      </div>
      <form class="support-video-form" @submit.prevent="submitVideo">
        <label>
          <span class="sr-only">Email для связи</span>
          <input v-model="email" type="email" placeholder="Email для связи">
        </label>
        <label class="support-video-file">
          <LayoutAppIcon name="upload" />
          <span>{{ file?.name || 'Выбрать видео' }}</span>
          <input type="file" accept="video/mp4,video/quicktime" @change="handleFileChange">
        </label>
        <button class="btn btn-outline support-video-submit" type="submit">
          Прислать видео
        </button>
        <p v-if="status" class="form-message form-message--success">{{ status }}</p>
        <p v-if="error" class="form-message form-message--error">{{ error }}</p>
      </form>
    </div>

    <div class="support-video-grid">
      <article
        v-for="video in videos"
        :key="video.title"
        class="support-video-card"
        :class="{ 'support-video-featured': video.featured }"
      >
        <div class="support-video-media">
          <img :src="video.image" alt="" width="1536" height="864" loading="lazy" decoding="async">
          <span class="support-video-play" aria-hidden="true">
            <LayoutAppIcon name="play" />
          </span>
          <span class="support-video-badge">{{ video.badge }}</span>
        </div>
        <div class="support-video-body">
          <h3>{{ video.title }}</h3>
          <p>{{ video.text }}</p>
        </div>
      </article>
    </div>
  </section>
</template>
