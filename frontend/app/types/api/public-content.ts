export type RobotsDirective = 'index,follow' | 'noindex,follow' | 'noindex,nofollow';

export type SeoDto = {
  title: string;
  description: string;
  canonicalUrl: string;
  robots: RobotsDirective;
  ogImageUrl: string | null;
  lastModifiedAt: string | null;
};

export type PaginationDto = {
  currentPage: number;
  perPage: number;
  total: number;
  lastPage: number;
};

export type CategoryDto = {
  id: string;
  slug: string;
  title: string;
  description: string;
  icon: string;
};

export type CategoryGroupDto = {
  slug: string;
  title: string;
  categories: CategoryDto[];
};

export type CategoriesPageDto = {
  groups: CategoryGroupDto[];
  seo: SeoDto;
};

export type HomeSlideDto = {
  id: string;
  slug: string;
  label: string;
  title: string;
  lead: string;
  note: string | null;
  imageUrl: string;
  primaryCtaLabel: string | null;
  primaryCtaUrl: string | null;
  secondaryCtaLabel: string | null;
  secondaryCtaUrl: string | null;
};

export type HomeAdvertisementDto = {
  id: string;
  slug: string;
  placement: string;
  title: string;
  label: string | null;
  description: string | null;
  imageUrl: string;
  alt: string;
  targetUrl: string;
};

export type HomeContentDto = {
  slides: HomeSlideDto[];
  advertisements: HomeAdvertisementDto[];
  categoryGroups: CategoryGroupDto[];
  seo: SeoDto;
};

export type NewsItemDto = {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  publishedAt: string;
  category: string;
  imageUrl: string;
  seo?: SeoDto;
};

export type NewsIndexDto = {
  items: NewsItemDto[];
  pagination: PaginationDto;
  seo: SeoDto;
};

export type AppealStatus = 'checking' | 'active' | 'resolved' | 'draft';

export type AppealListItemDto = {
  id: string;
  slug: string;
  title: string;
  status: AppealStatus;
  statusLabel: string;
  city: string;
  district: string;
  category: string;
  publishedAt: string;
  supportCount: number;
  viewsCount: number;
  commentsCount: number;
  imageUrl: string;
  excerpt: string;
};

export type AppealTimelineItemDto = {
  status: string;
  title: string;
  date: string;
  text: string;
};

export type AppealAttachmentDto = {
  type: string;
  url: string;
  title: string;
};

export type AppealDocumentDto = {
  title: string;
  url: string;
};

export type AppealOfficialResponseDto = {
  title: string;
  text: string;
  receivedAt: string | null;
};

export type AppealCommentDto = {
  id: string;
  authorName: string;
  status: string;
  type: 'public' | 'official';
  comment: string;
  createdAt: string;
  hasMedia: boolean;
};

export type AppealDetailDto = AppealListItemDto & {
  description: string;
  location: string;
  updatedAt: string;
  attachments: AppealAttachmentDto[];
  timeline: AppealTimelineItemDto[];
  officialResponse: AppealOfficialResponseDto | null;
  documents: AppealDocumentDto[];
  commentsPreview: AppealCommentDto[];
  seo: SeoDto;
};

export type AppealsIndexDto = {
  items: AppealListItemDto[];
  pagination: PaginationDto;
  summary: {
    publishedCount: number;
    resolvedCount: number;
    activeCount: number;
    supportCount: number;
  };
  seo: SeoDto;
};
