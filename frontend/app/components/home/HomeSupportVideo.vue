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
const fileInputRef = ref<HTMLInputElement | null>(null);
const modalPanelRef = ref<HTMLElement | null>(null);
const isModalOpen = ref(false);
const isSubmitting = ref(false);
const status = ref('');
const error = ref('');

const handleFileChange = (event: Event): void => {
  const input = event.target;

  if (!(input instanceof HTMLInputElement)) {
    return;
  }

  file.value = input.files?.[0] ?? null;
};

const openVideoModal = (): void => {
  isModalOpen.value = true;
  status.value = '';
  error.value = '';
};

const closeVideoModal = (): void => {
  isModalOpen.value = false;
};

const handleKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape' && isModalOpen.value) {
    closeVideoModal();
  }
};

const submitVideo = async (): Promise<void> => {
  if (isSubmitting.value) {
    return;
  }

  status.value = '';
  error.value = '';

  if (!file.value) {
    error.value = 'Выберите MP4 или MOV файл.';

    return;
  }

  isSubmitting.value = true;

  const body = new FormData();
  body.append('video', file.value);

  if (email.value !== '') {
    body.append('email', email.value);
  }

  try {
    await api.request('/support-videos', 'POST', { body });
    file.value = null;
    email.value = '';
    if (fileInputRef.value) {
      fileInputRef.value.value = '';
    }
    isSubmitting.value = false;
    status.value = 'Видео отправлено на модерацию.';
  } catch {
    isSubmitting.value = false;
    error.value = 'Не удалось отправить видео. Проверьте тип и размер файла.';
  }
};

watch(
  isModalOpen,
  async (open) => {
    if (!import.meta.client) {
      return;
    }

    document.body.classList.toggle('modal-open', open);

    if (!open) {
      return;
    }

    await nextTick();
    modalPanelRef.value?.focus();
  },
);

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  document.body.classList.remove('modal-open');
  window.removeEventListener('keydown', handleKeydown);
});
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
      <div class="support-video-actions">
        <button class="btn btn-outline support-video-submit" type="button" @click="openVideoModal">
          <LayoutAppIcon name="upload" />
          Прислать видео
        </button>
      </div>
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

    <Teleport to="body">
      <div v-if="isModalOpen" class="modal video-submit-modal">
        <button
          class="modal-backdrop"
          type="button"
          aria-label="Закрыть окно"
          @click="closeVideoModal"
        />

        <section
          ref="modalPanelRef"
          class="modal-panel video-submit-panel"
          role="dialog"
          aria-modal="true"
          aria-labelledby="video-modal-title"
          aria-describedby="video-modal-text"
          tabindex="-1"
        >
          <button
            class="modal-close"
            type="button"
            aria-label="Закрыть окно"
            @click="closeVideoModal"
          >
            <span aria-hidden="true">×</span>
          </button>

          <span class="video-submit-eyebrow">
            <LayoutAppIcon name="camera" />
            Видео от граждан
          </span>
          <h2 id="video-modal-title">Прислать видео поддержки</h2>
          <p id="video-modal-text">
            Прикрепите MP4 или MOV файл до 100 МБ. После модерации видео сможет появиться в разделе поддержки.
          </p>

          <form class="video-submit-form" @submit.prevent="submitVideo">
            <div class="video-submit-grid">
              <label class="video-submit-wide">
                <span>Email для связи</span>
                <input v-model="email" type="email" autocomplete="email" placeholder="example@mail.ru">
              </label>

              <label class="video-file-upload">
                <input
                  ref="fileInputRef"
                  class="sr-only"
                  type="file"
                  accept=".mp4,.mov,video/mp4,video/quicktime"
                  required
                  @change="handleFileChange"
                >
                <span class="video-file-icon" aria-hidden="true">
                  <LayoutAppIcon name="upload" />
                </span>
                <span class="video-file-copy">
                  <strong>Прикрепить видео</strong>
                  <small>{{ file?.name || 'MP4 или MOV до 100 МБ' }}</small>
                </span>
              </label>
            </div>

            <div class="video-submit-actions">
              <button class="btn btn-red" type="submit" :disabled="isSubmitting">
                <LayoutAppIcon name="check" />
                {{ isSubmitting ? 'Отправляем...' : 'Отправить на модерацию' }}
              </button>
              <button class="btn btn-outline" type="button" @click="closeVideoModal">
                Отмена
              </button>
            </div>

            <p v-if="status" class="video-submit-status" aria-live="polite">{{ status }}</p>
            <p v-if="error" class="video-submit-status video-submit-status--error" aria-live="polite">{{ error }}</p>
          </form>
        </section>
      </div>
    </Teleport>
  </section>
</template>
