<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealCommentDto, AppealDetailDto, AppealStatus } from '~/types/api/public-content';
import { formatRuDateTime, formatRuNumber } from '~/utils/formatters';

const route = useRoute();
const routeSlug = route.params.slug;
const slug = Array.isArray(routeSlug) ? routeSlug[0] : routeSlug;

if (!slug) {
  throw createError({
    statusCode: 404,
    statusMessage: 'Appeal not found',
  });
}

const api = useApi();
const { handleImageError, resolveImageUrl } = useImageFallback('/assets/issue-entrance-roof.png');

const { data: response, error } = await useAsyncData(`appeal-detail-${slug}`, () => {
  return api.get<ApiDataEnvelope<AppealDetailDto>>(`/appeals/${slug}`);
});

if (error.value) {
  throw createError({
    statusCode: 404,
    statusMessage: 'Appeal not found',
  });
}

const payload = response.value;

if (!payload) {
  throw createError({
    statusCode: 404,
    statusMessage: 'Appeal not found',
  });
}

const page = payload.data;
const auth = useAuth();

const { data: commentsResponse } = await useAsyncData(`appeal-comments-${slug}`, () => {
  return api.get<ApiDataEnvelope<{ items: AppealCommentDto[] }>>(`/appeals/${slug}/comments`);
});

const comments = ref<AppealCommentDto[]>(commentsResponse.value?.data.items ?? page.commentsPreview);
const commentFilter = ref<'all' | 'official' | 'media'>('all');
const commentText = ref('');
const commentStatus = ref('');
const commentError = ref('');

const statusClasses: Record<AppealStatus, string> = {
  active: 'status-orange',
  checking: 'status-blue',
  draft: 'status-blue',
  resolved: 'status-green',
};

usePageSeo({
  title: page.seo.title,
  description: page.seo.description,
  path: `/appeals/${page.slug}`,
  robots: page.seo.robots,
  ogImageUrl: page.seo.ogImageUrl ?? page.imageUrl,
});

const filteredComments = computed(() => {
  if (commentFilter.value === 'official') {
    return comments.value.filter((comment) => comment.type === 'official');
  }

  if (commentFilter.value === 'media') {
    return comments.value.filter((comment) => comment.hasMedia);
  }

  return comments.value;
});

const submitComment = async (): Promise<void> => {
  commentStatus.value = '';
  commentError.value = '';

  try {
    const response = await api.request<ApiDataEnvelope<AppealCommentDto>>(
      `/appeals/${page.slug}/comments`,
      'POST',
      {
        body: {
          comment: commentText.value,
        },
        headers: auth.authHeaders(),
      },
    );
    comments.value.unshift(response.data);
    commentText.value = '';
    commentStatus.value = 'Комментарий отправлен на проверку.';
  } catch {
    commentError.value = 'Войдите в аккаунт и проверьте текст комментария.';
  }
};
</script>

<template>
  <main class="content-page public-page">
    <div class="container content-page__inner">
      <nav class="content-breadcrumb" aria-label="Навигационная цепочка">
        <NuxtLink to="/">
          Главная
        </NuxtLink>
        <NuxtLink to="/appeals">
          Обращения
        </NuxtLink>
        <span>{{ page.title }}</span>
      </nav>

      <article class="appeal-detail">
        <header class="content-hero public-hero appeal-detail-hero">
          <span class="status" :class="statusClasses[page.status]">{{ page.statusLabel }}</span>
          <p class="content-eyebrow">{{ page.category }}</p>
          <h1>{{ page.title }}</h1>
          <p>{{ page.description }}</p>
          <div class="appeal-detail-meta">
            <span><LayoutAppIcon name="pin" />{{ page.location }}</span>
            <time :datetime="page.publishedAt">{{ formatRuDateTime(page.publishedAt) }}</time>
          </div>
        </header>

        <div class="appeal-detail-grid">
          <div class="appeal-detail-main">
            <img
              class="article-cover"
              :src="resolveImageUrl(page.imageUrl)"
              :alt="page.title"
              width="960"
              height="640"
              @error="handleImageError"
            >

            <section class="content-card article-body" aria-labelledby="appeal-timeline-title">
              <section>
                <h2 id="appeal-timeline-title">Ход обращения</h2>
                <ol class="timeline-list">
                  <li v-for="item in page.timeline" :key="`${item.status}-${item.date}`">
                    <time :datetime="item.date">{{ formatRuDateTime(item.date) }}</time>
                    <strong>{{ item.title }}</strong>
                    <p>{{ item.text }}</p>
                  </li>
                </ol>
              </section>
            </section>

            <section v-if="page.officialResponse" class="content-card article-body" aria-labelledby="official-response-title">
              <section>
                <h2 id="official-response-title">{{ page.officialResponse.title }}</h2>
                <p>{{ page.officialResponse.text }}</p>
                <p v-if="page.officialResponse.receivedAt">
                  Получено: {{ formatRuDateTime(page.officialResponse.receivedAt) }}
                </p>
              </section>
            </section>

            <section class="content-card article-body" aria-labelledby="appeal-comments-title">
              <section>
                <h2 id="appeal-comments-title">Комментарии</h2>
                <div class="comment-filters">
                  <button type="button" :class="{ 'is-active': commentFilter === 'all' }" @click="commentFilter = 'all'">
                    Все
                  </button>
                  <button type="button" :class="{ 'is-active': commentFilter === 'official' }" @click="commentFilter = 'official'">
                    Официальные
                  </button>
                  <button type="button" :class="{ 'is-active': commentFilter === 'media' }" @click="commentFilter = 'media'">
                    С фото
                  </button>
                </div>
                <div v-if="filteredComments.length > 0" class="comments-preview">
                  <article v-for="comment in filteredComments" :key="comment.id">
                    <strong>{{ comment.authorName }}</strong>
                    <time :datetime="comment.createdAt">{{ formatRuDateTime(comment.createdAt) }}</time>
                    <p>{{ comment.comment }}</p>
                    <span v-if="comment.status === 'pending'" class="comment-status">На проверке</span>
                  </article>
                </div>
                <p v-else>Комментариев пока нет.</p>

                <form class="comment-form" @submit.prevent="submitComment">
                  <label>
                    <span>Комментарий</span>
                    <textarea v-model="commentText" rows="4" required minlength="3" maxlength="1000" />
                  </label>
                  <p v-if="commentStatus" class="form-message form-message--success">{{ commentStatus }}</p>
                  <p v-if="commentError" class="form-message form-message--error">{{ commentError }}</p>
                  <button class="btn btn-red" type="submit">
                    Отправить комментарий
                  </button>
                  <NuxtLink v-if="!auth.token.value" to="/login">
                    Войдите, чтобы оставить комментарий
                  </NuxtLink>
                </form>
              </section>
            </section>
          </div>

          <aside class="appeal-detail-aside" aria-label="Сводка обращения">
            <section>
              <h2>Сводка</h2>
              <dl>
                <div>
                  <dt>Город</dt>
                  <dd>{{ page.city }}</dd>
                </div>
                <div>
                  <dt>Район</dt>
                  <dd>{{ page.district }}</dd>
                </div>
                <div>
                  <dt>Поддержали</dt>
                  <dd>{{ formatRuNumber(page.supportCount) }}</dd>
                </div>
                <div>
                  <dt>Просмотры</dt>
                  <dd>{{ formatRuNumber(page.viewsCount) }}</dd>
                </div>
              </dl>
            </section>

            <section v-if="page.attachments.length > 0">
              <h2>Материалы</h2>
              <NuxtLink
                v-for="attachment in page.attachments"
                :key="attachment.url"
                :to="attachment.url"
                external
              >
                <LayoutAppIcon name="file" />
                {{ attachment.title }}
              </NuxtLink>
            </section>

            <section v-if="page.documents.length > 0">
              <h2>Документы</h2>
              <NuxtLink
                v-for="document in page.documents"
                :key="document.title"
                :to="document.url"
                external
              >
                <LayoutAppIcon name="file" />
                {{ document.title }}
              </NuxtLink>
            </section>
          </aside>
        </div>
      </article>
    </div>
  </main>
</template>
