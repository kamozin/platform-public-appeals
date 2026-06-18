<script setup lang="ts">
const route = useRoute();
const headerRef = ref<HTMLElement | null>(null);
const menuOpen = ref(false);

const navLinks = [
  { label: 'О нас', to: '/#about', external: false },
  { label: 'Обращения и жалобы', to: '/appeals', external: false },
  { label: 'Как это работает', to: '/#how', external: false },
  { label: 'Новости', to: '/news', external: false },
  { label: 'Контакты', to: '/#contacts', external: false },
] as const;

let stopBodyClassWatch: (() => void) | null = null;

const closeMenu = (): void => {
  menuOpen.value = false;
};

const toggleMenu = (): void => {
  menuOpen.value = !menuOpen.value;
};

const handleDocumentClick = (event: MouseEvent): void => {
  if (!menuOpen.value) {
    return;
  }

  if (!(event.target instanceof Node)) {
    return;
  }

  if (headerRef.value?.contains(event.target)) {
    return;
  }

  closeMenu();
};

const handleKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape') {
    closeMenu();
  }
};

const handleNavClick = (event: MouseEvent): void => {
  if (!(event.target instanceof Element)) {
    return;
  }

  if (event.target.closest('a')) {
    closeMenu();
  }
};

watch(() => route.fullPath, closeMenu);

onMounted(() => {
  stopBodyClassWatch = watch(
    menuOpen,
    (isOpen) => {
      document.body.classList.toggle('menu-open', isOpen);
    },
    { immediate: true },
  );

  document.addEventListener('click', handleDocumentClick);
  window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  stopBodyClassWatch?.();
  document.body.classList.remove('menu-open');
  document.removeEventListener('click', handleDocumentClick);
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <header ref="headerRef" class="site-header shell-header">
    <div class="container header-inner auth-header-inner">
      <LayoutAppLogo />

      <button
        class="menu-toggle"
        type="button"
        aria-label="Открыть меню"
        :aria-expanded="menuOpen"
        @click="toggleMenu"
      >
        <LayoutAppIcon name="menu" />
      </button>

      <nav
        class="main-nav auth-nav"
        :class="{ 'is-open': menuOpen }"
        aria-label="Основная навигация"
        @click="handleNavClick"
      >
        <NuxtLink
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          :external="link.external"
        >
          {{ link.label }}
        </NuxtLink>
        <div class="mobile-nav-actions">
          <NuxtLink class="header-login auth-account-link auth-account-link-mobile" to="/login">
            <LayoutAppIcon name="user" />
            Войти
          </NuxtLink>
          <NuxtLink class="header-cta auth-submit-link" to="/appeal/new">
            <LayoutAppIcon name="file" />
            Подать обращение
          </NuxtLink>
        </div>
      </nav>

      <div class="header-actions auth-header-actions">
        <NuxtLink class="header-login auth-account-link" to="/login">
          <LayoutAppIcon name="user" />
          Войти
        </NuxtLink>
        <NuxtLink class="header-cta auth-submit-link" to="/appeal/new">
          <LayoutAppIcon name="file" />
          Подать обращение
        </NuxtLink>
      </div>
    </div>
  </header>
</template>
