<script setup lang="ts">
defineProps<{
  screen?: boolean;
}>();
</script>

<template>
  <footer class="auth-footer" :class="{ 'screen-footer': screen }">
    <div class="container auth-footer-bottom">
      <span>© 2026 НКО «Рука добра». Все права защищены.</span>
      <NuxtLink to="/privacy">
        Политика конфиденциальности
      </NuxtLink>
      <NuxtLink to="/agreement">
        Пользовательское соглашение
      </NuxtLink>
    </div>
    <div class="footer-ribbon auth-footer-ribbon" aria-hidden="true" />
  </footer>
</template>
