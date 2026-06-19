<script setup lang="ts">
import type { AppIconName } from '~/components/layout/AppIcon.vue';
import type {
  AdminAppealAttachmentDto,
  AdminAppealDocumentDto,
  AdminAppealDto,
  AdminAppealOfficialResponsePayload,
  AdminAppealPayload,
  AdminAppealStatus,
  AdminAppealTimelinePayload,
  AdminAdvertisementDto,
  AdminAdvertisementPayload,
  AdminCategoryDto,
  AdminCategoryGroupDto,
  AdminCategoryPayload,
  AdminHomepageSlideDto,
  AdminHomepageSlidePayload,
  AdminNewsDto,
  AdminNewsPayload,
  AdminNewsStatus,
} from '~/types/api/admin-content';
import { formatRuDateTime, formatRuNumber } from '~/utils/formatters';

type AdminTabId = 'categories' | 'slides' | 'advertisements' | 'news' | 'appeals';

type AdminTab = {
  id: AdminTabId;
  label: string;
  icon: AppIconName;
};

type CategoryForm = {
  id: string | null;
  groupSlug: string;
  groupTitle: string;
  slug: string;
  title: string;
  description: string;
  icon: string;
  sortOrder: number;
  isActive: boolean;
};

type NewsForm = {
  id: string | null;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  category: string;
  imageUrl: string;
  status: AdminNewsStatus;
  publishedAt: string;
};

type HomepageSlideForm = {
  id: string | null;
  slug: string;
  label: string;
  title: string;
  lead: string;
  note: string;
  imageUrl: string;
  primaryCtaLabel: string;
  primaryCtaUrl: string;
  secondaryCtaLabel: string;
  secondaryCtaUrl: string;
  sortOrder: number;
  isActive: boolean;
};

type AdvertisementForm = {
  id: string | null;
  slug: string;
  placement: string;
  title: string;
  label: string;
  description: string;
  imageUrl: string;
  alt: string;
  targetUrl: string;
  sortOrder: number;
  startsAt: string;
  endsAt: string;
  isActive: boolean;
};

type AppealTimelineForm = {
  status: string;
  title: string;
  happenedAt: string;
  text: string;
};

type AppealOfficialResponseForm = {
  enabled: boolean;
  title: string;
  text: string;
  receivedAt: string;
};

type AppealForm = {
  id: string | null;
  slug: string;
  title: string;
  excerpt: string;
  description: string;
  status: AdminAppealStatus;
  statusLabel: string;
  city: string;
  district: string;
  category: string;
  location: string;
  publishedAt: string;
  supportCount: number;
  viewsCount: number;
  commentsCount: number;
  imageUrl: string;
  isPublic: boolean;
  attachments: AdminAppealAttachmentDto[];
  timeline: AppealTimelineForm[];
  documents: AdminAppealDocumentDto[];
  officialResponse: AppealOfficialResponseForm;
};

type ApiErrorLike = {
  data?: {
    error?: {
      code?: string;
      message?: string;
    };
  };
  statusCode?: number;
};

const tabs: readonly AdminTab[] = [
  { id: 'categories', label: 'Категории', icon: 'grid' },
  { id: 'slides', label: 'Слайдер', icon: 'play' },
  { id: 'advertisements', label: 'Реклама', icon: 'camera' },
  { id: 'news', label: 'Новости', icon: 'book' },
  { id: 'appeals', label: 'Обращения', icon: 'file' },
];

const newsStatusLabels: Record<AdminNewsStatus, string> = {
  archived: 'Архив',
  draft: 'Черновик',
  published: 'Опубликовано',
};

const appealStatusLabels: Record<AdminAppealStatus, string> = {
  active: 'В работе',
  checking: 'На проверке',
  draft: 'Черновик',
  resolved: 'Решено',
};

const admin = useAdminContent();
const auth = useAuth();
const route = useRoute();

const adminTabPaths: Record<AdminTabId, string> = {
  categories: '/admin/categories',
  slides: '/admin/slides',
  advertisements: '/admin/advertisements',
  news: '/admin/news',
  appeals: '/admin/appeals',
};
const adminTabIds = Object.keys(adminTabPaths) as AdminTabId[];
const routeSection = computed(() => {
  const section = route.params.section;

  if (Array.isArray(section)) {
    return section[0] ?? null;
  }

  if (typeof section === 'string') {
    return section;
  }

  return null;
});
const isAdminTabId = (value: string | null): value is AdminTabId => {
  return value !== null && (adminTabIds as readonly string[]).includes(value);
};
const activeTab = computed<AdminTabId>(() => {
  if (isAdminTabId(routeSection.value)) {
    return routeSection.value;
  }

  return 'categories';
});
const shouldRedirectToCategories = computed(() => {
  const normalizedPath = route.path.replace(/\/$/, '');

  return normalizedPath === '/admin' || !isAdminTabId(routeSection.value);
});
const pending = ref(true);
const saving = ref(false);
const accessDenied = ref(false);
const statusMessage = ref('');
const errorMessage = ref('');
const categoryGroups = ref<AdminCategoryGroupDto[]>([]);
const homepageSlides = ref<AdminHomepageSlideDto[]>([]);
const advertisements = ref<AdminAdvertisementDto[]>([]);
const newsItems = ref<AdminNewsDto[]>([]);
const appeals = ref<AdminAppealDto[]>([]);

const emptyCategoryForm = (): CategoryForm => ({
  id: null,
  groupSlug: 'city',
  groupTitle: 'Дом и город',
  slug: '',
  title: '',
  description: '',
  icon: 'file',
  sortOrder: 0,
  isActive: true,
});

const emptyNewsForm = (): NewsForm => ({
  id: null,
  slug: '',
  title: '',
  excerpt: '',
  content: '',
  category: 'Новости проекта',
  imageUrl: '',
  status: 'draft',
  publishedAt: '',
});

const emptyHomepageSlideForm = (): HomepageSlideForm => ({
  id: null,
  slug: '',
  label: '',
  title: '',
  lead: '',
  note: '',
  imageUrl: '',
  primaryCtaLabel: 'Подать обращение / жалобу',
  primaryCtaUrl: '/appeal/new',
  secondaryCtaLabel: 'Смотреть обращения',
  secondaryCtaUrl: '/appeals',
  sortOrder: 0,
  isActive: true,
});

const emptyAdvertisementForm = (): AdvertisementForm => ({
  id: null,
  slug: '',
  placement: 'home_promo',
  title: '',
  label: 'Партнерский блок',
  description: '',
  imageUrl: '',
  alt: '',
  targetUrl: '',
  sortOrder: 0,
  startsAt: '',
  endsAt: '',
  isActive: true,
});

const emptyAppealForm = (): AppealForm => ({
  id: null,
  slug: '',
  title: '',
  excerpt: '',
  description: '',
  status: 'checking',
  statusLabel: appealStatusLabels.checking,
  city: '',
  district: '',
  category: '',
  location: '',
  publishedAt: '',
  supportCount: 0,
  viewsCount: 0,
  commentsCount: 0,
  imageUrl: '',
  isPublic: true,
  attachments: [],
  timeline: [],
  documents: [],
  officialResponse: {
    enabled: false,
    title: '',
    text: '',
    receivedAt: '',
  },
});

const categoryForm = reactive<CategoryForm>(emptyCategoryForm());
const homepageSlideForm = reactive<HomepageSlideForm>(emptyHomepageSlideForm());
const advertisementForm = reactive<AdvertisementForm>(emptyAdvertisementForm());
const newsForm = reactive<NewsForm>(emptyNewsForm());
const appealForm = reactive<AppealForm>(emptyAppealForm());

const categoryRows = computed(() => {
  return categoryGroups.value.flatMap((group) => {
    return group.categories.map((category) => ({
      category,
      group,
    }));
  });
});

const publishedNewsCount = computed(() => newsItems.value.filter((item) => item.status === 'published').length);
const publicAppealsCount = computed(() => appeals.value.filter((item) => item.isPublic).length);
const activeSlidesCount = computed(() => homepageSlides.value.filter((item) => item.isActive).length);
const activeAdvertisementsCount = computed(() => advertisements.value.filter((item) => item.isActive).length);
const currentUserName = computed(() => auth.user.value?.name ?? 'Администратор');
const currentUserEmail = computed(() => auth.user.value?.email ?? '');

const tabCount = (id: AdminTabId): number => {
  if (id === 'categories') {
    return categoryRows.value.length;
  }

  if (id === 'news') {
    return newsItems.value.length;
  }

  if (id === 'slides') {
    return homepageSlides.value.length;
  }

  if (id === 'advertisements') {
    return advertisements.value.length;
  }

  return appeals.value.length;
};

const padDatePart = (value: number): string => String(value).padStart(2, '0');

const toLocalDateTimeInput = (value: string | null): string => {
  if (!value) {
    return '';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return '';
  }

  return [
    date.getFullYear(),
    '-',
    padDatePart(date.getMonth() + 1),
    '-',
    padDatePart(date.getDate()),
    'T',
    padDatePart(date.getHours()),
    ':',
    padDatePart(date.getMinutes()),
  ].join('');
};

const nowDateTimeInput = (): string => toLocalDateTimeInput(new Date().toISOString());

const stringOrNull = (value: string): string | null => {
  const normalized = value.trim();

  if (normalized === '') {
    return null;
  }

  return normalized;
};

const optionalString = (value: string): string | undefined => {
  const normalized = value.trim();

  if (normalized === '') {
    return undefined;
  }

  return normalized;
};

const positiveInteger = (value: number): number => {
  const normalized = Number(value);

  if (!Number.isFinite(normalized) || normalized < 0) {
    return 0;
  }

  return Math.trunc(normalized);
};

const formatOptionalDateTime = (value: string | null): string => {
  if (!value) {
    return 'не задано';
  }

  return formatRuDateTime(value);
};

const resolveApiError = (error: unknown): string => {
  const candidate = error as ApiErrorLike;
  const code = candidate.data?.error?.code;
  const message = candidate.data?.error?.message;

  if (code && message) {
    return `${code}: ${message}`;
  }

  if (candidate.statusCode === 403) {
    return 'FORBIDDEN: доступ разрешён только администратору.';
  }

  if (candidate.statusCode === 401) {
    return 'UNAUTHORIZED: требуется вход в систему.';
  }

  return 'Не удалось выполнить действие.';
};

const clearMessages = (): void => {
  statusMessage.value = '';
  errorMessage.value = '';
};

const adminTabPath = (id: AdminTabId): string => adminTabPaths[id];

watch(activeTab, () => {
  clearMessages();
});

const loadAdminContent = async (): Promise<void> => {
  const [categoriesResponse, slidesResponse, advertisementsResponse, newsResponse, appealsResponse] = await Promise.all([
    admin.listCategories(),
    admin.listHomepageSlides(),
    admin.listAdvertisements(),
    admin.listNews(),
    admin.listAppeals(),
  ]);

  categoryGroups.value = categoriesResponse.groups;
  homepageSlides.value = slidesResponse.items;
  advertisements.value = advertisementsResponse.items;
  newsItems.value = newsResponse.items;
  appeals.value = appealsResponse.items;
};

const runAdminAction = async (successMessage: string, action: () => Promise<void>): Promise<void> => {
  saving.value = true;
  clearMessages();

  try {
    await action();
    await loadAdminContent();
    statusMessage.value = successMessage;
  } catch (error: unknown) {
    errorMessage.value = resolveApiError(error);
  } finally {
    saving.value = false;
  }
};

const resetCategoryForm = (): void => {
  Object.assign(categoryForm, emptyCategoryForm());
};

const resetNewsForm = (): void => {
  Object.assign(newsForm, emptyNewsForm());
};

const resetHomepageSlideForm = (): void => {
  Object.assign(homepageSlideForm, emptyHomepageSlideForm());
};

const resetAdvertisementForm = (): void => {
  Object.assign(advertisementForm, emptyAdvertisementForm());
};

const resetAppealForm = (): void => {
  Object.assign(appealForm, emptyAppealForm());
};

const categoryPayload = (): AdminCategoryPayload => {
  const payload: AdminCategoryPayload = {
    group_slug: categoryForm.groupSlug.trim(),
    slug: categoryForm.slug.trim(),
    title: categoryForm.title.trim(),
    description: categoryForm.description.trim(),
    sort_order: positiveInteger(categoryForm.sortOrder),
    is_active: categoryForm.isActive,
  };
  const groupTitle = optionalString(categoryForm.groupTitle);
  const icon = optionalString(categoryForm.icon);

  if (groupTitle) {
    payload.group_title = groupTitle;
  }

  if (icon) {
    payload.icon = icon;
  }

  return payload;
};

const newsPayload = (): AdminNewsPayload => ({
  slug: newsForm.slug.trim(),
  title: newsForm.title.trim(),
  excerpt: newsForm.excerpt.trim(),
  content: newsForm.content.trim(),
  category: newsForm.category.trim(),
  image_url: stringOrNull(newsForm.imageUrl),
  status: newsForm.status,
  published_at: stringOrNull(newsForm.publishedAt),
});

const homepageSlidePayload = (): AdminHomepageSlidePayload => ({
  slug: homepageSlideForm.slug.trim(),
  label: homepageSlideForm.label.trim(),
  title: homepageSlideForm.title.trim(),
  lead: homepageSlideForm.lead.trim(),
  note: stringOrNull(homepageSlideForm.note),
  image_url: homepageSlideForm.imageUrl.trim(),
  primary_cta_label: stringOrNull(homepageSlideForm.primaryCtaLabel),
  primary_cta_url: stringOrNull(homepageSlideForm.primaryCtaUrl),
  secondary_cta_label: stringOrNull(homepageSlideForm.secondaryCtaLabel),
  secondary_cta_url: stringOrNull(homepageSlideForm.secondaryCtaUrl),
  sort_order: positiveInteger(homepageSlideForm.sortOrder),
  is_active: homepageSlideForm.isActive,
});

const advertisementPayload = (): AdminAdvertisementPayload => ({
  slug: advertisementForm.slug.trim(),
  placement: advertisementForm.placement.trim(),
  title: advertisementForm.title.trim(),
  label: stringOrNull(advertisementForm.label),
  description: stringOrNull(advertisementForm.description),
  image_url: advertisementForm.imageUrl.trim(),
  alt: advertisementForm.alt.trim(),
  target_url: advertisementForm.targetUrl.trim(),
  sort_order: positiveInteger(advertisementForm.sortOrder),
  starts_at: stringOrNull(advertisementForm.startsAt),
  ends_at: stringOrNull(advertisementForm.endsAt),
  is_active: advertisementForm.isActive,
});

const appealPayload = (): AdminAppealPayload => {
  const officialResponse: AdminAppealOfficialResponsePayload | null = appealForm.officialResponse.enabled
    ? {
        title: appealForm.officialResponse.title.trim(),
        text: appealForm.officialResponse.text.trim(),
        received_at: stringOrNull(appealForm.officialResponse.receivedAt),
      }
    : null;

  const timeline: AdminAppealTimelinePayload[] = appealForm.timeline.map((item) => ({
    status: item.status.trim(),
    title: item.title.trim(),
    happened_at: item.happenedAt,
    text: item.text.trim(),
  }));

  return {
    slug: appealForm.slug.trim(),
    title: appealForm.title.trim(),
    excerpt: appealForm.excerpt.trim(),
    description: appealForm.description.trim(),
    status: appealForm.status,
    status_label: appealForm.statusLabel.trim(),
    city: appealForm.city.trim(),
    district: stringOrNull(appealForm.district),
    category: appealForm.category.trim(),
    location: stringOrNull(appealForm.location),
    published_at: stringOrNull(appealForm.publishedAt),
    support_count: positiveInteger(appealForm.supportCount),
    views_count: positiveInteger(appealForm.viewsCount),
    comments_count: positiveInteger(appealForm.commentsCount),
    image_url: stringOrNull(appealForm.imageUrl),
    is_public: appealForm.isPublic,
    attachments: appealForm.attachments.map((item) => ({
      type: item.type.trim(),
      url: item.url.trim(),
      title: item.title.trim(),
    })),
    timeline,
    documents: appealForm.documents.map((item) => ({
      title: item.title.trim(),
      url: item.url.trim(),
    })),
    official_response: officialResponse,
  };
};

const editCategory = (category: AdminCategoryDto): void => {
  Object.assign(categoryForm, {
    id: category.id,
    groupSlug: category.group?.slug ?? 'content',
    groupTitle: category.group?.title ?? '',
    slug: category.slug,
    title: category.title,
    description: category.description,
    icon: category.icon,
    sortOrder: category.sortOrder,
    isActive: category.isActive,
  });
  void navigateTo(adminTabPath('categories'));
  clearMessages();
};

const editNews = (item: AdminNewsDto): void => {
  Object.assign(newsForm, {
    id: item.id,
    slug: item.slug,
    title: item.title,
    excerpt: item.excerpt,
    content: item.content,
    category: item.category,
    imageUrl: item.imageUrl ?? '',
    status: item.status,
    publishedAt: toLocalDateTimeInput(item.publishedAt),
  });
  void navigateTo(adminTabPath('news'));
  clearMessages();
};

const editHomepageSlide = (item: AdminHomepageSlideDto): void => {
  Object.assign(homepageSlideForm, {
    id: item.id,
    slug: item.slug,
    label: item.label,
    title: item.title,
    lead: item.lead,
    note: item.note ?? '',
    imageUrl: item.imageUrl,
    primaryCtaLabel: item.primaryCtaLabel ?? '',
    primaryCtaUrl: item.primaryCtaUrl ?? '',
    secondaryCtaLabel: item.secondaryCtaLabel ?? '',
    secondaryCtaUrl: item.secondaryCtaUrl ?? '',
    sortOrder: item.sortOrder,
    isActive: item.isActive,
  });
  void navigateTo(adminTabPath('slides'));
  clearMessages();
};

const editAdvertisement = (item: AdminAdvertisementDto): void => {
  Object.assign(advertisementForm, {
    id: item.id,
    slug: item.slug,
    placement: item.placement,
    title: item.title,
    label: item.label ?? '',
    description: item.description ?? '',
    imageUrl: item.imageUrl,
    alt: item.alt,
    targetUrl: item.targetUrl,
    sortOrder: item.sortOrder,
    startsAt: toLocalDateTimeInput(item.startsAt),
    endsAt: toLocalDateTimeInput(item.endsAt),
    isActive: item.isActive,
  });
  void navigateTo(adminTabPath('advertisements'));
  clearMessages();
};

const editAppeal = (item: AdminAppealDto): void => {
  Object.assign(appealForm, {
    id: item.id,
    slug: item.slug,
    title: item.title,
    excerpt: item.excerpt,
    description: item.description,
    status: item.status,
    statusLabel: item.statusLabel,
    city: item.city,
    district: item.district ?? '',
    category: item.category,
    location: item.location ?? '',
    publishedAt: toLocalDateTimeInput(item.publishedAt),
    supportCount: item.supportCount,
    viewsCount: item.viewsCount,
    commentsCount: item.commentsCount,
    imageUrl: item.imageUrl ?? '',
    isPublic: item.isPublic,
    attachments: item.attachments.map((attachment) => ({ ...attachment })),
    timeline: item.timeline.map((timelineItem) => ({
      status: timelineItem.status,
      title: timelineItem.title,
      happenedAt: toLocalDateTimeInput(timelineItem.date),
      text: timelineItem.text,
    })),
    documents: item.documents.map((document) => ({ ...document })),
    officialResponse: {
      enabled: item.officialResponse !== null,
      title: item.officialResponse?.title ?? '',
      text: item.officialResponse?.text ?? '',
      receivedAt: toLocalDateTimeInput(item.officialResponse?.receivedAt ?? null),
    },
  });
  void navigateTo(adminTabPath('appeals'));
  clearMessages();
};

const saveCategory = async (): Promise<void> => {
  await runAdminAction('Категория сохранена.', async () => {
    if (categoryForm.id) {
      await admin.updateCategory(categoryForm.id, categoryPayload());
    } else {
      await admin.createCategory(categoryPayload());
    }

    resetCategoryForm();
  });
};

const saveNews = async (): Promise<void> => {
  await runAdminAction('Новость сохранена.', async () => {
    if (newsForm.id) {
      await admin.updateNews(newsForm.id, newsPayload());
    } else {
      await admin.createNews(newsPayload());
    }

    resetNewsForm();
  });
};

const saveHomepageSlide = async (): Promise<void> => {
  await runAdminAction('Слайд сохранён.', async () => {
    if (homepageSlideForm.id) {
      await admin.updateHomepageSlide(homepageSlideForm.id, homepageSlidePayload());
    } else {
      await admin.createHomepageSlide(homepageSlidePayload());
    }

    resetHomepageSlideForm();
  });
};

const saveAdvertisement = async (): Promise<void> => {
  await runAdminAction('Рекламный баннер сохранён.', async () => {
    if (advertisementForm.id) {
      await admin.updateAdvertisement(advertisementForm.id, advertisementPayload());
    } else {
      await admin.createAdvertisement(advertisementPayload());
    }

    resetAdvertisementForm();
  });
};

const saveAppeal = async (): Promise<void> => {
  await runAdminAction('Обращение сохранено.', async () => {
    if (appealForm.id) {
      await admin.updateAppeal(appealForm.id, appealPayload());
    } else {
      await admin.createAppeal(appealPayload());
    }

    resetAppealForm();
  });
};

const deleteCategory = async (category: AdminCategoryDto): Promise<void> => {
  if (!confirm(`Удалить категорию «${category.title}»?`)) {
    return;
  }

  await runAdminAction('Категория удалена.', async () => {
    await admin.deleteCategory(category.id);

    if (categoryForm.id === category.id) {
      resetCategoryForm();
    }
  });
};

const deleteNews = async (item: AdminNewsDto): Promise<void> => {
  if (!confirm(`Удалить новость «${item.title}»?`)) {
    return;
  }

  await runAdminAction('Новость удалена.', async () => {
    await admin.deleteNews(item.id);

    if (newsForm.id === item.id) {
      resetNewsForm();
    }
  });
};

const deleteHomepageSlide = async (item: AdminHomepageSlideDto): Promise<void> => {
  if (!confirm(`Удалить слайд «${item.title}»?`)) {
    return;
  }

  await runAdminAction('Слайд удалён.', async () => {
    await admin.deleteHomepageSlide(item.id);

    if (homepageSlideForm.id === item.id) {
      resetHomepageSlideForm();
    }
  });
};

const deleteAdvertisement = async (item: AdminAdvertisementDto): Promise<void> => {
  if (!confirm(`Удалить рекламный баннер «${item.title}»?`)) {
    return;
  }

  await runAdminAction('Рекламный баннер удалён.', async () => {
    await admin.deleteAdvertisement(item.id);

    if (advertisementForm.id === item.id) {
      resetAdvertisementForm();
    }
  });
};

const deleteAppeal = async (item: AdminAppealDto): Promise<void> => {
  if (!confirm(`Удалить обращение «${item.title}»?`)) {
    return;
  }

  await runAdminAction('Обращение удалено.', async () => {
    await admin.deleteAppeal(item.id);

    if (appealForm.id === item.id) {
      resetAppealForm();
    }
  });
};

const addAttachment = (): void => {
  appealForm.attachments.push({
    type: 'image',
    url: '',
    title: '',
  });
};

const removeAttachment = (index: number): void => {
  appealForm.attachments.splice(index, 1);
};

const addTimelineItem = (): void => {
  appealForm.timeline.push({
    status: 'published',
    title: '',
    happenedAt: nowDateTimeInput(),
    text: '',
  });
};

const removeTimelineItem = (index: number): void => {
  appealForm.timeline.splice(index, 1);
};

const addDocument = (): void => {
  appealForm.documents.push({
    title: '',
    url: '',
  });
};

const removeDocument = (index: number): void => {
  appealForm.documents.splice(index, 1);
};

const syncAppealStatusLabel = (): void => {
  appealForm.statusLabel = appealStatusLabels[appealForm.status];
};

useSeoMeta({
  title: 'Админка',
  description: 'Панель управления публичным контентом.',
});
useNoindexSeo();

onMounted(async () => {
  const user = await auth.fetchMe();

  if (!user) {
    await navigateTo('/login');

    return;
  }

  if (!user.isAdmin) {
    accessDenied.value = true;
    pending.value = false;

    return;
  }

  if (shouldRedirectToCategories.value) {
    await navigateTo('/admin/categories', { replace: true });

    return;
  }

  try {
    await loadAdminContent();
  } catch (error: unknown) {
    errorMessage.value = resolveApiError(error);
  } finally {
    pending.value = false;
  }
});
</script>

<template>
  <div class="admin-page">
    <div class="container admin-layout">
      <aside class="admin-sidebar" aria-label="Разделы админки">
        <div class="admin-account">
          <span class="admin-account-icon">
            <LayoutAppIcon name="shield" />
          </span>
          <div>
            <strong>{{ currentUserName }}</strong>
            <small>{{ currentUserEmail }}</small>
          </div>
        </div>

        <nav class="admin-nav">
          <NuxtLink
            v-for="tab in tabs"
            :key="tab.id"
            :to="adminTabPath(tab.id)"
            :class="{ 'is-active': activeTab === tab.id }"
          >
            <LayoutAppIcon :name="tab.icon" />
            <span>{{ tab.label }}</span>
            <em>{{ formatRuNumber(tabCount(tab.id)) }}</em>
          </NuxtLink>
        </nav>

        <button class="admin-secondary-button" type="button" @click="auth.logout">
          <LayoutAppIcon name="back" />
          Выйти
        </button>
      </aside>

      <section class="admin-workspace">
        <header class="admin-topbar">
          <div>
            <span>Управление контентом</span>
            <h1>Админка</h1>
          </div>
          <button class="admin-secondary-button" type="button" :disabled="pending || saving || accessDenied" @click="loadAdminContent">
            <LayoutAppIcon name="refresh" />
            Обновить
          </button>
        </header>

        <div v-if="pending" class="admin-state">
          <LayoutAppIcon name="refresh" />
          <strong>Загрузка</strong>
        </div>

        <div v-else-if="accessDenied" class="admin-state admin-state--danger">
          <LayoutAppIcon name="lock" />
          <strong>Доступ запрещён</strong>
        </div>

        <template v-else>
          <section class="admin-metrics" aria-label="Сводка">
            <article>
              <span><LayoutAppIcon name="grid" /></span>
              <strong>{{ formatRuNumber(categoryRows.length) }}</strong>
              <p>категорий</p>
            </article>
            <article>
              <span><LayoutAppIcon name="play" /></span>
              <strong>{{ formatRuNumber(activeSlidesCount) }}</strong>
              <p>активных слайдов</p>
            </article>
            <article>
              <span><LayoutAppIcon name="camera" /></span>
              <strong>{{ formatRuNumber(activeAdvertisementsCount) }}</strong>
              <p>активной рекламы</p>
            </article>
            <article>
              <span><LayoutAppIcon name="book" /></span>
              <strong>{{ formatRuNumber(publishedNewsCount) }}</strong>
              <p>опубликованных новостей</p>
            </article>
            <article>
              <span><LayoutAppIcon name="file" /></span>
              <strong>{{ formatRuNumber(publicAppealsCount) }}</strong>
              <p>публичных обращений</p>
            </article>
          </section>

          <p v-if="statusMessage" class="form-message form-message--success">
            {{ statusMessage }}
          </p>
          <p v-if="errorMessage" class="form-message form-message--error">
            {{ errorMessage }}
          </p>

          <section v-if="activeTab === 'categories'" class="admin-panel">
            <div class="admin-panel-head">
              <h2>Категории</h2>
              <button class="admin-secondary-button" type="button" @click="resetCategoryForm">
                <LayoutAppIcon name="refresh" />
                Новая
              </button>
            </div>

            <div class="admin-split">
              <form class="admin-form" @submit.prevent="saveCategory">
                <h3>{{ categoryForm.id ? 'Редактирование' : 'Создание' }}</h3>
                <div class="admin-form-grid">
                  <label>
                    <span>Группа slug</span>
                    <input v-model="categoryForm.groupSlug" type="text" pattern="[a-z0-9-]+" required>
                  </label>
                  <label>
                    <span>Группа</span>
                    <input v-model="categoryForm.groupTitle" type="text">
                  </label>
                  <label>
                    <span>Slug</span>
                    <input v-model="categoryForm.slug" type="text" pattern="[a-z0-9-]+" required>
                  </label>
                  <label>
                    <span>Иконка</span>
                    <input v-model="categoryForm.icon" type="text">
                  </label>
                  <label class="wide">
                    <span>Название</span>
                    <input v-model="categoryForm.title" type="text" required>
                  </label>
                  <label class="wide">
                    <span>Описание</span>
                    <textarea v-model="categoryForm.description" rows="4" required />
                  </label>
                  <label>
                    <span>Сортировка</span>
                    <input v-model.number="categoryForm.sortOrder" type="number" min="0">
                  </label>
                  <label class="admin-check">
                    <input v-model="categoryForm.isActive" type="checkbox">
                    <span>Активна</span>
                  </label>
                </div>
                <button class="admin-primary-button" type="submit" :disabled="saving">
                  <LayoutAppIcon name="check" />
                  Сохранить
                </button>
              </form>

              <div class="admin-list">
                <article v-for="row in categoryRows" :key="row.category.id" class="admin-row">
                  <div>
                    <span>{{ row.group.title }}</span>
                    <h3>{{ row.category.title }}</h3>
                    <p>{{ row.category.description }}</p>
                    <small>{{ row.category.slug }} · {{ row.category.icon }}</small>
                  </div>
                  <div class="admin-row-actions">
                    <span class="admin-pill" :class="row.category.isActive ? 'is-green' : 'is-gray'">
                      {{ row.category.isActive ? 'активна' : 'скрыта' }}
                    </span>
                    <button type="button" class="admin-icon-button" aria-label="Редактировать категорию" @click="editCategory(row.category)">
                      <LayoutAppIcon name="edit" />
                    </button>
                    <button type="button" class="admin-icon-button is-danger" aria-label="Удалить категорию" @click="deleteCategory(row.category)">
                      <LayoutAppIcon name="trash" />
                    </button>
                  </div>
                </article>
              </div>
            </div>
          </section>

          <section v-if="activeTab === 'slides'" class="admin-panel">
            <div class="admin-panel-head">
              <h2>Слайдер</h2>
              <button class="admin-secondary-button" type="button" @click="resetHomepageSlideForm">
                <LayoutAppIcon name="refresh" />
                Новый
              </button>
            </div>

            <div class="admin-split">
              <form class="admin-form" @submit.prevent="saveHomepageSlide">
                <h3>{{ homepageSlideForm.id ? 'Редактирование' : 'Создание' }}</h3>
                <div class="admin-form-grid">
                  <label>
                    <span>Slug</span>
                    <input v-model="homepageSlideForm.slug" type="text" pattern="[a-z0-9-]+" required>
                  </label>
                  <label>
                    <span>Сортировка</span>
                    <input v-model.number="homepageSlideForm.sortOrder" type="number" min="0">
                  </label>
                  <label class="wide">
                    <span>Метка</span>
                    <input v-model="homepageSlideForm.label" type="text" required>
                  </label>
                  <label class="wide">
                    <span>Заголовок</span>
                    <input v-model="homepageSlideForm.title" type="text" required>
                  </label>
                  <label class="wide">
                    <span>Текст</span>
                    <textarea v-model="homepageSlideForm.lead" rows="4" required />
                  </label>
                  <label class="wide">
                    <span>Заметка</span>
                    <textarea v-model="homepageSlideForm.note" rows="3" />
                  </label>
                  <label class="wide">
                    <span>Изображение</span>
                    <input v-model="homepageSlideForm.imageUrl" type="text" required>
                  </label>
                  <label>
                    <span>Основная кнопка</span>
                    <input v-model="homepageSlideForm.primaryCtaLabel" type="text">
                  </label>
                  <label>
                    <span>URL основной кнопки</span>
                    <input v-model="homepageSlideForm.primaryCtaUrl" type="text">
                  </label>
                  <label>
                    <span>Вторая кнопка</span>
                    <input v-model="homepageSlideForm.secondaryCtaLabel" type="text">
                  </label>
                  <label>
                    <span>URL второй кнопки</span>
                    <input v-model="homepageSlideForm.secondaryCtaUrl" type="text">
                  </label>
                  <label class="admin-check">
                    <input v-model="homepageSlideForm.isActive" type="checkbox">
                    <span>Активен</span>
                  </label>
                </div>
                <button class="admin-primary-button" type="submit" :disabled="saving">
                  <LayoutAppIcon name="check" />
                  Сохранить
                </button>
              </form>

              <div class="admin-list">
                <article v-for="item in homepageSlides" :key="item.id" class="admin-row">
                  <div>
                    <span>{{ item.label }}</span>
                    <h3>{{ item.title }}</h3>
                    <p>{{ item.lead }}</p>
                    <small>{{ item.slug }} · {{ item.imageUrl }}</small>
                  </div>
                  <div class="admin-row-actions">
                    <span class="admin-pill" :class="item.isActive ? 'is-green' : 'is-gray'">
                      {{ item.isActive ? 'активен' : 'скрыт' }}
                    </span>
                    <button type="button" class="admin-icon-button" aria-label="Редактировать слайд" @click="editHomepageSlide(item)">
                      <LayoutAppIcon name="edit" />
                    </button>
                    <button type="button" class="admin-icon-button is-danger" aria-label="Удалить слайд" @click="deleteHomepageSlide(item)">
                      <LayoutAppIcon name="trash" />
                    </button>
                  </div>
                </article>
              </div>
            </div>
          </section>

          <section v-if="activeTab === 'advertisements'" class="admin-panel">
            <div class="admin-panel-head">
              <h2>Реклама</h2>
              <button class="admin-secondary-button" type="button" @click="resetAdvertisementForm">
                <LayoutAppIcon name="refresh" />
                Новая
              </button>
            </div>

            <div class="admin-split">
              <form class="admin-form" @submit.prevent="saveAdvertisement">
                <h3>{{ advertisementForm.id ? 'Редактирование' : 'Создание' }}</h3>
                <div class="admin-form-grid">
                  <label>
                    <span>Slug</span>
                    <input v-model="advertisementForm.slug" type="text" pattern="[a-z0-9-]+" required>
                  </label>
                  <label>
                    <span>Placement</span>
                    <input v-model="advertisementForm.placement" type="text" required>
                  </label>
                  <label>
                    <span>Заголовок</span>
                    <input v-model="advertisementForm.title" type="text" required>
                  </label>
                  <label>
                    <span>Метка</span>
                    <input v-model="advertisementForm.label" type="text">
                  </label>
                  <label class="wide">
                    <span>Описание</span>
                    <textarea v-model="advertisementForm.description" rows="3" />
                  </label>
                  <label class="wide">
                    <span>Изображение</span>
                    <input v-model="advertisementForm.imageUrl" type="text" required>
                  </label>
                  <label class="wide">
                    <span>Alt</span>
                    <input v-model="advertisementForm.alt" type="text" required>
                  </label>
                  <label class="wide">
                    <span>Ссылка</span>
                    <input v-model="advertisementForm.targetUrl" type="text" required>
                  </label>
                  <label>
                    <span>Сортировка</span>
                    <input v-model.number="advertisementForm.sortOrder" type="number" min="0">
                  </label>
                  <label>
                    <span>Начало</span>
                    <input v-model="advertisementForm.startsAt" type="datetime-local">
                  </label>
                  <label>
                    <span>Окончание</span>
                    <input v-model="advertisementForm.endsAt" type="datetime-local">
                  </label>
                  <label class="admin-check">
                    <input v-model="advertisementForm.isActive" type="checkbox">
                    <span>Активна</span>
                  </label>
                </div>
                <button class="admin-primary-button" type="submit" :disabled="saving">
                  <LayoutAppIcon name="check" />
                  Сохранить
                </button>
              </form>

              <div class="admin-list">
                <article v-for="item in advertisements" :key="item.id" class="admin-row">
                  <div>
                    <span>{{ item.placement }} · {{ item.label || 'без метки' }}</span>
                    <h3>{{ item.title }}</h3>
                    <p>{{ item.description || item.alt }}</p>
                    <small>{{ item.slug }} · {{ item.targetUrl }}</small>
                  </div>
                  <div class="admin-row-actions">
                    <span class="admin-pill" :class="item.isActive ? 'is-green' : 'is-gray'">
                      {{ item.isActive ? 'активна' : 'скрыта' }}
                    </span>
                    <a class="admin-icon-button" :href="item.targetUrl" target="_blank" rel="noopener noreferrer" aria-label="Открыть ссылку рекламы">
                      <LayoutAppIcon name="eye" />
                    </a>
                    <button type="button" class="admin-icon-button" aria-label="Редактировать рекламу" @click="editAdvertisement(item)">
                      <LayoutAppIcon name="edit" />
                    </button>
                    <button type="button" class="admin-icon-button is-danger" aria-label="Удалить рекламу" @click="deleteAdvertisement(item)">
                      <LayoutAppIcon name="trash" />
                    </button>
                  </div>
                </article>
              </div>
            </div>
          </section>

          <section v-if="activeTab === 'news'" class="admin-panel">
            <div class="admin-panel-head">
              <h2>Новости</h2>
              <button class="admin-secondary-button" type="button" @click="resetNewsForm">
                <LayoutAppIcon name="refresh" />
                Новая
              </button>
            </div>

            <div class="admin-split">
              <form class="admin-form" @submit.prevent="saveNews">
                <h3>{{ newsForm.id ? 'Редактирование' : 'Создание' }}</h3>
                <div class="admin-form-grid">
                  <label>
                    <span>Slug</span>
                    <input v-model="newsForm.slug" type="text" pattern="[a-z0-9-]+" required>
                  </label>
                  <label>
                    <span>Статус</span>
                    <select v-model="newsForm.status">
                      <option value="draft">Черновик</option>
                      <option value="published">Опубликовано</option>
                      <option value="archived">Архив</option>
                    </select>
                  </label>
                  <label class="wide">
                    <span>Название</span>
                    <input v-model="newsForm.title" type="text" required>
                  </label>
                  <label>
                    <span>Категория</span>
                    <input v-model="newsForm.category" type="text" required>
                  </label>
                  <label>
                    <span>Дата публикации</span>
                    <input v-model="newsForm.publishedAt" type="datetime-local">
                  </label>
                  <label class="wide">
                    <span>Изображение</span>
                    <input v-model="newsForm.imageUrl" type="text">
                  </label>
                  <label class="wide">
                    <span>Анонс</span>
                    <textarea v-model="newsForm.excerpt" rows="3" required />
                  </label>
                  <label class="wide">
                    <span>Текст</span>
                    <textarea v-model="newsForm.content" rows="8" required />
                  </label>
                </div>
                <button class="admin-primary-button" type="submit" :disabled="saving">
                  <LayoutAppIcon name="check" />
                  Сохранить
                </button>
              </form>

              <div class="admin-list">
                <article v-for="item in newsItems" :key="item.id" class="admin-row">
                  <div>
                    <span>{{ item.category }}</span>
                    <h3>{{ item.title }}</h3>
                    <p>{{ item.excerpt }}</p>
                    <small>{{ item.slug }} · {{ formatOptionalDateTime(item.publishedAt) }}</small>
                  </div>
                  <div class="admin-row-actions">
                    <span class="admin-pill" :class="item.status === 'published' ? 'is-green' : 'is-gray'">
                      {{ newsStatusLabels[item.status] }}
                    </span>
                    <NuxtLink class="admin-icon-button" :to="`/news/${item.slug}`" aria-label="Открыть новость">
                      <LayoutAppIcon name="eye" />
                    </NuxtLink>
                    <button type="button" class="admin-icon-button" aria-label="Редактировать новость" @click="editNews(item)">
                      <LayoutAppIcon name="edit" />
                    </button>
                    <button type="button" class="admin-icon-button is-danger" aria-label="Удалить новость" @click="deleteNews(item)">
                      <LayoutAppIcon name="trash" />
                    </button>
                  </div>
                </article>
              </div>
            </div>
          </section>

          <section v-if="activeTab === 'appeals'" class="admin-panel">
            <div class="admin-panel-head">
              <h2>Обращения</h2>
              <button class="admin-secondary-button" type="button" @click="resetAppealForm">
                <LayoutAppIcon name="refresh" />
                Новое
              </button>
            </div>

            <div class="admin-split admin-split--appeals">
              <form class="admin-form" @submit.prevent="saveAppeal">
                <h3>{{ appealForm.id ? 'Редактирование' : 'Создание' }}</h3>
                <div class="admin-form-grid">
                  <label>
                    <span>Slug</span>
                    <input v-model="appealForm.slug" type="text" pattern="[a-z0-9-]+" required>
                  </label>
                  <label>
                    <span>Статус</span>
                    <select v-model="appealForm.status" @change="syncAppealStatusLabel">
                      <option value="draft">Черновик</option>
                      <option value="checking">На проверке</option>
                      <option value="active">В работе</option>
                      <option value="resolved">Решено</option>
                    </select>
                  </label>
                  <label>
                    <span>Метка статуса</span>
                    <input v-model="appealForm.statusLabel" type="text" required>
                  </label>
                  <label>
                    <span>Дата публикации</span>
                    <input v-model="appealForm.publishedAt" type="datetime-local">
                  </label>
                  <label class="wide">
                    <span>Название</span>
                    <input v-model="appealForm.title" type="text" required>
                  </label>
                  <label>
                    <span>Город</span>
                    <input v-model="appealForm.city" type="text" required>
                  </label>
                  <label>
                    <span>Район</span>
                    <input v-model="appealForm.district" type="text">
                  </label>
                  <label>
                    <span>Категория</span>
                    <input v-model="appealForm.category" type="text" required>
                  </label>
                  <label>
                    <span>Адрес</span>
                    <input v-model="appealForm.location" type="text">
                  </label>
                  <label class="wide">
                    <span>Изображение</span>
                    <input v-model="appealForm.imageUrl" type="text">
                  </label>
                  <label class="wide">
                    <span>Анонс</span>
                    <textarea v-model="appealForm.excerpt" rows="3" required />
                  </label>
                  <label class="wide">
                    <span>Описание</span>
                    <textarea v-model="appealForm.description" rows="7" required />
                  </label>
                  <label>
                    <span>Поддержали</span>
                    <input v-model.number="appealForm.supportCount" type="number" min="0">
                  </label>
                  <label>
                    <span>Просмотры</span>
                    <input v-model.number="appealForm.viewsCount" type="number" min="0">
                  </label>
                  <label>
                    <span>Комментарии</span>
                    <input v-model.number="appealForm.commentsCount" type="number" min="0">
                  </label>
                  <label class="admin-check">
                    <input v-model="appealForm.isPublic" type="checkbox">
                    <span>Публичное</span>
                  </label>
                </div>

                <section class="admin-fieldset">
                  <div class="admin-fieldset-head">
                    <h4>Вложения</h4>
                    <button class="admin-secondary-button" type="button" @click="addAttachment">
                      <LayoutAppIcon name="plus" />
                      Добавить
                    </button>
                  </div>
                  <div class="admin-repeater">
                    <div v-for="(attachment, index) in appealForm.attachments" :key="`attachment-${index}`" class="admin-repeater-row">
                      <label>
                        <span>Тип</span>
                        <input v-model="attachment.type" type="text" required>
                      </label>
                      <label>
                        <span>URL</span>
                        <input v-model="attachment.url" type="text" required>
                      </label>
                      <label>
                        <span>Название</span>
                        <input v-model="attachment.title" type="text" required>
                      </label>
                      <button class="admin-icon-button is-danger" type="button" aria-label="Удалить вложение" @click="removeAttachment(index)">
                        <LayoutAppIcon name="trash" />
                      </button>
                    </div>
                  </div>
                </section>

                <section class="admin-fieldset">
                  <div class="admin-fieldset-head">
                    <h4>Таймлайн</h4>
                    <button class="admin-secondary-button" type="button" @click="addTimelineItem">
                      <LayoutAppIcon name="plus" />
                      Добавить
                    </button>
                  </div>
                  <div class="admin-repeater">
                    <div v-for="(timelineItem, index) in appealForm.timeline" :key="`timeline-${index}`" class="admin-repeater-row admin-repeater-row--timeline">
                      <label>
                        <span>Статус</span>
                        <input v-model="timelineItem.status" type="text" required>
                      </label>
                      <label>
                        <span>Дата</span>
                        <input v-model="timelineItem.happenedAt" type="datetime-local" required>
                      </label>
                      <label>
                        <span>Название</span>
                        <input v-model="timelineItem.title" type="text" required>
                      </label>
                      <label class="wide">
                        <span>Текст</span>
                        <textarea v-model="timelineItem.text" rows="3" required />
                      </label>
                      <button class="admin-icon-button is-danger" type="button" aria-label="Удалить событие" @click="removeTimelineItem(index)">
                        <LayoutAppIcon name="trash" />
                      </button>
                    </div>
                  </div>
                </section>

                <section class="admin-fieldset">
                  <div class="admin-fieldset-head">
                    <h4>Документы</h4>
                    <button class="admin-secondary-button" type="button" @click="addDocument">
                      <LayoutAppIcon name="plus" />
                      Добавить
                    </button>
                  </div>
                  <div class="admin-repeater">
                    <div v-for="(document, index) in appealForm.documents" :key="`document-${index}`" class="admin-repeater-row">
                      <label>
                        <span>Название</span>
                        <input v-model="document.title" type="text" required>
                      </label>
                      <label class="wide">
                        <span>URL</span>
                        <input v-model="document.url" type="text" required>
                      </label>
                      <button class="admin-icon-button is-danger" type="button" aria-label="Удалить документ" @click="removeDocument(index)">
                        <LayoutAppIcon name="trash" />
                      </button>
                    </div>
                  </div>
                </section>

                <section class="admin-fieldset">
                  <label class="admin-check admin-check--inline">
                    <input v-model="appealForm.officialResponse.enabled" type="checkbox">
                    <span>Официальный ответ</span>
                  </label>
                  <div v-if="appealForm.officialResponse.enabled" class="admin-form-grid">
                    <label>
                      <span>Заголовок</span>
                      <input v-model="appealForm.officialResponse.title" type="text" required>
                    </label>
                    <label>
                      <span>Дата получения</span>
                      <input v-model="appealForm.officialResponse.receivedAt" type="datetime-local">
                    </label>
                    <label class="wide">
                      <span>Текст</span>
                      <textarea v-model="appealForm.officialResponse.text" rows="4" required />
                    </label>
                  </div>
                </section>

                <button class="admin-primary-button" type="submit" :disabled="saving">
                  <LayoutAppIcon name="check" />
                  Сохранить
                </button>
              </form>

              <div class="admin-list">
                <article v-for="item in appeals" :key="item.id" class="admin-row">
                  <div>
                    <span>{{ item.category }} · {{ item.city }}</span>
                    <h3>{{ item.title }}</h3>
                    <p>{{ item.excerpt }}</p>
                    <small>{{ item.slug }} · {{ formatOptionalDateTime(item.publishedAt) }}</small>
                  </div>
                  <div class="admin-row-actions">
                    <span class="admin-pill" :class="item.isPublic ? 'is-green' : 'is-gray'">
                      {{ item.isPublic ? 'публичное' : 'скрыто' }}
                    </span>
                    <span class="admin-pill is-blue">{{ appealStatusLabels[item.status] }}</span>
                    <NuxtLink class="admin-icon-button" :to="`/appeals/${item.slug}`" aria-label="Открыть обращение">
                      <LayoutAppIcon name="eye" />
                    </NuxtLink>
                    <button type="button" class="admin-icon-button" aria-label="Редактировать обращение" @click="editAppeal(item)">
                      <LayoutAppIcon name="edit" />
                    </button>
                    <button type="button" class="admin-icon-button is-danger" aria-label="Удалить обращение" @click="deleteAppeal(item)">
                      <LayoutAppIcon name="trash" />
                    </button>
                  </div>
                </article>
              </div>
            </div>
          </section>
        </template>
      </section>
    </div>
  </div>
</template>
