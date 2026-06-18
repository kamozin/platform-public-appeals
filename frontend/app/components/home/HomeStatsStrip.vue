<script setup lang="ts">
const stats = [
  { icon: 'file', color: 'blue', value: '126 540+', label: 'обращений и жалоб граждан подано' },
  { icon: 'check', color: 'green', value: '12 846+', label: 'проблем решено' },
  { icon: 'users', color: 'blue', value: '87 320+', label: 'активных граждан' },
  { icon: 'heart', color: 'red', value: '15 200+', label: 'нас поддерживают' },
] as const;
</script>

<template>
  <section class="container stats-strip" aria-label="Показатели платформы">
    <article v-for="stat in stats" :key="stat.label">
      <span class="stat-icon" :class="stat.color">
        <LayoutAppIcon :name="stat.icon" />
      </span>
      <strong>{{ stat.value }}</strong>
      <p>{{ stat.label }}</p>
    </article>
  </section>
</template>
