<script setup lang="ts">
import type { NuxtError } from '#app';
import type { AppIconName } from '~/components/layout/AppIcon.vue';

type RecoveryAction = {
  title: string;
  text: string;
  path: string;
  icon: AppIconName;
};

const props = defineProps<{
  error: NuxtError;
}>();

const statusCode = computed(() => props.error.status ?? props.error.statusCode ?? 500);
const isNotFound = computed(() => statusCode.value === 404);

const pageTitle = computed(() => {
  if (isNotFound.value) {
    return 'Страница не найдена';
  }

  return 'Раздел временно недоступен';
});

const pageDescription = computed(() => {
  if (isNotFound.value) {
    return 'Адрес мог измениться, страница могла быть снята с публикации или ссылка была указана с ошибкой.';
  }

  return 'Мы уже знаем, что этот раздел открылся некорректно. Попробуйте вернуться к основным разделам сайта.';
});

const eyebrow = computed(() => {
  if (isNotFound.value) {
    return 'Ошибка 404';
  }

  return `Ошибка ${statusCode.value}`;
});

const actions: readonly RecoveryAction[] = [
  {
    title: 'Смотреть обращения',
    text: 'Перейдите к публичной ленте и найдите похожие ситуации по темам и статусам.',
    path: '/appeals',
    icon: 'file',
  },
  {
    title: 'Выбрать категорию',
    text: 'Откройте список направлений, чтобы точнее определить тему будущего обращения.',
    path: '/categories',
    icon: 'grid',
  },
  {
    title: 'Народная книга',
    text: 'Посмотрите, как проект собирает и показывает системные проблемы граждан.',
    path: '/book',
    icon: 'book',
  },
] as const;

const redirectTo = async (path: string): Promise<void> => {
  await clearError({ redirect: path });
};

useSeoMeta({
  title: () => pageTitle.value,
  description: () => pageDescription.value,
  ogTitle: () => pageTitle.value,
  ogDescription: () => pageDescription.value,
  robots: 'noindex,nofollow',
});
</script>

<template>
  <NuxtLayout>
    <article class="content-page not-found-page">
      <div class="container content-page__inner">
        <nav class="content-breadcrumb" aria-label="Навигационная цепочка">
          <button type="button" @click="redirectTo('/')">
            Главная
          </button>
          <span>{{ pageTitle }}</span>
        </nav>

        <header class="not-found-hero" aria-labelledby="not-found-title">
          <div class="not-found-copy">
            <p class="content-eyebrow">
              {{ eyebrow }}
            </p>
            <h1 id="not-found-title">{{ pageTitle }}</h1>
            <p>{{ pageDescription }}</p>

            <div class="not-found-buttons" aria-label="Основные действия">
              <button class="btn btn-red" type="button" @click="redirectTo('/')">
                <LayoutAppIcon name="home" />
                На главную
              </button>
              <button class="btn btn-outline" type="button" @click="redirectTo('/appeals')">
                <LayoutAppIcon name="arrow" />
                Смотреть обращения
              </button>
            </div>
          </div>

          <div class="not-found-visual" aria-hidden="true">
            <span>{{ statusCode }}</span>
            <LayoutAppIcon name="flag" />
          </div>
        </header>

        <section class="not-found-actions" aria-label="Полезные разделы">
          <button
            v-for="action in actions"
            :key="action.path"
            class="not-found-action"
            type="button"
            @click="redirectTo(action.path)"
          >
            <LayoutAppIcon :name="action.icon" />
            <span>
              <strong>{{ action.title }}</strong>
              <small>{{ action.text }}</small>
            </span>
            <LayoutAppIcon name="arrow" />
          </button>
        </section>
      </div>
    </article>
  </NuxtLayout>
</template>
