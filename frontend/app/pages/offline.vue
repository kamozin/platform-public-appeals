<script setup lang="ts">
definePageMeta({
  layout: 'pwa',
});

useNoindexSeo();
useHead({
  title: 'Нет сети | Рука добра',
});
</script>

<template>
  <section class="pwa-screen" aria-labelledby="offline-title">
    <section class="pwa-empty-panel">
      <LayoutAppIcon name="refresh" />
      <h1 id="offline-title">
        Нет соединения
      </h1>
      <p>Откройте сохранённые экраны или повторите загрузку после восстановления сети.</p>
      <NuxtLink class="pwa-wide-action" to="/app">
        <LayoutAppIcon name="home" />
        На главный экран
      </NuxtLink>
    </section>
  </section>
</template>
