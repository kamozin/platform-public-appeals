<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealCommentDto, AppealDetailDto } from '~/types/api/public-content';
import type { AppealDraftDto, AuthUserDto, DashboardListDto } from '~/types/api/private';
import type { AppIconName } from '~/components/layout/AppIcon.vue';
import { formatRuDateTime, formatRuNumber } from '~/utils/formatters';

type DashboardTabId =
  | 'profile'
  | 'drafts'
  | 'appeals'
  | 'saved'
  | 'comments'
  | 'notifications'
  | 'security'
  | 'achievements';

type DashboardTab = {
  id: DashboardTabId;
  label: string;
  icon: AppIconName;
};

type AvatarMimeType = 'image/jpeg' | 'image/png' | 'image/webp';
type AvatarStatusKind = 'success' | 'error' | null;
type AvatarCropDragState = {
  pointerId: number;
  startX: number;
  startY: number;
  originX: number;
  originY: number;
};
type SecurityModalId = 'password' | 'email-two-factor' | 'disable-email-two-factor';
type EmailTwoFactorChallengeDto = {
  id: string;
  expiresAt: string | null;
  maskedTarget: string;
  devCode?: string;
};
type EmailTwoFactorStateDto = {
  emailTwoFactorEnabled: boolean;
};

const avatarAllowedTypes: readonly AvatarMimeType[] = ['image/jpeg', 'image/png', 'image/webp'];
const avatarSourceMaxBytes = 10 * 1024 * 1024;
const avatarUploadMaxBytes = 2 * 1024 * 1024;
const avatarSize = 512;
const avatarOutputType: AvatarMimeType = 'image/webp';
const avatarOutputQuality = 0.88;
const avatarCropZoomMin = 1;
const avatarCropZoomMax = 3;
const avatarCropZoomStep = 0.01;

const api = useApi();
const auth = useAuth();
const route = useRoute();
const { handleImageError, resolveImageUrl } = useImageFallback('/assets/issue-road.png');

const dashboardTabPaths: Record<DashboardTabId, string> = {
  profile: '/dashboard/profile',
  drafts: '/dashboard/drafts',
  appeals: '/dashboard/appeals',
  saved: '/dashboard/saved',
  comments: '/dashboard/comments',
  notifications: '/dashboard/notifications',
  security: '/dashboard/security',
  achievements: '/dashboard/achievements',
};
const dashboardTabIds = Object.keys(dashboardTabPaths) as DashboardTabId[];
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
const isDashboardTabId = (value: string | null): value is DashboardTabId => {
  return value !== null && (dashboardTabIds as readonly string[]).includes(value);
};
const activeTab = computed<DashboardTabId>(() => {
  if (isDashboardTabId(routeSection.value)) {
    return routeSection.value;
  }

  return 'profile';
});
const shouldRedirectToProfile = computed(() => {
  const normalizedPath = route.path.replace(/\/$/, '');

  return normalizedPath === '/dashboard' || !isDashboardTabId(routeSection.value);
});
const pending = ref(true);
const statusMessage = ref('');
const avatarPending = ref(false);
const avatarStatusMessage = ref('');
const avatarStatusKind = ref<AvatarStatusKind>(null);
const avatarPreviewUrl = ref<string | null>(null);
const avatarCropOpen = ref(false);
const avatarCropError = ref('');
const avatarCropImageUrl = ref<string | null>(null);
const avatarCropImage = ref<HTMLImageElement | null>(null);
const avatarCropPanelRef = ref<HTMLElement | null>(null);
const avatarCropFrameRef = ref<HTMLElement | null>(null);
const securityModal = ref<SecurityModalId | null>(null);
const securityModalPanelRef = ref<HTMLElement | null>(null);
const avatarCropFrameSize = ref(320);
const avatarCropZoom = ref(1);
const avatarCropRotation = ref(0);
const avatarCropOffset = reactive({
  x: 0,
  y: 0,
});
const avatarCropDrag = ref<AvatarCropDragState | null>(null);
const profile = ref<AuthUserDto | null>(null);
const drafts = ref<AppealDraftDto[]>([]);
const appeals = ref<AppealDraftDto[]>([]);
const saved = ref<AppealDetailDto[]>([]);
const comments = ref<AppealCommentDto[]>([]);
const notifications = ref<Array<{ id: string; title: string; text: string; read: boolean }>>([]);
const sessions = ref<Array<{ id: string; name: string; createdAt: string | null; lastUsedAt: string | null }>>([]);
const achievements = ref<Array<{ id: string; title: string; description: string; earned: boolean }>>([]);

const profileForm = reactive({
  name: '',
  phone: '',
  notifications: true,
});
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const passwordPending = ref(false);
const passwordSuccessMessage = ref('');
const passwordErrorMessage = ref('');
const emailTwoFactorForm = reactive({
  current_password: '',
  challenge_id: '',
  code: '',
  disable_password: '',
});
const emailTwoFactorPending = ref(false);
const emailTwoFactorSuccessMessage = ref('');
const emailTwoFactorErrorMessage = ref('');
const emailTwoFactorMaskedTarget = ref('');

const tabs: readonly DashboardTab[] = [
  { id: 'profile', label: 'Профиль', icon: 'user' },
  { id: 'drafts', label: 'Черновики', icon: 'edit' },
  { id: 'appeals', label: 'Обращения', icon: 'file' },
  { id: 'saved', label: 'Сохранённые', icon: 'heart' },
  { id: 'comments', label: 'Комментарии', icon: 'comment' },
  { id: 'notifications', label: 'Уведомления', icon: 'bell' },
  { id: 'security', label: 'Безопасность', icon: 'shield' },
  { id: 'achievements', label: 'Достижения', icon: 'trend' },
];

const appealCategoryLabels: Readonly<Record<string, string>> = {
  zhkh: 'ЖКХ',
  roads: 'Дороги',
  transport: 'Транспорт',
  improvement: 'Благоустройство',
  healthcare: 'Здравоохранение',
  education: 'Образование',
  'social-support': 'Социальная защита',
  ecology: 'Экология',
  safety: 'Безопасность',
  'public-services': 'Госуслуги и документы',
  'land-real-estate': 'Земля и недвижимость',
  authorities: 'Работа органов власти',
  telecom: 'Связь и интернет',
  'commerce-services': 'Торговля и услуги',
  other: 'Другое',
};

const appealStatusLabels: Readonly<Record<AppealDraftDto['status'], string>> = {
  draft: 'Черновик',
  approved: 'Одобрено',
  needs_changes: 'Нужны правки',
  pending_moderation: 'На модерации',
  rejected: 'Отклонено',
};

const appealStatusPillClasses: Readonly<Record<AppealDraftDto['status'], string>> = {
  draft: 'gray',
  approved: 'green',
  needs_changes: 'orange',
  pending_moderation: 'orange',
  rejected: 'red',
};

const commentStatusLabels: Readonly<Record<string, string>> = {
  published: 'Опубликован',
  pending: 'На проверке',
  moderation: 'На модерации',
  rejected: 'Отклонён',
  hidden: 'Скрыт',
};

const displayName = computed(() => profile.value?.name || auth.user.value?.name || 'Личный кабинет');
const avatarDisplayUrl = computed(() => avatarPreviewUrl.value || profile.value?.avatarUrl || null);
const initials = computed(() => {
  return displayName.value
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('') || 'РД';
});
const avatarCropImageStyle = computed(() => {
  const image = avatarCropImage.value;

  if (!image) {
    return {};
  }

  const baseScale = getAvatarCropBaseScale(avatarCropFrameSize.value, avatarCropRotation.value, image);

  return {
    height: `${image.naturalHeight * baseScale}px`,
    transform: `rotate(${avatarCropRotation.value}deg) scale(${avatarCropZoom.value})`,
    width: `${image.naturalWidth * baseScale}px`,
  };
});
const avatarCropStageStyle = computed(() => {
  return {
    transform: `translate(-50%, -50%) translate(${avatarCropOffset.x}px, ${avatarCropOffset.y}px)`,
  };
});
const unreadNotificationsCount = computed(() => notifications.value.filter((item) => !item.read).length);
const earnedAchievementsCount = computed(() => achievements.value.filter((item) => item.earned).length);
const totalAppealsCount = computed(() => drafts.value.length + appeals.value.length);
const securityModalPending = computed(() => passwordPending.value || emailTwoFactorPending.value);
const dashboardTabPath = (id: DashboardTabId): string => dashboardTabPaths[id];

const formatOptionalDate = (value: string | null): string => {
  if (!value) {
    return 'активна';
  }

  return formatRuDateTime(value);
};

const formatAppealCategory = (category: string | null, fallback = 'Категория уточняется'): string => {
  const value = category?.trim();

  if (!value) {
    return fallback;
  }

  if (appealCategoryLabels[value]) {
    return appealCategoryLabels[value];
  }

  if (/[А-Яа-яЁё]/.test(value)) {
    return value;
  }

  return fallback;
};

const formatAppealStatus = (status: AppealDraftDto['status']): string => {
  return appealStatusLabels[status] ?? 'Статус уточняется';
};

const appealStatusPillClass = (status: AppealDraftDto['status']): string => {
  return appealStatusPillClasses[status] ?? 'gray';
};

const appealEditLink = (appeal: AppealDraftDto): string => {
  return `/appeal/new?draft=${encodeURIComponent(appeal.id)}`;
};

const formatCommentStatus = (status: string): string => {
  return commentStatusLabels[status] ?? 'Статус уточняется';
};

useNoindexSeo();

const loadDashboard = async (): Promise<void> => {
  const headers = auth.authHeaders();
  const [
    profileResponse,
    draftsResponse,
    appealsResponse,
    savedResponse,
    commentsResponse,
    notificationsResponse,
    sessionsResponse,
    achievementsResponse,
  ] = await Promise.all([
    api.get<ApiDataEnvelope<AuthUserDto>>('/profile', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealDraftDto>>>('/dashboard/drafts', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealDraftDto>>>('/dashboard/appeals', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealDetailDto>>>('/dashboard/saved-appeals', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<AppealCommentDto>>>('/dashboard/comments', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<{ id: string; title: string; text: string; read: boolean }>>>('/dashboard/notifications', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<{ id: string; name: string; createdAt: string | null; lastUsedAt: string | null }>>>('/dashboard/security/sessions', { headers }),
    api.get<ApiDataEnvelope<DashboardListDto<{ id: string; title: string; description: string; earned: boolean }>>>('/dashboard/achievements', { headers }),
  ]);

  profile.value = profileResponse.data;
  profileForm.name = profile.value.name;
  profileForm.phone = profile.value.phone ?? '';
  profileForm.notifications = profile.value.notificationsEnabled;
  drafts.value = draftsResponse.data.items;
  appeals.value = appealsResponse.data.items;
  saved.value = savedResponse.data.items;
  comments.value = commentsResponse.data.items;
  notifications.value = notificationsResponse.data.items;
  sessions.value = sessionsResponse.data.items;
  achievements.value = achievementsResponse.data.items;
};

onMounted(async () => {
  const user = await auth.fetchMe();

  if (!user) {
    await navigateTo('/login');

    return;
  }

  if (shouldRedirectToProfile.value) {
    await navigateTo('/dashboard/profile', { replace: true });

    return;
  }

  try {
    await loadDashboard();
  } finally {
    pending.value = false;
  }
});

const updateProfile = async (): Promise<void> => {
  const response = await api.request<ApiDataEnvelope<AuthUserDto>>('/profile', 'PATCH', {
    body: profileForm,
    headers: auth.authHeaders(),
  });
  applyProfile(response.data);
  statusMessage.value = 'Профиль обновлён.';
};

const getApiErrorMessage = (error: unknown, fallback: string): string => {
  const apiMessage = (error as { data?: { error?: { message?: unknown } } })?.data?.error?.message;

  if (typeof apiMessage === 'string' && apiMessage.trim() !== '') {
    return apiMessage;
  }

  if (error instanceof Error && error.message.trim() !== '') {
    return error.message;
  }

  return fallback;
};

const resetPasswordForm = (): void => {
  passwordForm.current_password = '';
  passwordForm.password = '';
  passwordForm.password_confirmation = '';
};

const resetEmailTwoFactorForm = (): void => {
  emailTwoFactorForm.current_password = '';
  emailTwoFactorForm.challenge_id = '';
  emailTwoFactorForm.code = '';
  emailTwoFactorForm.disable_password = '';
  emailTwoFactorMaskedTarget.value = '';
};

const openPasswordModal = (): void => {
  resetPasswordForm();
  passwordSuccessMessage.value = '';
  passwordErrorMessage.value = '';
  securityModal.value = 'password';
};

const openEmailTwoFactorModal = (): void => {
  resetEmailTwoFactorForm();
  emailTwoFactorSuccessMessage.value = '';
  emailTwoFactorErrorMessage.value = '';
  securityModal.value = 'email-two-factor';
};

const openDisableEmailTwoFactorModal = (): void => {
  resetEmailTwoFactorForm();
  emailTwoFactorSuccessMessage.value = '';
  emailTwoFactorErrorMessage.value = '';
  securityModal.value = 'disable-email-two-factor';
};

const closeSecurityModal = (): void => {
  if (securityModalPending.value) {
    return;
  }

  securityModal.value = null;
};

const changePassword = async (): Promise<void> => {
  passwordPending.value = true;
  passwordSuccessMessage.value = '';
  passwordErrorMessage.value = '';

  try {
    await api.request<ApiDataEnvelope<{ changed: boolean }>>('/profile/password', 'PATCH', {
      body: passwordForm,
      headers: auth.authHeaders(),
    });
    resetPasswordForm();
    passwordSuccessMessage.value = 'Пароль обновлён. Остальные активные сеансы завершены.';
    securityModal.value = null;
  } catch (error) {
    passwordErrorMessage.value = getApiErrorMessage(error, 'Не удалось изменить пароль.');
  } finally {
    passwordPending.value = false;
  }
};

const sendEmailTwoFactorCode = async (): Promise<void> => {
  emailTwoFactorPending.value = true;
  emailTwoFactorSuccessMessage.value = '';
  emailTwoFactorErrorMessage.value = '';

  try {
    const response = await api.request<ApiDataEnvelope<EmailTwoFactorChallengeDto>>('/profile/security/email-2fa/send', 'POST', {
      body: {
        current_password: emailTwoFactorForm.current_password,
      },
      headers: auth.authHeaders(),
    });
    emailTwoFactorForm.challenge_id = response.data.id;
    emailTwoFactorForm.code = '';
    emailTwoFactorMaskedTarget.value = response.data.maskedTarget;
    emailTwoFactorSuccessMessage.value = `Код отправлен на ${response.data.maskedTarget}.`;
  } catch (error) {
    emailTwoFactorErrorMessage.value = getApiErrorMessage(error, 'Не удалось отправить код.');
  } finally {
    emailTwoFactorPending.value = false;
  }
};

const enableEmailTwoFactor = async (): Promise<void> => {
  emailTwoFactorPending.value = true;
  emailTwoFactorSuccessMessage.value = '';
  emailTwoFactorErrorMessage.value = '';

  try {
    const response = await api.request<ApiDataEnvelope<EmailTwoFactorStateDto>>('/profile/security/email-2fa/enable', 'POST', {
      body: {
        challenge_id: emailTwoFactorForm.challenge_id,
        code: emailTwoFactorForm.code,
      },
      headers: auth.authHeaders(),
    });

    if (profile.value) {
      applyProfile({
        ...profile.value,
        emailTwoFactorEnabled: response.data.emailTwoFactorEnabled,
      });
    }

    resetEmailTwoFactorForm();
    emailTwoFactorSuccessMessage.value = 'Двухфакторная проверка по email включена.';
    securityModal.value = null;
  } catch (error) {
    emailTwoFactorErrorMessage.value = getApiErrorMessage(error, 'Не удалось включить двухфакторную проверку.');
  } finally {
    emailTwoFactorPending.value = false;
  }
};

const disableEmailTwoFactor = async (): Promise<void> => {
  emailTwoFactorPending.value = true;
  emailTwoFactorSuccessMessage.value = '';
  emailTwoFactorErrorMessage.value = '';

  try {
    const response = await api.request<ApiDataEnvelope<EmailTwoFactorStateDto>>('/profile/security/email-2fa', 'DELETE', {
      body: {
        current_password: emailTwoFactorForm.disable_password,
      },
      headers: auth.authHeaders(),
    });

    if (profile.value) {
      applyProfile({
        ...profile.value,
        emailTwoFactorEnabled: response.data.emailTwoFactorEnabled,
      });
    }

    resetEmailTwoFactorForm();
    emailTwoFactorSuccessMessage.value = 'Двухфакторная проверка по email отключена.';
    securityModal.value = null;
  } catch (error) {
    emailTwoFactorErrorMessage.value = getApiErrorMessage(error, 'Не удалось отключить двухфакторную проверку.');
  } finally {
    emailTwoFactorPending.value = false;
  }
};

const isAvatarMimeType = (type: string): type is AvatarMimeType => {
  return avatarAllowedTypes.includes(type as AvatarMimeType);
};

const applyProfile = (user: AuthUserDto): void => {
  profile.value = user;
  auth.user.value = user;
};

const setAvatarStatus = (message: string, kind: AvatarStatusKind): void => {
  avatarStatusMessage.value = message;
  avatarStatusKind.value = kind;
};

const clearAvatarPreview = (): void => {
  if (!avatarPreviewUrl.value) {
    return;
  }

  URL.revokeObjectURL(avatarPreviewUrl.value);
  avatarPreviewUrl.value = null;
};

const setAvatarPreview = (blob: Blob): void => {
  clearAvatarPreview();
  avatarPreviewUrl.value = URL.createObjectURL(blob);
};

const getAvatarErrorMessage = (error: unknown, fallback: string): string => {
  if (error instanceof Error) {
    return error.message;
  }

  return fallback;
};

const loadImageFromUrl = async (imageUrl: string): Promise<HTMLImageElement> => {
  return await new Promise((resolve, reject) => {
    const image = new Image();
    image.onload = (): void => {
      resolve(image);
    };
    image.onerror = (): void => {
      reject(new Error('Не удалось прочитать изображение.'));
    };
    image.src = imageUrl;
  });
};

const canvasToBlob = async (canvas: HTMLCanvasElement): Promise<Blob> => {
  return await new Promise((resolve, reject) => {
    canvas.toBlob((blob) => {
      if (!blob) {
        reject(new Error('Не удалось обработать изображение.'));

        return;
      }

      resolve(blob);
    }, avatarOutputType, avatarOutputQuality);
  });
};

const normalizeAvatarRotation = (rotation: number): number => {
  return ((rotation % 360) + 360) % 360;
};

const getAvatarCropOrientedSize = (
  image: HTMLImageElement,
  rotation: number,
): { width: number; height: number } => {
  const normalizedRotation = normalizeAvatarRotation(rotation);

  if (normalizedRotation === 90 || normalizedRotation === 270) {
    return {
      height: image.naturalWidth,
      width: image.naturalHeight,
    };
  }

  return {
    height: image.naturalHeight,
    width: image.naturalWidth,
  };
};

const getAvatarCropBaseScale = (
  frameSize: number,
  rotation: number,
  image: HTMLImageElement,
): number => {
  const safeFrameSize = Math.max(frameSize, 1);
  const orientedSize = getAvatarCropOrientedSize(image, rotation);

  return Math.max(
    safeFrameSize / orientedSize.width,
    safeFrameSize / orientedSize.height,
  );
};

const clampNumber = (value: number, min: number, max: number): number => {
  return Math.min(Math.max(value, min), max);
};

const updateAvatarCropFrameSize = (): void => {
  const frameSize = avatarCropFrameRef.value?.clientWidth ?? 0;

  if (!Number.isFinite(frameSize) || frameSize <= 0) {
    return;
  }

  avatarCropFrameSize.value = frameSize;
};

const clampAvatarCropOffset = (): void => {
  const image = avatarCropImage.value;

  if (!image) {
    avatarCropOffset.x = 0;
    avatarCropOffset.y = 0;

    return;
  }

  const frameSize = Math.max(avatarCropFrameSize.value, 1);
  const orientedSize = getAvatarCropOrientedSize(image, avatarCropRotation.value);
  const baseScale = getAvatarCropBaseScale(frameSize, avatarCropRotation.value, image);
  const scaledWidth = orientedSize.width * baseScale * avatarCropZoom.value;
  const scaledHeight = orientedSize.height * baseScale * avatarCropZoom.value;
  const limitX = Math.max((scaledWidth - frameSize) / 2, 0);
  const limitY = Math.max((scaledHeight - frameSize) / 2, 0);

  avatarCropOffset.x = clampNumber(avatarCropOffset.x, -limitX, limitX);
  avatarCropOffset.y = clampNumber(avatarCropOffset.y, -limitY, limitY);
};

const resetAvatarCropTransform = (): void => {
  avatarCropZoom.value = 1;
  avatarCropRotation.value = 0;
  avatarCropOffset.x = 0;
  avatarCropOffset.y = 0;
};

const clearAvatarCropSource = (): void => {
  if (avatarCropImageUrl.value) {
    URL.revokeObjectURL(avatarCropImageUrl.value);
  }

  avatarCropImageUrl.value = null;
  avatarCropImage.value = null;
  avatarCropDrag.value = null;
  avatarCropError.value = '';
  resetAvatarCropTransform();
};

const validateAvatarFile = (file: File): void => {
  if (!isAvatarMimeType(file.type)) {
    throw new Error('Загрузите изображение JPG, PNG или WebP.');
  }

  if (file.size > avatarSourceMaxBytes) {
    throw new Error('Исходное изображение должно быть не больше 10 МБ.');
  }
};

const openAvatarCrop = async (file: File): Promise<void> => {
  validateAvatarFile(file);
  clearAvatarCropSource();

  const imageUrl = URL.createObjectURL(file);

  try {
    const image = await loadImageFromUrl(imageUrl);

    if (image.naturalWidth <= 0 || image.naturalHeight <= 0) {
      throw new Error('Не удалось определить размер изображения.');
    }

    avatarCropImageUrl.value = imageUrl;
    avatarCropImage.value = image;
    avatarCropOpen.value = true;

    await nextTick();
    updateAvatarCropFrameSize();
    clampAvatarCropOffset();
    avatarCropPanelRef.value?.focus();
  } catch (error) {
    URL.revokeObjectURL(imageUrl);

    throw error;
  }
};

const renderAvatarCropFile = async (): Promise<File> => {
  const image = avatarCropImage.value;

  if (!image) {
    throw new Error('Выберите изображение для аватарки.');
  }

  updateAvatarCropFrameSize();
  clampAvatarCropOffset();

  const frameSize = Math.max(avatarCropFrameSize.value, 1);
  const offsetScale = avatarSize / frameSize;
  const orientedSize = getAvatarCropOrientedSize(image, avatarCropRotation.value);
  const baseScale = Math.max(
    avatarSize / orientedSize.width,
    avatarSize / orientedSize.height,
  );
  const drawScale = baseScale * avatarCropZoom.value;
  const canvas = document.createElement('canvas');
  canvas.width = avatarSize;
  canvas.height = avatarSize;

  const context = canvas.getContext('2d');

  if (!context) {
    throw new Error('Браузер не смог подготовить изображение.');
  }

  context.imageSmoothingEnabled = true;
  context.imageSmoothingQuality = 'high';
  context.translate(
    (avatarSize / 2) + (avatarCropOffset.x * offsetScale),
    (avatarSize / 2) + (avatarCropOffset.y * offsetScale),
  );
  context.rotate((normalizeAvatarRotation(avatarCropRotation.value) * Math.PI) / 180);
  context.scale(drawScale, drawScale);
  context.drawImage(image, -image.naturalWidth / 2, -image.naturalHeight / 2);

  const processedBlob = await canvasToBlob(canvas);

  if (processedBlob.size > avatarUploadMaxBytes) {
    throw new Error('После обработки аватарка всё ещё больше 2 МБ. Выберите другое изображение.');
  }

  return new File([processedBlob], 'avatar.webp', {
    lastModified: Date.now(),
    type: processedBlob.type || avatarOutputType,
  });
};

const closeAvatarCrop = (): void => {
  avatarCropOpen.value = false;
  clearAvatarCropSource();
};

const cancelAvatarCrop = (): void => {
  if (avatarPending.value) {
    return;
  }

  closeAvatarCrop();
};

const rotateAvatarCrop = (): void => {
  avatarCropRotation.value = normalizeAvatarRotation(avatarCropRotation.value + 90);
  nextTick(() => {
    updateAvatarCropFrameSize();
    clampAvatarCropOffset();
  });
};

const resetAvatarCrop = (): void => {
  resetAvatarCropTransform();
  nextTick(() => {
    updateAvatarCropFrameSize();
    clampAvatarCropOffset();
  });
};

const startAvatarCropDrag = (event: PointerEvent): void => {
  if (!avatarCropImage.value || avatarPending.value) {
    return;
  }

  const target = event.currentTarget;

  if (!(target instanceof HTMLElement)) {
    return;
  }

  target.setPointerCapture(event.pointerId);
  avatarCropDrag.value = {
    originX: avatarCropOffset.x,
    originY: avatarCropOffset.y,
    pointerId: event.pointerId,
    startX: event.clientX,
    startY: event.clientY,
  };
};

const moveAvatarCropDrag = (event: PointerEvent): void => {
  const drag = avatarCropDrag.value;

  if (!drag || drag.pointerId !== event.pointerId) {
    return;
  }

  avatarCropOffset.x = drag.originX + event.clientX - drag.startX;
  avatarCropOffset.y = drag.originY + event.clientY - drag.startY;
  clampAvatarCropOffset();
};

const stopAvatarCropDrag = (event: PointerEvent): void => {
  const drag = avatarCropDrag.value;

  if (!drag || drag.pointerId !== event.pointerId) {
    return;
  }

  const target = event.currentTarget;

  if (target instanceof HTMLElement && target.hasPointerCapture(event.pointerId)) {
    target.releasePointerCapture(event.pointerId);
  }

  avatarCropDrag.value = null;
};

const selectAvatarFile = async (event: Event): Promise<void> => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0] ?? null;
  input.value = '';

  if (!file) {
    return;
  }

  setAvatarStatus('', null);

  try {
    await openAvatarCrop(file);
  } catch (error) {
    clearAvatarCropSource();
    setAvatarStatus(getAvatarErrorMessage(error, 'Не удалось открыть изображение.'), 'error');
  }
};

const confirmAvatarCrop = async (): Promise<void> => {
  if (avatarPending.value) {
    return;
  }

  avatarPending.value = true;
  avatarCropError.value = '';
  setAvatarStatus('', null);

  try {
    const processedFile = await renderAvatarCropFile();
    setAvatarPreview(processedFile);
    closeAvatarCrop();

    const formData = new FormData();
    formData.set('avatar', processedFile);

    const response = await api.request<ApiDataEnvelope<AuthUserDto>>('/profile/avatar', 'POST', {
      body: formData,
      headers: auth.authHeaders(),
    });

    applyProfile(response.data);
    setAvatarStatus('Аватарка обновлена.', 'success');
  } catch (error) {
    if (avatarCropOpen.value) {
      avatarCropError.value = getAvatarErrorMessage(error, 'Не удалось обработать изображение.');
    } else {
      clearAvatarPreview();
      setAvatarStatus(getAvatarErrorMessage(error, 'Не удалось загрузить аватарку.'), 'error');
    }
  } finally {
    avatarPending.value = false;
  }
};

const deleteAvatar = async (): Promise<void> => {
  avatarPending.value = true;
  setAvatarStatus('', null);

  try {
    await api.request('/profile/avatar', 'DELETE', {
      headers: auth.authHeaders(),
    });

    if (profile.value) {
      applyProfile({
        ...profile.value,
        avatarUrl: null,
      });
    }

    clearAvatarPreview();
    setAvatarStatus('Аватарка удалена.', 'success');
  } catch (error) {
    setAvatarStatus(getAvatarErrorMessage(error, 'Не удалось удалить аватарку.'), 'error');
  } finally {
    avatarPending.value = false;
  }
};

const syncModalBodyState = (): void => {
  if (!import.meta.client) {
    return;
  }

  document.body.classList.toggle('modal-open', avatarCropOpen.value || securityModal.value !== null);
};

const handleDashboardKeydown = (event: KeyboardEvent): void => {
  if (event.key !== 'Escape') {
    return;
  }

  if (securityModal.value !== null) {
    closeSecurityModal();

    return;
  }

  if (avatarCropOpen.value && !avatarPending.value) {
    closeAvatarCrop();
  }
};

const handleAvatarCropResize = (): void => {
  if (!avatarCropOpen.value) {
    return;
  }

  updateAvatarCropFrameSize();
  clampAvatarCropOffset();
};

watch([avatarCropZoom, avatarCropRotation], () => {
  nextTick(() => {
    updateAvatarCropFrameSize();
    clampAvatarCropOffset();
  });
});

watch(avatarCropOpen, async (isOpen) => {
  if (!import.meta.client) {
    return;
  }

  syncModalBodyState();

  if (!isOpen) {
    return;
  }

  await nextTick();
  updateAvatarCropFrameSize();
  clampAvatarCropOffset();
  avatarCropPanelRef.value?.focus();
});

watch(securityModal, async (modal) => {
  syncModalBodyState();

  if (modal === null) {
    return;
  }

  await nextTick();
  securityModalPanelRef.value?.focus();
});

const markNotificationsRead = async (): Promise<void> => {
  await api.request('/dashboard/notifications/mark-all-read', 'POST', {
    headers: auth.authHeaders(),
  });
  notifications.value = notifications.value.map((item) => ({ ...item, read: true }));
};

const terminateSession = async (id: string): Promise<void> => {
  await api.request(`/dashboard/security/sessions/${id}`, 'DELETE', {
    headers: auth.authHeaders(),
  });
  sessions.value = sessions.value.filter((session) => session.id !== id);
};

onMounted(() => {
  window.addEventListener('keydown', handleDashboardKeydown);
  window.addEventListener('resize', handleAvatarCropResize);
});

onBeforeUnmount(() => {
  clearAvatarPreview();
  clearAvatarCropSource();

  if (!import.meta.client) {
    return;
  }

  document.body.classList.remove('modal-open');
  window.removeEventListener('keydown', handleDashboardKeydown);
  window.removeEventListener('resize', handleAvatarCropResize);
});
</script>

<template>
  <div class="cabinet-page cabinet-main">
    <div class="container cabinet-layout">
      <aside class="cabinet-sidebar" aria-label="Разделы личного кабинета">
        <nav class="cabinet-menu">
          <NuxtLink
            v-for="tab in tabs"
            :key="tab.id"
            :to="dashboardTabPath(tab.id)"
            :class="{ 'is-active': activeTab === tab.id }"
          >
            <LayoutAppIcon :name="tab.icon" />
            <span>{{ tab.label }}</span>
            <em v-if="tab.id === 'notifications' && unreadNotificationsCount > 0">
              {{ unreadNotificationsCount }}
            </em>
          </NuxtLink>
        </nav>

        <div class="cabinet-support">
          <strong>Нужна помощь?</strong>
          <span>Команда поддержки подскажет, как оформить обращение и приложить документы.</span>
          <a href="tel:+79102357746">
            <LayoutAppIcon name="phone" />
            8 910 235-77-46
          </a>
          <NuxtLink class="cabinet-support-link" to="/appeal/new">
            Подать обращение
          </NuxtLink>
        </div>
      </aside>

      <section class="cabinet-content">
        <div class="cabinet-screen">
          <header class="cabinet-screen-head">
            <div>
              <span>Личный кабинет</span>
              <h1>{{ displayName }}</h1>
            </div>
            <button class="cabinet-ghost-button" type="button" @click="auth.logout">
              <LayoutAppIcon name="back" />
              Выйти
            </button>
          </header>

          <div v-if="pending" class="content-notice cabinet-panel">
            <strong>Загрузка</strong>
            <p>Получаем данные личного кабинета.</p>
          </div>

          <template v-else>
            <section class="cabinet-hero" aria-label="Профиль пользователя">
              <span class="cabinet-avatar" :class="{ 'has-image': Boolean(avatarDisplayUrl) }">
                <img v-if="avatarDisplayUrl" :src="avatarDisplayUrl" alt="" width="112" height="112">
                <template v-else>
                  {{ initials }}
                </template>
              </span>
              <div class="cabinet-hero-copy">
                <h2>{{ displayName }}</h2>
                <p>
                  Управляйте обращениями, отслеживайте ответы и получайте уведомления по важным изменениям.
                </p>
              </div>
            </section>

            <section class="cabinet-stat-grid" aria-label="Сводка личного кабинета">
              <article class="cabinet-stat-card">
                <span><LayoutAppIcon name="file" /></span>
                <strong>{{ formatRuNumber(totalAppealsCount) }}</strong>
                <p>обращений и черновиков</p>
              </article>
              <article class="cabinet-stat-card cabinet-stat-green">
                <span><LayoutAppIcon name="heart" /></span>
                <strong>{{ formatRuNumber(saved.length) }}</strong>
                <p>сохранённых обращений</p>
              </article>
              <article class="cabinet-stat-card cabinet-stat-orange">
                <span><LayoutAppIcon name="comment" /></span>
                <strong>{{ formatRuNumber(comments.length) }}</strong>
                <p>комментариев</p>
              </article>
              <article class="cabinet-stat-card cabinet-stat-red">
                <span><LayoutAppIcon name="bell" /></span>
                <strong>{{ formatRuNumber(unreadNotificationsCount) }}</strong>
                <p>новых уведомлений</p>
              </article>
              <article class="cabinet-stat-card">
                <span><LayoutAppIcon name="trend" /></span>
                <strong>{{ formatRuNumber(earnedAchievementsCount) }}</strong>
                <p>достижений</p>
              </article>
            </section>

            <template v-if="activeTab === 'profile'">
              <section class="cabinet-avatar-card" aria-label="Аватарка профиля">
                <span
                  class="cabinet-avatar cabinet-avatar-large"
                  :class="{ 'has-image': Boolean(avatarDisplayUrl) }"
                >
                  <img v-if="avatarDisplayUrl" :src="avatarDisplayUrl" alt="" width="112" height="112">
                  <template v-else>
                    {{ initials }}
                  </template>
                </span>

                <div class="cabinet-avatar-copy">
                  <h2>Аватарка профиля</h2>
                  <p>
                    Выберите JPG, PNG или WebP до 10 МБ. Перед сохранением откроется окно кадрирования,
                    а в профиль уйдёт подтверждённый кадр 512×512.
                  </p>

                  <div class="cabinet-avatar-actions">
                    <label
                      class="cabinet-avatar-upload"
                      :class="{ 'is-disabled': avatarPending || avatarCropOpen }"
                    >
                      <LayoutAppIcon name="upload" />
                      {{ avatarPending ? 'Загружаем' : 'Загрузить аватарку' }}
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        :disabled="avatarPending || avatarCropOpen"
                        @change="selectAvatarFile"
                      >
                    </label>

                    <button
                      v-if="avatarDisplayUrl"
                      class="cabinet-avatar-remove"
                      type="button"
                      :disabled="avatarPending"
                      @click="deleteAvatar"
                    >
                      <LayoutAppIcon name="trash" />
                      Удалить
                    </button>
                  </div>

                  <p
                    v-if="avatarStatusMessage"
                    class="cabinet-avatar-status"
                    :class="{
                      'is-error': avatarStatusKind === 'error',
                      'is-success': avatarStatusKind === 'success',
                    }"
                  >
                    {{ avatarStatusMessage }}
                  </p>
                </div>
              </section>

              <form class="cabinet-form-grid" @submit.prevent="updateProfile">
                <label>
                  <span>Имя</span>
                  <input v-model="profileForm.name" type="text" autocomplete="name">
                </label>
                <label>
                  <span>Телефон</span>
                  <input v-model="profileForm.phone" type="tel" autocomplete="tel">
                </label>
                <label class="cabinet-toggle wide">
                  <input v-model="profileForm.notifications" type="checkbox">
                  <span />
                  Получать уведомления по обращениям
                </label>
                <p v-if="statusMessage" class="form-message form-message--success wide">
                  {{ statusMessage }}
                </p>
                <button class="cabinet-primary-link wide" type="submit">
                  <LayoutAppIcon name="check" />
                  Сохранить профиль
                </button>
              </form>
            </template>

            <section v-if="activeTab === 'drafts'" class="cabinet-panel">
              <div class="cabinet-panel-head">
                <h2>Черновики</h2>
                <NuxtLink class="cabinet-ghost-button" to="/appeal/new">
                  <LayoutAppIcon name="edit" />
                  Новый черновик
                </NuxtLink>
              </div>
              <div class="cabinet-card-list">
                <article v-for="draft in drafts" :key="draft.id" class="cabinet-appeal-card cabinet-appeal-card--compact">
                  <span class="cabinet-row-icon"><LayoutAppIcon name="edit" /></span>
                  <div class="cabinet-appeal-card-copy">
                    <div class="cabinet-card-topline">
                      <span>{{ formatAppealCategory(draft.category, 'Без категории') }}</span>
                      <span class="cabinet-pill" :class="appealStatusPillClass(draft.status)">
                        {{ formatAppealStatus(draft.status) }}
                      </span>
                    </div>
                    <h2>{{ draft.title || 'Без названия' }}</h2>
                    <p>{{ draft.description || 'Добавьте описание проблемы, адрес и материалы.' }}</p>
                  </div>
                  <div class="cabinet-card-actions">
                    <NuxtLink :to="appealEditLink(draft)">
                      <LayoutAppIcon name="edit" />
                      Продолжить
                    </NuxtLink>
                  </div>
                </article>
                <p v-if="drafts.length === 0" class="content-notice">
                  Черновиков пока нет.
                </p>
              </div>
            </section>

            <section v-if="activeTab === 'appeals'" class="cabinet-panel">
              <div class="cabinet-panel-head">
                <h2>Мои обращения</h2>
                <NuxtLink class="cabinet-primary-link" to="/appeal/new">
                  <LayoutAppIcon name="file" />
                  Подать обращение
                </NuxtLink>
              </div>
              <div class="cabinet-card-list">
                <article v-for="appeal in appeals" :key="appeal.id" class="cabinet-appeal-card cabinet-appeal-card--compact">
                  <span class="cabinet-row-icon cabinet-ok"><LayoutAppIcon name="file" /></span>
                  <div class="cabinet-appeal-card-copy">
                    <div class="cabinet-card-topline">
                      <span>{{ formatAppealCategory(appeal.category, 'Обращение') }}</span>
                      <span class="cabinet-pill" :class="appealStatusPillClass(appeal.status)">
                        {{ formatAppealStatus(appeal.status) }}
                      </span>
                    </div>
                    <h2>{{ appeal.title || 'Обращение' }}</h2>
                    <p>{{ appeal.location || 'Адрес не указан' }}</p>
                  </div>
                  <div class="cabinet-card-actions">
                    <span class="cabinet-pill gray">{{ appeal.submittedAt ? formatRuDateTime(appeal.submittedAt) : 'Дата уточняется' }}</span>
                    <NuxtLink :to="appealEditLink(appeal)">
                      <LayoutAppIcon name="edit" />
                      Редактировать
                    </NuxtLink>
                  </div>
                </article>
                <p v-if="appeals.length === 0" class="content-notice">
                  Опубликованных обращений пока нет.
                </p>
              </div>
            </section>

            <section v-if="activeTab === 'saved'" class="cabinet-panel">
              <div class="cabinet-panel-head">
                <h2>Сохранённые обращения</h2>
              </div>
              <div class="cabinet-card-list">
                <article v-for="appeal in saved" :key="appeal.id" class="cabinet-appeal-card">
                  <img
                    :src="resolveImageUrl(appeal.imageUrl)"
                    :alt="appeal.title"
                    width="138"
                    height="96"
                    @error="handleImageError"
                  >
                  <div>
                    <div class="cabinet-card-topline">
                      <span>{{ appeal.category }}</span>
                      <span class="cabinet-pill green">{{ appeal.statusLabel }}</span>
                    </div>
                    <h2>{{ appeal.title }}</h2>
                    <p>{{ appeal.city }}, {{ appeal.district }}</p>
                  </div>
                  <NuxtLink :to="`/appeals/${appeal.slug}`">
                    Открыть
                  </NuxtLink>
                </article>
                <p v-if="saved.length === 0" class="content-notice">
                  Сохранённых обращений пока нет.
                </p>
              </div>
            </section>

            <section v-if="activeTab === 'comments'" class="cabinet-panel">
              <div class="cabinet-panel-head">
                <h2>Мои комментарии</h2>
              </div>
              <div class="cabinet-comment-list">
                <article v-for="comment in comments" :key="comment.id">
                  <span class="cabinet-row-icon"><LayoutAppIcon name="comment" /></span>
                  <div>
                    <h2>{{ comment.authorName }}</h2>
                    <p>{{ comment.comment }}</p>
                  </div>
                  <span class="cabinet-pill gray">{{ formatCommentStatus(comment.status) }}</span>
                </article>
                <p v-if="comments.length === 0" class="content-notice">
                  Комментариев пока нет.
                </p>
              </div>
            </section>

            <section v-if="activeTab === 'notifications'" class="cabinet-panel">
              <div class="cabinet-panel-head">
                <h2>Уведомления</h2>
                <button class="cabinet-ghost-button" type="button" @click="markNotificationsRead">
                  <LayoutAppIcon name="check" />
                  Отметить прочитанными
                </button>
              </div>
              <div class="cabinet-notification-list">
                <article v-for="item in notifications" :key="item.id" :class="{ 'is-unread': !item.read }">
                  <span><LayoutAppIcon name="bell" /></span>
                  <div>
                    <h2>{{ item.title }}</h2>
                    <p>{{ item.text }}</p>
                  </div>
                  <span class="cabinet-pill" :class="item.read ? 'gray' : 'blue'">
                    {{ item.read ? 'прочитано' : 'новое' }}
                  </span>
                </article>
                <p v-if="notifications.length === 0" class="content-notice">
                  Уведомлений пока нет.
                </p>
              </div>
            </section>

            <template v-if="activeTab === 'security'">
              <section class="cabinet-security-grid" aria-label="Настройки безопасности">
                <article class="cabinet-security-card">
                  <span class="cabinet-security-icon">
                    <LayoutAppIcon name="lock" />
                  </span>
                  <div class="cabinet-security-copy">
                    <div class="cabinet-card-topline">
                      <span>Пароль</span>
                      <span class="cabinet-pill blue">основная защита</span>
                    </div>
                    <h2>Смена пароля</h2>
                    <p>Обновите пароль и автоматически завершите остальные активные сеансы.</p>
                    <p v-if="passwordSuccessMessage" class="form-message form-message--success">
                      {{ passwordSuccessMessage }}
                    </p>
                    <p v-if="passwordErrorMessage" class="form-message form-message--error">
                      {{ passwordErrorMessage }}
                    </p>
                  </div>
                  <button class="cabinet-primary-link" type="button" @click="openPasswordModal">
                    <LayoutAppIcon name="check" />
                    Изменить
                  </button>
                </article>

                <article class="cabinet-security-card">
                  <span class="cabinet-security-icon is-shielded">
                    <LayoutAppIcon name="shield" />
                  </span>
                  <div class="cabinet-security-copy">
                    <div class="cabinet-card-topline">
                      <span>Email-2FA</span>
                      <span class="cabinet-pill" :class="profile?.emailTwoFactorEnabled ? 'green' : 'gray'">
                        {{ profile?.emailTwoFactorEnabled ? 'включена' : 'выключена' }}
                      </span>
                    </div>
                    <h2>Двухфакторная проверка</h2>
                    <p>
                      При входе после пароля потребуется одноразовый код, отправленный на ваш email.
                    </p>
                    <p v-if="emailTwoFactorSuccessMessage" class="form-message form-message--success">
                      {{ emailTwoFactorSuccessMessage }}
                    </p>
                    <p v-if="emailTwoFactorErrorMessage" class="form-message form-message--error">
                      {{ emailTwoFactorErrorMessage }}
                    </p>
                  </div>
                  <button
                    v-if="!profile?.emailTwoFactorEnabled"
                    class="cabinet-primary-link"
                    type="button"
                    @click="openEmailTwoFactorModal"
                  >
                    <LayoutAppIcon name="shield" />
                    Включить
                  </button>
                  <button
                    v-else
                    class="cabinet-ghost-button"
                    type="button"
                    @click="openDisableEmailTwoFactorModal"
                  >
                    Отключить
                  </button>
                </article>
              </section>

              <section class="cabinet-panel cabinet-session-panel">
                <div class="cabinet-panel-head">
                  <h2>Сессии</h2>
                </div>
                <article v-for="session in sessions" :key="session.id">
                  <div>
                    <h2>{{ session.name }}</h2>
                    <p>Создана: {{ formatOptionalDate(session.createdAt) }}</p>
                    <p>Последняя активность: {{ formatOptionalDate(session.lastUsedAt) }}</p>
                  </div>
                  <button class="cabinet-ghost-button" type="button" @click="terminateSession(session.id)">
                    Завершить
                  </button>
                </article>
                <p v-if="sessions.length === 0" class="content-notice">
                  Активных сессий не найдено.
                </p>
              </section>
            </template>

            <section v-if="activeTab === 'achievements'" class="cabinet-badge-grid">
              <article v-for="item in achievements" :key="item.id">
                <span><LayoutAppIcon :name="item.earned ? 'trend' : 'lock'" /></span>
                <h2>{{ item.title }}</h2>
                <p>{{ item.description }}</p>
                <span class="cabinet-pill" :class="item.earned ? 'green' : 'gray'">
                  {{ item.earned ? 'получено' : 'в процессе' }}
                </span>
              </article>
              <p v-if="achievements.length === 0" class="content-notice">
                Достижения появятся после активности в проекте.
              </p>
            </section>
          </template>
        </div>
      </section>
    </div>

    <Teleport to="body">
      <div v-if="avatarCropOpen" class="modal avatar-crop-modal">
        <button
          class="modal-backdrop"
          type="button"
          aria-label="Закрыть окно кадрирования"
          @click="cancelAvatarCrop"
        />

        <section
          ref="avatarCropPanelRef"
          class="modal-panel avatar-crop-panel"
          role="dialog"
          aria-modal="true"
          aria-labelledby="avatar-crop-title"
          aria-describedby="avatar-crop-text"
          tabindex="-1"
        >
          <button
            class="modal-close"
            type="button"
            aria-label="Закрыть окно кадрирования"
            :disabled="avatarPending"
            @click="cancelAvatarCrop"
          >
            <span aria-hidden="true">×</span>
          </button>

          <span class="avatar-crop-eyebrow">
            <LayoutAppIcon name="camera" />
            Аватарка профиля
          </span>
          <h2 id="avatar-crop-title">Кадрирование аватарки</h2>
          <p id="avatar-crop-text">
            Переместите изображение внутри круга, настройте масштаб и подтвердите кадр перед загрузкой.
          </p>

          <div class="avatar-crop-workspace">
            <div
              ref="avatarCropFrameRef"
              class="avatar-crop-frame"
              role="img"
              aria-label="Предпросмотр будущей аватарки"
              @pointerdown="startAvatarCropDrag"
              @pointermove="moveAvatarCropDrag"
              @pointerup="stopAvatarCropDrag"
              @pointercancel="stopAvatarCropDrag"
            >
              <div
                v-if="avatarCropImageUrl"
                class="avatar-crop-image-stage"
                :style="avatarCropStageStyle"
              >
                <img
                  :src="avatarCropImageUrl"
                  alt=""
                  draggable="false"
                  :style="avatarCropImageStyle"
                >
              </div>
              <span class="avatar-crop-frame-ring" aria-hidden="true" />
            </div>

            <div class="avatar-crop-controls">
              <label class="avatar-crop-slider">
                <span>Масштаб</span>
                <input
                  v-model.number="avatarCropZoom"
                  type="range"
                  :min="avatarCropZoomMin"
                  :max="avatarCropZoomMax"
                  :step="avatarCropZoomStep"
                  :disabled="avatarPending"
                >
              </label>

              <div class="avatar-crop-toolrow">
                <button type="button" :disabled="avatarPending" @click="rotateAvatarCrop">
                  <LayoutAppIcon name="refresh" />
                  Повернуть
                </button>
                <button type="button" :disabled="avatarPending" @click="resetAvatarCrop">
                  <LayoutAppIcon name="camera" />
                  По центру
                </button>
              </div>
            </div>
          </div>

          <p v-if="avatarCropError" class="avatar-crop-error" aria-live="polite">
            {{ avatarCropError }}
          </p>

          <div class="avatar-crop-actions">
            <button
              class="cabinet-ghost-button avatar-crop-secondary"
              type="button"
              :disabled="avatarPending"
              @click="cancelAvatarCrop"
            >
              Отмена
            </button>
            <button
              class="cabinet-primary-link avatar-crop-primary"
              type="button"
              :disabled="avatarPending"
              @click="confirmAvatarCrop"
            >
              <LayoutAppIcon name="check" />
              {{ avatarPending ? 'Загружаем' : 'Сохранить аватарку' }}
            </button>
          </div>
        </section>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="securityModal" class="modal security-modal">
        <button
          class="modal-backdrop"
          type="button"
          aria-label="Закрыть настройки безопасности"
          @click="closeSecurityModal"
        />

        <section
          ref="securityModalPanelRef"
          class="modal-panel security-modal-panel"
          role="dialog"
          aria-modal="true"
          aria-labelledby="security-modal-title"
          tabindex="-1"
        >
          <button
            class="modal-close"
            type="button"
            aria-label="Закрыть настройки безопасности"
            :disabled="securityModalPending"
            @click="closeSecurityModal"
          >
            <span aria-hidden="true">×</span>
          </button>

          <template v-if="securityModal === 'password'">
            <span class="security-modal-eyebrow">
              <LayoutAppIcon name="lock" />
              Безопасность аккаунта
            </span>
            <h2 id="security-modal-title">Изменить пароль</h2>
            <p>
              После смены пароля текущий сеанс останется активным, остальные токены доступа будут завершены.
            </p>

            <form class="cabinet-form-grid security-modal-form" @submit.prevent="changePassword">
              <label class="wide">
                <span>Текущий пароль</span>
                <input
                  v-model="passwordForm.current_password"
                  type="password"
                  autocomplete="current-password"
                  required
                >
              </label>
              <label>
                <span>Новый пароль</span>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  autocomplete="new-password"
                  minlength="8"
                  required
                >
              </label>
              <label>
                <span>Повторите новый пароль</span>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  autocomplete="new-password"
                  minlength="8"
                  required
                >
              </label>
              <p v-if="passwordErrorMessage" class="form-message form-message--error wide">
                {{ passwordErrorMessage }}
              </p>
              <div class="security-modal-actions wide">
                <button class="cabinet-ghost-button" type="button" :disabled="passwordPending" @click="closeSecurityModal">
                  Отмена
                </button>
                <button class="cabinet-primary-link" type="submit" :disabled="passwordPending">
                  <LayoutAppIcon name="check" />
                  {{ passwordPending ? 'Сохраняем' : 'Сохранить пароль' }}
                </button>
              </div>
            </form>
          </template>

          <template v-else-if="securityModal === 'email-two-factor'">
            <span class="security-modal-eyebrow">
              <LayoutAppIcon name="shield" />
              Второй фактор
            </span>
            <h2 id="security-modal-title">Включить email-2FA</h2>
            <p>
              Сначала подтвердите текущий пароль. Затем введите код, который придёт на ваш email.
            </p>

            <form
              class="cabinet-form-grid security-modal-form"
              @submit.prevent="emailTwoFactorForm.challenge_id ? enableEmailTwoFactor() : sendEmailTwoFactorCode()"
            >
              <label class="wide">
                <span>Текущий пароль</span>
                <input
                  v-model="emailTwoFactorForm.current_password"
                  type="password"
                  autocomplete="current-password"
                  :disabled="Boolean(emailTwoFactorForm.challenge_id)"
                  required
                >
              </label>
              <label class="wide">
                <span>Код из письма</span>
                <input
                  v-model="emailTwoFactorForm.code"
                  type="text"
                  inputmode="numeric"
                  autocomplete="one-time-code"
                  maxlength="6"
                  placeholder="6 цифр"
                  :required="Boolean(emailTwoFactorForm.challenge_id)"
                  :disabled="!emailTwoFactorForm.challenge_id"
                >
              </label>
              <p v-if="emailTwoFactorMaskedTarget" class="content-notice wide">
                Код отправлен на {{ emailTwoFactorMaskedTarget }}.
              </p>
              <p v-if="emailTwoFactorErrorMessage" class="form-message form-message--error wide">
                {{ emailTwoFactorErrorMessage }}
              </p>
              <div class="security-modal-actions wide">
                <button class="cabinet-ghost-button" type="button" :disabled="emailTwoFactorPending" @click="closeSecurityModal">
                  Отмена
                </button>
                <button class="cabinet-primary-link" type="submit" :disabled="emailTwoFactorPending">
                  <LayoutAppIcon name="shield" />
                  {{ emailTwoFactorPending ? 'Проверяем' : (emailTwoFactorForm.challenge_id ? 'Включить 2FA' : 'Отправить код') }}
                </button>
              </div>
            </form>
          </template>

          <template v-else>
            <span class="security-modal-eyebrow is-danger">
              <LayoutAppIcon name="shield" />
              Снижение защиты
            </span>
            <h2 id="security-modal-title">Отключить email-2FA</h2>
            <p>
              После отключения для входа снова будет достаточно только логина и пароля.
            </p>

            <form class="cabinet-form-grid security-modal-form" @submit.prevent="disableEmailTwoFactor">
              <label class="wide">
                <span>Текущий пароль</span>
                <input
                  v-model="emailTwoFactorForm.disable_password"
                  type="password"
                  autocomplete="current-password"
                  required
                >
              </label>
              <p v-if="emailTwoFactorErrorMessage" class="form-message form-message--error wide">
                {{ emailTwoFactorErrorMessage }}
              </p>
              <div class="security-modal-actions wide">
                <button class="cabinet-ghost-button" type="button" :disabled="emailTwoFactorPending" @click="closeSecurityModal">
                  Отмена
                </button>
                <button class="cabinet-danger-button" type="submit" :disabled="emailTwoFactorPending">
                  {{ emailTwoFactorPending ? 'Отключаем' : 'Отключить 2FA' }}
                </button>
              </div>
            </form>
          </template>
        </section>
      </div>
    </Teleport>
  </div>
</template>
