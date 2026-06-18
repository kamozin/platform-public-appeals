<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    open: boolean;
    itemTitle?: string;
    link?: string;
  }>(),
  {
    itemTitle: 'обращение',
    link: '',
  },
);

const emit = defineEmits<{
  'update:open': [value: boolean];
}>();

const panelRef = ref<HTMLElement | null>(null);
const copyLabel = ref('Скопировать ссылку');

const currentLink = computed<string>(() => {
  const propLink = props.link ?? '';

  if (propLink.length > 0) {
    return propLink;
  }

  if (import.meta.client) {
    return window.location.href.split('#')[0] ?? '';
  }

  return '';
});

const encodedMailBody = computed(() => encodeURIComponent(currentLink.value));

const closeModal = (): void => {
  emit('update:open', false);
};

const copyLink = async (): Promise<void> => {
  if (!currentLink.value || !navigator.clipboard) {
    copyLabel.value = 'Ссылка недоступна';
    return;
  }

  try {
    await navigator.clipboard.writeText(currentLink.value);
    copyLabel.value = 'Ссылка скопирована';
  } catch {
    copyLabel.value = 'Не удалось скопировать';
  }
};

const handleKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape' && props.open) {
    closeModal();
  }
};

watch(
  () => props.open,
  async (isOpen) => {
    if (!import.meta.client) {
      return;
    }

    document.body.classList.toggle('modal-open', isOpen);

    if (!isOpen) {
      return;
    }

    copyLabel.value = 'Скопировать ссылку';
    await nextTick();
    panelRef.value?.focus();
  },
  { immediate: true },
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
  <Teleport to="body">
    <div
      v-if="open"
      class="modal share-modal"
      data-share-modal
    >
      <button
        class="modal-backdrop"
        type="button"
        aria-label="Закрыть окно"
        @click="closeModal"
      />
      <section
        ref="panelRef"
        class="modal-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="share-modal-title"
        aria-describedby="share-modal-text"
        tabindex="-1"
      >
        <button
          class="modal-close"
          type="button"
          aria-label="Закрыть окно"
          @click="closeModal"
        >
          <span aria-hidden="true">×</span>
        </button>

        <h2 id="share-modal-title">Поделиться ссылкой</h2>
        <p id="share-modal-text">
          Ссылка на обращение «{{ itemTitle }}» готова к отправке.
        </p>

        <div class="modal-actions">
          <button class="btn btn-red" type="button" @click="copyLink">
            <LayoutAppIcon name="share" />
            {{ copyLabel }}
          </button>
          <a class="btn btn-outline" :href="`mailto:?body=${encodedMailBody}`">
            Отправить по почте
          </a>
        </div>
      </section>
    </div>
  </Teleport>
</template>
