<script setup lang="ts">
const config = useRuntimeConfig();
const siteUrl = config.public.siteUrl.replace(/\/+$/, '');

usePageSeo({
  title: 'Рука добра - платформа обращений и жалоб граждан',
  description: 'Общественная платформа для обращений и жалоб граждан. Помощь, поддержка, общественный контроль и прозрачные результаты.',
  path: '/',
  ogImageUrl: `${siteUrl}/assets/hero-civic-flag.png`,
});
</script>

<template>
  <div class="home-page">
    <HomeHero />
    <HomeSupportVideo />
    <HomeTopPanels />
    <HomeAdBanner />
    <HomeStatsStrip />
    <HomeCategoriesPreview />
    <HomeHowItWorks />
    <HomeAppealsPreview />
    <HomeLatestNews />
    <HomeTransparencyTrust />
  </div>
</template>
