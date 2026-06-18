<template>
  <div class="app-shell">
    <LayoutAppSvgSprite />
    <NuxtLink class="skip-link" to="#main-content">
      Перейти к содержимому
    </NuxtLink>
    <LayoutAppHeader />
    <main id="main-content" class="app-main" tabindex="-1">
      <slot />
    </main>
    <LayoutAppFooter />
    <UiScrollTopButton />
  </div>
</template>
