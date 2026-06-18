<script setup lang="ts">
type PaginationEntry = number | 'ellipsis';

const props = withDefaults(
  defineProps<{
    currentPage?: number;
    pageCount?: number;
    totalCount?: number;
    pageSize?: number;
  }>(),
  {
    currentPage: 1,
    pageCount: 1,
    totalCount: 0,
    pageSize: 10,
  },
);

const emit = defineEmits<{
  change: [page: number];
}>();

const safePageCount = computed(() => Math.max(1, props.pageCount));
const currentPage = computed(() => Math.min(Math.max(1, props.currentPage), safePageCount.value));
const isVisible = computed(() => safePageCount.value > 1 && props.totalCount > 0);
const startIndex = computed(() => ((currentPage.value - 1) * props.pageSize) + 1);
const endIndex = computed(() => Math.min(currentPage.value * props.pageSize, props.totalCount));

const pages = computed<PaginationEntry[]>(() => {
  if (safePageCount.value <= 7) {
    return Array.from({ length: safePageCount.value }, (_, index) => index + 1);
  }

  const entries: PaginationEntry[] = [1];
  const start = Math.max(2, currentPage.value - 1);
  const end = Math.min(safePageCount.value - 1, currentPage.value + 1);

  if (start > 2) {
    entries.push('ellipsis');
  }

  for (let page = start; page <= end; page += 1) {
    entries.push(page);
  }

  if (end < safePageCount.value - 1) {
    entries.push('ellipsis');
  }

  entries.push(safePageCount.value);

  return entries;
});

const goToPage = (page: number): void => {
  if (page < 1 || page > safePageCount.value || page === currentPage.value) {
    return;
  }

  emit('change', page);
};
</script>

<template>
  <nav
    v-if="isVisible"
    class="list-pagination"
    aria-label="Пагинация"
  >
    <span class="list-pagination-status" aria-live="polite">
      Показано {{ startIndex }}-{{ endIndex }} из {{ totalCount }}
    </span>

    <div class="list-pagination-controls">
      <button
        class="pagination-step"
        type="button"
        :disabled="currentPage === 1"
        aria-label="Предыдущая страница"
        @click="goToPage(currentPage - 1)"
      >
        Назад
      </button>

      <template
        v-for="(entry, index) in pages"
        :key="`${entry}-${index}`"
      >
        <span
          v-if="entry === 'ellipsis'"
          class="list-pagination-ellipsis"
          aria-hidden="true"
        >
          ...
        </span>
        <button
          v-else
          type="button"
          :class="{ 'is-active': entry === currentPage }"
          :aria-current="entry === currentPage ? 'page' : undefined"
          :aria-label="`Страница ${entry}`"
          @click="goToPage(entry)"
        >
          {{ entry }}
        </button>
      </template>

      <button
        class="pagination-step"
        type="button"
        :disabled="currentPage === safePageCount"
        aria-label="Следующая страница"
        @click="goToPage(currentPage + 1)"
      >
        Вперед
      </button>
    </div>
  </nav>
</template>
