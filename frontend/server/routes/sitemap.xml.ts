type SitemapUrlDto = {
  loc: string;
  lastmod: string | null;
};

type SitemapResponse = {
  data: SitemapUrlDto[];
};

const escapeXml = (value: string): string => {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&apos;');
};

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig(event);
  const response = await $fetch<SitemapResponse>('/seo/sitemap-urls', {
    baseURL: config.apiInternalBase,
  }).catch((): SitemapResponse => ({ data: [] }));

  setHeader(event, 'content-type', 'application/xml; charset=utf-8');

  const urls = response.data
    .filter((item) => !item.loc.includes('/api') && !item.loc.includes('/dashboard'))
    .map((item) => {
      const lastmod = item.lastmod ? `<lastmod>${escapeXml(item.lastmod)}</lastmod>` : '';

      return `<url><loc>${escapeXml(item.loc)}</loc>${lastmod}</url>`;
    })
    .join('');

  return `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls}</urlset>`;
});
