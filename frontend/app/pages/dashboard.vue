<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealCommentDto, AppealDetailDto } from '~/types/api/public-content';
import type { AppealDraftDto, AuthUserDto, DashboardListDto } from '~/types/api/private';

const api = useApi();
const auth = useAuth();

const activeTab = ref('profile');
const pending = ref(true);
const statusMessage = ref('');
const profile = ref<AuthUserDto | null>(null);
const drafts = ref<AppealDraftDto[]>([]);
const appeals = ref<AppealDraftDto[]>([]);
const saved = ref<AppealDetailDto[]>([]);
const comments = ref<AppealCommentDto[]>([]);
const notifications = ref<Array<{ id: string; title: string; text: string; read: boolean }>>([]);
const sessions = ref<Array<{ id: string; name: string; createdAt: string | null; lastUsedAt: string | null }>>([]);
const achievements = ref<Array<{ id: string; title: string; description: string; earned: boolean }>>([]);

const profileForm = reactive({
  name: '',
  phone: '',
  notifications: true,
});

const tabs = [
  { id: 'profile', label: 'Профиль' },
  { id: 'drafts', label: 'Черновики' },
  { id: 'appeals', label: 'Обращения' },
  { id: 'saved', label: 'Сохранённые' },
  { id: 'comments', label: 'Комментарии' },
  { id: 'notifications', label: 'Уведомления' },
  { id: 'security', label: 'Безопасность' },
  { id: 'achievements', label: 'Достижения' },
] as const;

useNoindexSeo();

const loadDashboard = async (): Promise<void> => {
  const headers = auth.authHeaders();
  const [
    profileResponse,
    draftsResponse,
    appealsResponse,
    savedResponse,
    commentsResponse,
    notificationsResponse,
    sessionsResponse,
    achievementsResponse,
  ] = await Promise.all([
    api.get<ApiDataEnvelope<AuthUserDto>>('/profile', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealDraftDto>>>('/dashboard/drafts', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealDraftDto>>>('/dashboard/appeals', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealDetailDto>>>('/dashboard/saved-appeals', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealCommentDto>>>('/dashboard/comments', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<{ id: string; title: string; text: string; read: boolean }>>>('/dashboard/notifications', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<{ id: string; name: string; createdAt: string | null; lastUsedAt: string | null }>>>('/dashboard/security/sessions', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<{ id: string; title: string; description: string; earned: boolean }>>>('/dashboard/achievements', { headers }),
  ]);

  profile.value = profileResponse.data;
  profileForm.name = profile.value.name;
  profileForm.phone = profile.value.phone ?? '';
  profileForm.notifications = profile.value.notificationsEnabled;
  drafts.value = draftsResponse.data.items;
  appeals.value = appealsResponse.data.items;
  saved.value = savedResponse.data.items;
  comments.value = commentsResponse.data.items;
  notifications.value = notificationsResponse.data.items;
  sessions.value = sessionsResponse.data.items;
  achievements.value = achievementsResponse.data.items;
};

onMounted(async () => {
  const user = await auth.fetchMe();

  if (!user) {
    await navigateTo('/login');

    return;
  }

  try {
    await loadDashboard();
  } finally {
    pending.value = false;
  }
});

const updateProfile = async (): Promise<void> => {
  const response = await api.request<ApiDataEnvelope<AuthUserDto>>('/profile', 'PATCH', {
    body: profileForm,
    headers: auth.authHeaders(),
  });
  profile.value = response.data;
  auth.user.value = response.data;
  statusMessage.value = 'Профиль обновлён.';
};

const markNotificationsRead = async (): Promise<void> => {
  await api.request('/dashboard/notifications/mark-all-read', 'POST', {
    headers: auth.authHeaders(),
  });
  notifications.value = notifications.value.map((item) => ({ ...item, read: true }));
};

const terminateSession = async (id: string): Promise<void> => {
  await api.request(`/dashboard/security/sessions/${id}`, 'DELETE', {
    headers: auth.authHeaders(),
  });
  sessions.value = sessions.value.filter((session) => session.id !== id);
};
</script>

<template>
  <main class="content-page private-page">
    <section class="dashboard-shell">
      <aside class="dashboard-sidebar">
        <div>
          <p class="content-eyebrow">Кабинет</p>
          <h1>{{ profile?.name || 'Личный кабинет' }}</h1>
        </div>
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          :class="{ 'is-active': activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
        <button type="button" @click="auth.logout">
          Выйти
        </button>
      </aside>

      <section class="dashboard-content">
        <div v-if="pending" class="content-notice">
          <strong>Загрузка</strong>
          <p>Получаем данные личного кабинета.</p>
        </div>

        <template v-else>
          <form v-if="activeTab === 'profile'" class="private-form private-form--two dashboard-panel" @submit.prevent="updateProfile">
            <h2 class="private-form-full">Профиль</h2>
            <label>
              <span>Имя</span>
              <input v-model="profileForm.name" type="text">
            </label>
            <label>
              <span>Телефон</span>
              <input v-model="profileForm.phone" type="tel">
            </label>
            <label class="private-check private-form-full">
              <input v-model="profileForm.notifications" type="checkbox">
              <span>Получать уведомления</span>
            </label>
            <p v-if="statusMessage" class="form-message form-message--success private-form-full">{{ statusMessage }}</p>
            <button class="btn btn-red private-form-full" type="submit">
              Сохранить профиль
            </button>
          </form>

          <section v-if="activeTab === 'drafts'" class="dashboard-panel">
            <h2>Черновики</h2>
            <ul class="private-list">
              <li v-for="draft in drafts" :key="draft.id">{{ draft.title || 'Без названия' }} · {{ draft.category || 'без категории' }}</li>
            </ul>
          </section>

          <section v-if="activeTab === 'appeals'" class="dashboard-panel">
            <h2>Мои обращения</h2>
            <ul class="private-list">
              <li v-for="appeal in appeals" :key="appeal.id">{{ appeal.title || 'Обращение' }} · {{ appeal.status }}</li>
            </ul>
          </section>

          <section v-if="activeTab === 'saved'" class="dashboard-panel">
            <h2>Сохранённые обращения</h2>
            <ul class="private-list">
              <li v-for="appeal in saved" :key="appeal.id">
                <NuxtLink :to="`/appeals/${appeal.slug}`">{{ appeal.title }}</NuxtLink>
              </li>
            </ul>
          </section>

          <section v-if="activeTab === 'comments'" class="dashboard-panel">
            <h2>Мои комментарии</h2>
            <ul class="private-list">
              <li v-for="comment in comments" :key="comment.id">{{ comment.comment }} · {{ comment.status }}</li>
            </ul>
          </section>

          <section v-if="activeTab === 'notifications'" class="dashboard-panel">
            <div class="dashboard-panel-head">
              <h2>Уведомления</h2>
              <button class="btn btn-outline" type="button" @click="markNotificationsRead">Прочитано</button>
            </div>
            <ul class="private-list">
              <li v-for="item in notifications" :key="item.id">{{ item.title }} · {{ item.read ? 'прочитано' : 'новое' }}</li>
            </ul>
          </section>

          <section v-if="activeTab === 'security'" class="dashboard-panel">
            <h2>Сессии</h2>
            <ul class="private-list">
              <li v-for="session in sessions" :key="session.id">
                {{ session.name }} · {{ session.createdAt || 'активна' }}
                <button class="btn btn-outline" type="button" @click="terminateSession(session.id)">Завершить</button>
              </li>
            </ul>
          </section>

          <section v-if="activeTab === 'achievements'" class="dashboard-panel">
            <h2>Достижения</h2>
            <ul class="private-list">
              <li v-for="item in achievements" :key="item.id">{{ item.title }} · {{ item.description }}</li>
            </ul>
          </section>
        </template>
      </section>
    </section>
  </main>
</template>
