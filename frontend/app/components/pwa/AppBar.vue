<script setup lang="ts">
const route = useRoute();
const auth = useAuth();

const routeTitles: Readonly<Record<string, string>> = {
  '/app': 'Сегодня',
  '/app/feed': 'Лента',
  '/app/new': 'Обращение',
  '/app/profile': 'Профиль',
};

const title = computed(() => routeTitles[route.path] ?? 'Рука добра');
const initials = computed(() => {
  const name = auth.user.value?.name.trim();

  if (!name) {
    return 'РД';
  }

  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');
});

onMounted(() => {
  if (auth.token.value && !auth.user.value) {
    void auth.fetchMe();
  }
});
</script>

<template>
  <header class="pwa-app-bar">
    <NuxtLink class="pwa-brand" to="/app" aria-label="Рука добра">
      <img class="pwa-brand__icon" src="/assets/favicon.svg" alt="" width="36" height="36">
      <span class="pwa-brand__text">Рука добра</span>
    </NuxtLink>

    <div class="pwa-app-bar__title" aria-live="polite">
      {{ title }}
    </div>

    <NuxtLink class="pwa-avatar" to="/app/profile" aria-label="Профиль">
      {{ initials }}
    </NuxtLink>
  </header>
</template>
