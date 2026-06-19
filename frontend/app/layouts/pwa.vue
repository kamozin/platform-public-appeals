<template>
  <div class="pwa-shell">
    <LayoutAppSvgSprite />
    <NuxtLink class="skip-link" to="#main-content">
      Перейти к содержимому
    </NuxtLink>
    <PwaOfflineBanner />
    <PwaAppBar />
    <main id="main-content" class="pwa-main" tabindex="-1">
      <slot />
    </main>
    <PwaInstallPrompt />
    <PwaUpdatePrompt />
    <PwaBottomNavigation />
  </div>
</template>
