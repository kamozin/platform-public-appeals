<script setup lang="ts">
const isOnline = ref(true);

const updateStatus = (): void => {
  isOnline.value = navigator.onLine;
};

onMounted(() => {
  updateStatus();
  window.addEventListener('online', updateStatus);
  window.addEventListener('offline', updateStatus);
});

onBeforeUnmount(() => {
  window.removeEventListener('online', updateStatus);
  window.removeEventListener('offline', updateStatus);
});
</script>

<template>
  <div v-if="!isOnline" class="pwa-offline-banner" role="status">
    Нет сети. Доступны сохранённые экраны.
  </div>
</template>
