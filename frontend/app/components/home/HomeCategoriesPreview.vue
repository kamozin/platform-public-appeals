<script setup lang="ts">
const categories = [
  { label: 'ЖКХ', icon: 'home', to: '/appeal/new?category=zhkh' },
  { label: 'Дороги', icon: 'trend', to: '/appeal/new?category=roads' },
  { label: 'Здравоохранение', icon: 'shield', to: '/appeal/new?category=healthcare' },
  { label: 'Образование', icon: 'book', to: '/appeal/new?category=education' },
  { label: 'Благоустройство', icon: 'users', to: '/appeal/new?category=improvement' },
  { label: 'Коррупция', icon: 'heart', to: '/appeal/new?category=corruption' },
  { label: 'Права граждан', icon: 'scale', to: '/appeal/new?category=citizen-rights' },
  { label: 'СВО / Ветераны', icon: 'flag', to: '/appeal/new?category=veterans' },
  { label: 'Другое', icon: 'file', to: '/appeal/new?category=other' },
] as const;
</script>

<template>
  <section id="appeals" class="container categories" aria-labelledby="categories-title">
    <div class="section-head inline">
      <h2 id="categories-title">Куда направить обращение или жалобу</h2>
      <NuxtLink to="/categories">
        Все категории
        <LayoutAppIcon name="arrow" />
      </NuxtLink>
    </div>
    <div class="category-grid">
      <NuxtLink
        v-for="category in categories"
        :key="category.label"
        :to="category.to"
      >
        <LayoutAppIcon :name="category.icon" />
        <span>{{ category.label }}</span>
      </NuxtLink>
    </div>
  </section>
</template>
