<script setup lang="ts">
const stats = [
  {
    icon: 'file',
    color: 'blue',
    value: '126 540+',
    label: 'обращений подано',
    description: 'по ЖКХ, дорогам, медицине и правам граждан',
  },
  {
    icon: 'check',
    color: 'green',
    value: '12 846+',
    label: 'проблем решено',
    description: 'с понятным статусом и подтвержденным результатом',
  },
  {
    icon: 'users',
    color: 'blue',
    value: '87 320+',
    label: 'активных граждан',
    description: 'следят за обращениями и поддерживают соседей',
  },
  {
    icon: 'heart',
    color: 'red',
    value: '15 200+',
    label: 'поддержали нас',
    description: 'помогают обращениям получать общественный вес',
  },
] as const;
</script>

<template>
  <section class="container stats-strip" aria-labelledby="stats-title">
    <div class="stats-intro">
      <span class="section-eyebrow">
        <LayoutAppIcon name="shield" />
        Обращения получают ход
      </span>
      <h2 id="stats-title">
        Граждане уже используют платформу, чтобы добиться ответа
      </h2>
      <p>Собираем факты, помогаем правильно оформить жалобу и показываем движение по статусу.</p>
      <NuxtLink class="stats-cta" to="/appeal/new">
        Подать обращение
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
    </div>

    <div class="stats-grid">
      <article v-for="stat in stats" :key="stat.label">
        <span class="stat-icon" :class="stat.color">
          <LayoutAppIcon :name="stat.icon" />
        </span>
        <div>
          <strong>{{ stat.value }}</strong>
          <span>{{ stat.label }}</span>
          <p>{{ stat.description }}</p>
        </div>
      </article>
    </div>
  </section>
</template>
