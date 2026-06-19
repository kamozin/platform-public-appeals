<script setup lang="ts">
const { $pwa } = useNuxtApp();

const isVisible = computed(() => {
  return Boolean($pwa?.offlineReady || $pwa?.needRefresh);
});
const message = computed(() => {
  if ($pwa?.needRefresh) {
    return 'Доступно обновление.';
  }

  return 'Готово к работе без сети.';
});

const refresh = async (): Promise<void> => {
  await $pwa?.updateServiceWorker(true);
};

const close = async (): Promise<void> => {
  await $pwa?.cancelPrompt();
};
</script>

<template>
  <section v-if="isVisible" class="pwa-floating-prompt" aria-label="Состояние приложения">
    <div>
      <strong>{{ message }}</strong>
      <span v-if="$pwa?.needRefresh">Перезапуск применит новую версию.</span>
    </div>
    <div class="pwa-floating-prompt__actions">
      <button class="pwa-icon-button" type="button" aria-label="Закрыть" @click="close">
        <LayoutAppIcon name="check" />
      </button>
      <button v-if="$pwa?.needRefresh" class="pwa-prompt-button" type="button" @click="refresh">
        Обновить
      </button>
    </div>
  </section>
</template>
