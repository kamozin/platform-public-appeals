export type AdminCategoryGroupDto = {
  id: string;
  slug: string;
  title: string;
  sortOrder: number;
  isActive: boolean;
  categories: AdminCategoryDto[];
};

export type AdminCategoryDto = {
  id: string;
  slug: string;
  title: string;
  description: string;
  icon: string;
  sortOrder: number;
  isActive: boolean;
  group: {
    id: string;
    slug: string;
    title: string;
  } | null;
};

export type AdminCategoriesDto = {
  groups: AdminCategoryGroupDto[];
};

export type AdminCategoryPayload = {
  group_slug: string;
  group_title?: string;
  slug: string;
  title: string;
  description: string;
  icon?: string;
  sort_order?: number;
  is_active?: boolean;
};

export type AdminNewsStatus = 'draft' | 'published' | 'archived';

export type AdminNewsDto = {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  category: string;
  imageUrl: string | null;
  status: AdminNewsStatus;
  publishedAt: string | null;
  createdAt: string | null;
  updatedAt: string | null;
};

export type AdminNewsListDto = {
  items: AdminNewsDto[];
};

export type AdminNewsPayload = {
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  category: string;
  image_url?: string | null;
  status?: AdminNewsStatus;
  published_at?: string | null;
};

export type AdminHomepageSlideDto = {
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
  sortOrder: number;
  isActive: boolean;
  createdAt: string | null;
  updatedAt: string | null;
};

export type AdminHomepageSlideListDto = {
  items: AdminHomepageSlideDto[];
};

export type AdminHomepageSlidePayload = {
  slug: string;
  label: string;
  title: string;
  lead: string;
  note?: string | null;
  image_url: string;
  primary_cta_label?: string | null;
  primary_cta_url?: string | null;
  secondary_cta_label?: string | null;
  secondary_cta_url?: string | null;
  sort_order?: number;
  is_active?: boolean;
};

export type AdminAdvertisementDto = {
  id: string;
  slug: string;
  placement: string;
  title: string;
  label: string | null;
  description: string | null;
  imageUrl: string;
  alt: string;
  targetUrl: string;
  sortOrder: number;
  startsAt: string | null;
  endsAt: string | null;
  isActive: boolean;
  createdAt: string | null;
  updatedAt: string | null;
};

export type AdminAdvertisementListDto = {
  items: AdminAdvertisementDto[];
};

export type AdminAdvertisementPayload = {
  slug: string;
  placement?: string;
  title: string;
  label?: string | null;
  description?: string | null;
  image_url: string;
  alt: string;
  target_url: string;
  sort_order?: number;
  starts_at?: string | null;
  ends_at?: string | null;
  is_active?: boolean;
};

export type AdminAppealStatus = 'draft' | 'checking' | 'active' | 'resolved';

export type AdminAppealAttachmentDto = {
  type: string;
  url: string;
  title: string;
};

export type AdminAppealTimelineDto = {
  status: string;
  title: string;
  date: string | null;
  text: string;
};

export type AdminAppealTimelinePayload = {
  status: string;
  title: string;
  happened_at: string;
  text: string;
};

export type AdminAppealDocumentDto = {
  title: string;
  url: string;
};

export type AdminAppealOfficialResponseDto = {
  title: string;
  text: string;
  receivedAt: string | null;
};

export type AdminAppealOfficialResponsePayload = {
  title: string;
  text: string;
  received_at?: string | null;
};

export type AdminAppealDto = {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  description: string;
  status: AdminAppealStatus;
  statusLabel: string;
  city: string;
  district: string | null;
  category: string;
  location: string | null;
  publishedAt: string | null;
  supportCount: number;
  viewsCount: number;
  commentsCount: number;
  imageUrl: string | null;
  isPublic: boolean;
  attachments: AdminAppealAttachmentDto[];
  timeline: AdminAppealTimelineDto[];
  documents: AdminAppealDocumentDto[];
  officialResponse: AdminAppealOfficialResponseDto | null;
  createdAt: string | null;
  updatedAt: string | null;
};

export type AdminAppealListDto = {
  items: AdminAppealDto[];
};

export type AdminAppealPayload = {
  slug: string;
  title: string;
  excerpt: string;
  description: string;
  status: AdminAppealStatus;
  status_label: string;
  city: string;
  district?: string | null;
  category: string;
  location?: string | null;
  published_at?: string | null;
  support_count?: number;
  views_count?: number;
  comments_count?: number;
  image_url?: string | null;
  is_public?: boolean;
  attachments?: AdminAppealAttachmentDto[];
  timeline?: AdminAppealTimelinePayload[];
  documents?: AdminAppealDocumentDto[];
  official_response?: AdminAppealOfficialResponsePayload | null;
};
