export type PageSeoInput = {
  title: string;
  description: string;
  path?: string;
  robots?: 'index,follow' | 'noindex,follow' | 'noindex,nofollow';
  ogImageUrl?: string | undefined;
};
