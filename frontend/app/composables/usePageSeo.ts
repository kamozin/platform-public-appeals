import type { PageSeoInput } from '~/types/seo';

const trimTrailingSlash = (value: string): string => {
  return value.replace(/\/+$/, '');
};

const normalizePath = (path: string): string => {
  if (path === '') {
    return '/';
  }

  if (path.startsWith('/')) {
    return path;
  }

  return `/${path}`;
};

export const useCanonicalUrl = (path?: string): string => {
  const route = useRoute();
  const config = useRuntimeConfig();
  let currentPath = route.path;

  if (path !== undefined) {
    currentPath = path;
  }

  return `${trimTrailingSlash(config.public.siteUrl)}${normalizePath(currentPath)}`;
};

export const usePageSeo = (input: PageSeoInput): void => {
  const canonical = useCanonicalUrl(input.path);
  let robots = 'index,follow';

  if (input.robots !== undefined) {
    robots = input.robots;
  }

  useSeoMeta({
    title: input.title,
    description: input.description,
    ogTitle: input.title,
    ogDescription: input.description,
    ogUrl: canonical,
    ogImage: input.ogImageUrl,
    robots,
  });

  useHead({
    link: [
      {
        rel: 'canonical',
        href: canonical,
      },
    ],
  });
};

export const useNoindexSeo = (): void => {
  useHead({
    meta: [
      {
        name: 'robots',
        content: 'noindex,nofollow',
      },
    ],
  });
};
