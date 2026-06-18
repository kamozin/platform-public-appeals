export default defineEventHandler((event) => {
  const config = useRuntimeConfig(event);
  const siteUrl = String(config.public.siteUrl).replace(/\/+$/, '');

  setHeader(event, 'content-type', 'text/plain; charset=utf-8');

  return [
    'User-agent: *',
    'Disallow: /api',
    'Disallow: /dashboard',
    'Disallow: /login',
    'Disallow: /register',
    'Disallow: /password-reset',
    'Disallow: /verification',
    'Disallow: /appeal/new',
    '',
    `Sitemap: ${siteUrl}/sitemap.xml`,
    '',
  ].join('\n');
});
