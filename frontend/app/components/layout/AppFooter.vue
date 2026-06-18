<script setup lang="ts">
const footerNavLinks = [
  { label: 'О нас', to: '/#about', external: false },
  { label: 'Обращения и жалобы', to: '/appeals', external: false },
  { label: 'Как это работает', to: '/#how', external: false },
  { label: 'Новости', to: '/news', external: false },
  { label: 'Контакты', to: '/#contacts', external: false },
] as const;

const categoryLinks = [
  { label: 'ЖКХ', to: '/appeal/new?category=zhkh', external: false },
  { label: 'Дороги', to: '/appeal/new?category=roads', external: false },
  { label: 'Здравоохранение', to: '/appeal/new?category=healthcare', external: false },
  { label: 'Образование', to: '/appeal/new?category=education', external: false },
  { label: 'Все категории', to: '/categories', external: false },
] as const;

const socialButtons = [
  { label: 'ВКонтакте', icon: 'social-vk' },
  { label: 'Одноклассники', icon: 'social-ok' },
  { label: 'MAX', icon: 'social-max' },
] as const;

const api = useApi();
const subscribeEmail = ref('');
const subscribeStatus = ref('');
const subscribeError = ref('');

const submitSubscription = async (): Promise<void> => {
  subscribeStatus.value = '';
  subscribeError.value = '';

  try {
    await api.request('/subscriptions/news', 'POST', {
      body: {
        email: subscribeEmail.value,
      },
    });
    subscribeEmail.value = '';
    subscribeStatus.value = 'Подписка оформлена.';
  } catch {
    subscribeError.value = 'Проверьте e-mail и попробуйте ещё раз.';
  }
};
</script>

<template>
  <footer id="contacts" class="site-footer shell-footer">
    <div class="container footer-grid">
      <div class="footer-about">
        <LayoutAppLogo variant="footer" />
        <p>Общественная помощь гражданам с обращениями и жалобами. Работаем ради справедливости и реальных изменений в России.</p>
        <div class="socials" aria-label="Социальные сети">
          <button
            v-for="social in socialButtons"
            :key="social.label"
            :class="social.icon"
            type="button"
            :aria-label="social.label"
          >
            <LayoutAppIcon :name="social.icon" />
          </button>
        </div>
      </div>

      <nav class="footer-col" aria-label="Навигация">
        <h3>Навигация</h3>
        <NuxtLink
          v-for="link in footerNavLinks"
          :key="link.to"
          :to="link.to"
          :external="link.external"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>

      <nav class="footer-col" aria-label="Категории">
        <h3>Категории</h3>
        <NuxtLink
          v-for="link in categoryLinks"
          :key="link.to"
          :to="link.to"
          :external="link.external"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>

      <address class="footer-col footer-contacts">
        <h3>Контакты</h3>
        <a href="tel:+79102357746">
          <LayoutAppIcon name="phone" />
          8 910 235-77-46
        </a>
        <a href="mailto:info@rukadobra.ru">
          <LayoutAppIcon name="mail" />
          info@rukadobra.ru
        </a>
        <span>
          <LayoutAppIcon name="pin" />
          г. Москва
        </span>
        <span>
          <LayoutAppIcon name="check" />
          Ежедневно с 9:00 до 20:00
        </span>
      </address>

      <form class="subscribe" action="#" method="post" @submit.prevent="submitSubscription">
        <h3>Подпишитесь на новости</h3>
        <p>Будьте в курсе важных новостей и результатов нашей работы.</p>
        <label>
          <span class="sr-only">Ваш e-mail</span>
          <input v-model="subscribeEmail" type="email" name="email" placeholder="Ваш e-mail" autocomplete="email" required>
        </label>
        <button type="submit">Подписаться</button>
        <p v-if="subscribeStatus" class="form-message form-message--success">{{ subscribeStatus }}</p>
        <p v-if="subscribeError" class="form-message form-message--error">{{ subscribeError }}</p>
      </form>
    </div>

    <div class="container footer-bottom">
      <span>© 2026 НКО «Рука добра». Все права защищены.</span>
      <NuxtLink to="/privacy">
        Политика конфиденциальности
      </NuxtLink>
      <NuxtLink to="/agreement">
        Пользовательское соглашение
      </NuxtLink>
    </div>
    <div class="footer-ribbon" aria-hidden="true" />
  </footer>
</template>
