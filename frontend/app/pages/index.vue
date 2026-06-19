<script setup lang="ts">
import type { HomeContentDto } from '~/types/api/public-content';

const homeApi = useHomeContent();
const { data: homeContent, error: homeError } = await useAsyncData('home-content', () => homeApi.fetchHome());

if (homeError.value || !homeContent.value) {
  throw createError({
    statusCode: 500,
    statusMessage: 'Home content is unavailable',
  });
}

const resolvedHomeContent = computed<HomeContentDto>(() => homeContent.value as HomeContentDto);

usePageSeo({
  title: resolvedHomeContent.value.seo.title,
  description: resolvedHomeContent.value.seo.description,
  path: '/',
  ogImageUrl: resolvedHomeContent.value.seo.ogImageUrl ?? undefined,
  robots: resolvedHomeContent.value.seo.robots,
});
</script>

<template>
  <div class="home-page">
    <HomeHero :slides="resolvedHomeContent.slides" />
    <HomeSupportVideo />
    <HomeTopPanels />
    <HomeAdBanner :advertisements="resolvedHomeContent.advertisements" />
    <HomeStatsStrip />
    <HomeCategoriesPreview :category-groups="resolvedHomeContent.categoryGroups" />
    <HomeHowItWorks />
    <HomeAppealsPreview />
    <HomeLatestNews />
    <HomeTransparencyTrust />
  </div>
</template>
