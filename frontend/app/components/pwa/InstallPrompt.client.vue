<script setup lang="ts">
const { $pwa } = useNuxtApp();

const canInstall = computed(() => {
  return Boolean($pwa?.showInstallPrompt) && !$pwa?.isPWAInstalled;
});

const install = async (): Promise<void> => {
  await $pwa?.install();
};

const dismiss = (): void => {
  $pwa?.cancelInstall();
};
</script>

<template>
  <section v-if="canInstall" class="pwa-floating-prompt" aria-label="Установка приложения">
    <div>
      <strong>Добавить на экран</strong>
      <span>Откроется отдельным приложением.</span>
    </div>
    <div class="pwa-floating-prompt__actions">
      <button class="pwa-icon-button" type="button" aria-label="Скрыть" @click="dismiss">
        <LayoutAppIcon name="trash" />
      </button>
      <button class="pwa-prompt-button" type="button" @click="install">
        Добавить
      </button>
    </div>
  </section>
</template>
