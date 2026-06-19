<script setup lang="ts">
definePageMeta({
  layout: 'pwa',
});

useNoindexSeo();
useHead({
  title: 'Профиль | Рука добра',
});

const auth = useAuth();
const pending = ref(false);

onMounted(async () => {
  if (!auth.token.value || auth.user.value) {
    return;
  }

  pending.value = true;
  await auth.fetchMe();
  pending.value = false;
});

const user = computed(() => auth.user.value);
const isAuthorized = computed(() => Boolean(auth.token.value && user.value));
</script>

<template>
  <section class="pwa-screen" aria-labelledby="pwa-profile-title">
    <div class="pwa-section-heading pwa-section-heading--top">
      <div>
        <p class="pwa-eyebrow">
          Личный раздел
        </p>
        <h1 id="pwa-profile-title">
          Профиль
        </h1>
      </div>
    </div>

    <section v-if="isAuthorized && user" class="pwa-profile-card">
      <div class="pwa-profile-card__avatar">
        {{ user.name.slice(0, 2).toUpperCase() }}
      </div>
      <div>
        <h2>{{ user.name }}</h2>
        <p>{{ user.email }}</p>
        <span v-if="user.emailTwoFactorEnabled">2FA включена</span>
      </div>
    </section>

    <section v-else class="pwa-empty-panel">
      <LayoutAppIcon name="lock" />
      <h2>{{ pending ? 'Проверяем сессию...' : 'Войдите в аккаунт' }}</h2>
      <p>После входа будут доступны черновики, уведомления и настройки безопасности.</p>
    </section>

    <div class="pwa-action-list">
      <NuxtLink v-if="isAuthorized" class="pwa-action-row" to="/dashboard/profile">
        <span class="pwa-action-row__icon pwa-action-row__icon--blue">
          <LayoutAppIcon name="user" />
        </span>
        <span>
          <strong>Данные профиля</strong>
          <small>Контакты и уведомления</small>
        </span>
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
      <NuxtLink v-if="isAuthorized" class="pwa-action-row" to="/dashboard/security">
        <span class="pwa-action-row__icon pwa-action-row__icon--green">
          <LayoutAppIcon name="shield" />
        </span>
        <span>
          <strong>Безопасность</strong>
          <small>Пароль и двухфакторная защита</small>
        </span>
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
      <NuxtLink v-if="!isAuthorized" class="pwa-wide-action" to="/login">
        <LayoutAppIcon name="lock" />
        Войти
      </NuxtLink>
      <NuxtLink v-if="!isAuthorized" class="pwa-secondary-action" to="/register">
        Создать аккаунт
      </NuxtLink>
    </div>
  </section>
</template>
