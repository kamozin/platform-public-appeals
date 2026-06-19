<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';
import type { AppealDraftDto } from '~/types/api/private';
import type { CategoriesPageDto } from '~/types/api/public-content';
import type { AppIconName } from '~/components/layout/AppIcon.vue';

type DraftAttachmentDto = AppealDraftDto['attachments'][number];
type SubmissionMode = 'anonymous' | 'contacts';
type SubmissionType = 'complaint' | 'appeal' | 'proposal';
type Urgency = 'normal' | 'urgent';
type ContactVisibility = 'hidden';

type CategoryOption = {
  slug: string;
  label: string;
  description: string;
  icon: AppIconName;
};

type StepItem = {
  number: number;
  label: string;
};

type AppealFormState = {
  category: string;
  submissionMode: SubmissionMode;
  submissionType: SubmissionType;
  title: string;
  description: string;
  urgency: Urgency;
  location: string;
  landmark: string;
  contactVisibility: ContactVisibility;
  contactName: string;
  contactEmail: string;
  contactPhone: string;
  createCabinet: boolean;
  captchaChecked: boolean;
  consent: boolean;
};

const submissionTypes: readonly SubmissionType[] = ['complaint', 'appeal', 'proposal'];
const urgencyTypes: readonly Urgency[] = ['normal', 'urgent'];

const categoryIconNames = new Set<AppIconName>([
  'book',
  'building',
  'education',
  'file',
  'flag',
  'hand-heart',
  'heart',
  'home',
  'medical',
  'more',
  'parking',
  'phone',
  'road',
  'scale',
  'shield',
  'shop',
  'star',
  'tree',
  'users',
  'volume',
  'wallet',
]);

const resolveCategoryIcon = (icon: string): AppIconName => {
  const iconName = icon as AppIconName;

  if (categoryIconNames.has(iconName)) {
    return iconName;
  }

  return 'file';
};

const steps: StepItem[] = [
  { number: 1, label: 'Тема' },
  { number: 2, label: 'Проблема' },
  { number: 3, label: 'Доказательства' },
  { number: 4, label: 'Отправка' },
];

definePageMeta({
  layout: 'auth',
});

useSeoMeta({
  title: 'Подача обращения или жалобы',
  description: 'Форма подачи обращения или жалобы в НКО «Рука добра».',
});
useNoindexSeo();

const route = useRoute();
const api = useApi();
const auth = useAuth();

const { data: categoriesResponse, error: categoriesError } = await useAsyncData('appeal-form-categories', () => {
  return api.get<ApiDataEnvelope<CategoriesPageDto>>('/categories');
});

if (categoriesError.value || !categoriesResponse.value) {
  throw createError({
    statusCode: 500,
    statusMessage: 'Appeal categories are unavailable',
  });
}

const categoryOptions: CategoryOption[] = categoriesResponse.value.data.groups.flatMap((group) => {
  return group.categories.map((category) => ({
    slug: category.slug,
    label: category.title,
    description: category.description,
    icon: resolveCategoryIcon(category.icon),
  }));
});

if (categoryOptions.length === 0) {
  throw createError({
    statusCode: 500,
    statusMessage: 'Appeal categories are unavailable',
  });
}

const fallbackCategory = categoryOptions[0] as CategoryOption;

const guestDraftToken = useCookie<string | null>('rd_appeal_draft_token', {
  default: () => null,
  maxAge: 60 * 60 * 24 * 30,
  sameSite: 'lax',
});

const queryCategory = Array.isArray(route.query.category)
  ? route.query.category[0]
  : route.query.category;
const queryDraft = Array.isArray(route.query.draft)
  ? route.query.draft[0]
  : route.query.draft;

const initialCategory = categoryOptions.some((category) => category.slug === queryCategory)
  ? queryCategory
  : fallbackCategory.slug;
const initialDraftId = typeof queryDraft === 'string' && queryDraft.trim() !== ''
  ? queryDraft.trim()
  : null;

const form = reactive<AppealFormState>({
  category: initialCategory ?? fallbackCategory.slug,
  submissionMode: 'anonymous',
  submissionType: 'complaint',
  title: '',
  description: '',
  urgency: 'normal',
  location: '',
  landmark: '',
  contactVisibility: 'hidden',
  contactName: '',
  contactEmail: '',
  contactPhone: '',
  createCabinet: false,
  captchaChecked: true,
  consent: true,
});

const currentStep = ref(1);
const maxStepReached = ref(1);
const draft = ref<AppealDraftDto | null>(null);
const submittedDraft = ref<AppealDraftDto | null>(null);
const loadingDraft = ref(false);
const saving = ref(false);
const uploading = ref(false);
const submitting = ref(false);
const saveMessage = ref('');
const errorMessage = ref('');
const fileInput = ref<HTMLInputElement | null>(null);
const fileAccept = ref('image/jpeg,image/png,image/webp,video/mp4,video/quicktime,application/pdf');

const selectedCategory = computed<CategoryOption>(() => {
  return categoryOptions.find((category) => category.slug === form.category) ?? fallbackCategory;
});

const attachments = computed(() => draft.value?.attachments ?? []);
const isModerationEditMode = computed(() => draft.value?.status === 'pending_moderation');
const canSubmit = computed(() => form.consent && !submitting.value && !saving.value && !loadingDraft.value);
const isAnonymousMode = computed(() => form.submissionMode === 'anonymous');
const hasRequiredEvidence = computed(() => attachments.value.length > 0);
const hasRequiredContact = computed(() => {
  return form.contactName.trim() !== '' && (form.contactEmail.trim() !== '' || form.contactPhone.trim() !== '');
});
const contactModeLabel = computed(() => {
  if (form.submissionMode === 'contacts') {
    return 'С контактными данными';
  }

  return 'Инкогнито';
});
const saveActionLabel = computed(() => {
  if (isModerationEditMode.value) {
    return 'Сохранить изменения';
  }

  return 'Сохранить черновик';
});
const primarySubmitLabel = computed(() => {
  if (isModerationEditMode.value) {
    return submitting.value || saving.value ? 'Сохраняем...' : 'Сохранить изменения';
  }

  return submitting.value ? 'Отправляем...' : 'Отправить обращение / жалобу';
});
const primarySubmitHint = computed(() => {
  if (isModerationEditMode.value) {
    return 'Обращение останется на модерации';
  }

  return 'Обращение уйдёт на проверку';
});

const locationLabel = computed(() => {
  if (form.location.trim() === '') {
    return 'Место не указано';
  }

  if (form.landmark.trim() === '') {
    return form.location.trim();
  }

  return `${form.location.trim()}, ориентир: ${form.landmark.trim()}`;
});

const isSubmissionType = (value: string | null): value is SubmissionType => {
  return submissionTypes.includes(value as SubmissionType);
};

const isUrgency = (value: string | null): value is Urgency => {
  return urgencyTypes.includes(value as Urgency);
};

const splitDraftLocation = (value: string | null): { location: string; landmark: string } => {
  if (!value) {
    return {
      location: '',
      landmark: '',
    };
  }

  const marker = ', ориентир: ';
  const markerIndex = value.indexOf(marker);

  if (markerIndex === -1) {
    return {
      location: value,
      landmark: '',
    };
  }

  return {
    location: value.slice(0, markerIndex),
    landmark: value.slice(markerIndex + marker.length),
  };
};

const formatAttachmentKind = (kind: string): string => {
  return ({
    image: 'Фото',
    video: 'Видео',
    document: 'Документ',
  } as Record<string, string>)[kind] ?? 'Файл';
};

watch(
  () => form.submissionMode,
  (mode) => {
    form.contactVisibility = 'hidden';

    if (mode === 'anonymous') {
      form.createCabinet = false;

      return;
    }

    form.createCabinet = true;
  },
);

const draftHeaders = (): HeadersInit => {
  const headers = new Headers(auth.authHeaders());

  if (guestDraftToken.value) {
    headers.set('X-Appeal-Draft-Token', guestDraftToken.value);
  }

  return headers;
};

const draftPayload = () => {
  return {
    category: form.category,
    submission_type: form.submissionType,
    title: form.title.trim() || null,
    description: form.description.trim() || null,
    urgency: form.urgency,
    location: locationLabel.value === 'Место не указано' ? null : locationLabel.value,
    contact_visibility: 'hidden' as ContactVisibility,
    contact_name: isAnonymousMode.value ? null : form.contactName.trim() || null,
    contact_email: isAnonymousMode.value ? null : form.contactEmail.trim() || null,
    contact_phone: isAnonymousMode.value ? null : form.contactPhone.trim() || null,
  };
};

const applyDraft = (payload: AppealDraftDto): void => {
  if (payload.guestToken) {
    guestDraftToken.value = payload.guestToken;
  }

  draft.value = {
    ...payload,
    guestToken: payload.guestToken ?? draft.value?.guestToken ?? null,
  };
};

const applyDraftToForm = (payload: AppealDraftDto): void => {
  const locationParts = splitDraftLocation(payload.location);
  const hasContacts = Boolean(payload.contactName || payload.contactEmail || payload.contactPhone);

  form.category = categoryOptions.some((category) => category.slug === payload.category)
    ? payload.category ?? fallbackCategory.slug
    : fallbackCategory.slug;
  form.submissionMode = hasContacts ? 'contacts' : 'anonymous';
  form.submissionType = isSubmissionType(payload.submissionType) ? payload.submissionType : 'complaint';
  form.title = payload.title ?? '';
  form.description = payload.description ?? '';
  form.urgency = isUrgency(payload.urgency) ? payload.urgency : 'normal';
  form.location = locationParts.location;
  form.landmark = locationParts.landmark;
  form.contactVisibility = 'hidden';
  form.contactName = payload.contactName ?? '';
  form.contactEmail = payload.contactEmail ?? '';
  form.contactPhone = payload.contactPhone ?? '';
  form.createCabinet = hasContacts;
  form.captchaChecked = true;
  form.consent = true;
};

const loadExistingDraft = async (id: string): Promise<void> => {
  loadingDraft.value = true;
  errorMessage.value = '';
  saveMessage.value = '';

  try {
    const response = await api.get<ApiDataEnvelope<AppealDraftDto>>(`/appeal-drafts/${id}`, {
      headers: draftHeaders(),
    });

    applyDraft(response.data);
    applyDraftToForm(response.data);
    maxStepReached.value = steps.length;
    currentStep.value = response.data.status === 'pending_moderation' ? 4 : 1;
    saveMessage.value = response.data.status === 'pending_moderation'
      ? 'Открыто обращение на модерации. Можно внести уточнения.'
      : 'Черновик открыт для продолжения.';
  } catch {
    errorMessage.value = 'Не удалось открыть обращение для редактирования.';
  } finally {
    loadingDraft.value = false;
  }
};

onMounted(async () => {
  const user = await auth.fetchMe();

  if (user) {
    form.submissionMode = 'contacts';
    form.contactVisibility = 'hidden';
    form.contactName = form.contactName || user.name;
    form.contactEmail = form.contactEmail || user.email;
    form.contactPhone = form.contactPhone || user.phone || '';
  }

  if (initialDraftId) {
    await loadExistingDraft(initialDraftId);
  }
});

const saveDraft = async (message?: string): Promise<boolean> => {
  saving.value = true;
  errorMessage.value = '';
  saveMessage.value = '';

  try {
    const path = draft.value ? `/appeal-drafts/${draft.value.id}` : '/appeal-drafts';
    const method = draft.value ? 'PATCH' : 'POST';
    const response = await api.request<ApiDataEnvelope<AppealDraftDto>>(path, method, {
      body: draftPayload(),
      headers: draftHeaders(),
    });

    applyDraft(response.data);
    saveMessage.value = message ?? (isModerationEditMode.value ? 'Изменения сохранены.' : 'Черновик сохранён.');

    return true;
  } catch {
    errorMessage.value = 'Не удалось сохранить черновик. Проверьте поля формы и попробуйте ещё раз.';

    return false;
  } finally {
    saving.value = false;
  }
};

const validateStep = (step: number): boolean => {
  errorMessage.value = '';

  if (step === 1 && form.category.trim() === '') {
    errorMessage.value = 'Выберите категорию обращения.';

    return false;
  }

  if (step === 2 && (form.title.trim() === '' || form.description.trim() === '')) {
    errorMessage.value = 'Заполните краткое название и подробное описание проблемы.';

    return false;
  }

  if (step === 2 && form.location.trim() === '') {
    errorMessage.value = 'Укажите адрес, место происшествия или понятный ориентир для проверки.';

    return false;
  }

  if (step === 3 && isAnonymousMode.value && !hasRequiredEvidence.value) {
    errorMessage.value = 'Для подачи инкогнито прикрепите хотя бы одно фото, видео или документ.';

    return false;
  }

  if (step === 4 && !isAnonymousMode.value && !hasRequiredContact.value) {
    errorMessage.value = 'Укажите ФИО и хотя бы один способ связи: телефон или электронную почту.';

    return false;
  }

  if (step === 4 && !form.captchaChecked) {
    errorMessage.value = 'Подтвердите проверку от спама.';

    return false;
  }

  return true;
};

const goToStep = async (step: number): Promise<void> => {
  if (step < 1 || step > steps.length) {
    return;
  }

  if (step > maxStepReached.value) {
    return;
  }

  currentStep.value = step;
};

const nextStep = async (): Promise<void> => {
  if (!validateStep(currentStep.value)) {
    return;
  }

  const saved = await saveDraft();

  if (!saved) {
    return;
  }

  currentStep.value = Math.min(currentStep.value + 1, steps.length);
  maxStepReached.value = Math.max(maxStepReached.value, currentStep.value);
};

const previousStep = (): void => {
  currentStep.value = Math.max(currentStep.value - 1, 1);
};

const chooseFiles = (accept: string): void => {
  fileAccept.value = accept;
  fileInput.value?.click();
};

const uploadFile = async (file: File): Promise<void> => {
  const saved = await saveDraft('Черновик создан для загрузки файлов.');

  if (!saved || !draft.value) {
    return;
  }

  const body = new FormData();
  body.append('file', file);

  const response = await api.request<ApiDataEnvelope<DraftAttachmentDto>>(
    `/appeal-drafts/${draft.value.id}/attachments`,
    'POST',
    {
      body,
      headers: draftHeaders(),
    },
  );

  draft.value = {
    ...draft.value,
    attachments: [response.data, ...draft.value.attachments],
  };
};

const handleFiles = async (event: Event): Promise<void> => {
  const input = event.target as HTMLInputElement;
  const files = Array.from(input.files ?? []);

  if (files.length === 0) {
    return;
  }

  uploading.value = true;
  errorMessage.value = '';

  try {
    for (const file of files) {
      await uploadFile(file);
    }

    saveMessage.value = 'Файлы загружены.';
  } catch {
    errorMessage.value = 'Не удалось загрузить один из файлов. Проверьте формат и размер.';
  } finally {
    uploading.value = false;
    input.value = '';
  }
};

const deleteAttachment = async (attachment: DraftAttachmentDto): Promise<void> => {
  if (!draft.value) {
    return;
  }

  try {
    await api.request(`/appeal-drafts/${draft.value.id}/attachments/${attachment.id}`, 'DELETE', {
      headers: draftHeaders(),
    });
    draft.value = {
      ...draft.value,
      attachments: draft.value.attachments.filter((item) => item.id !== attachment.id),
    };
  } catch {
    errorMessage.value = 'Не удалось удалить файл.';
  }
};

const submitDraft = async (): Promise<void> => {
  if (!validateStep(2) || !validateStep(3) || !validateStep(4)) {
    return;
  }

  if (!form.consent) {
    errorMessage.value = 'Подтвердите достоверность данных и согласие на обработку.';

    return;
  }

  const saved = await saveDraft(
    isModerationEditMode.value
      ? 'Изменения сохранены. Обращение остаётся на модерации.'
      : 'Черновик сохранён перед отправкой.',
  );

  if (!saved || !draft.value) {
    return;
  }

  if (isModerationEditMode.value) {
    return;
  }

  submitting.value = true;
  errorMessage.value = '';

  try {
    const response = await api.request<ApiDataEnvelope<AppealDraftDto>>(
      `/appeal-drafts/${draft.value.id}/submit`,
      'POST',
      {
        body: {
          captcha_token: 'dev-captcha',
        },
        headers: draftHeaders(),
      },
    );

    submittedDraft.value = response.data;
    draft.value = response.data;
  } catch {
    errorMessage.value = 'Не удалось отправить обращение. Попробуйте ещё раз.';
  } finally {
    submitting.value = false;
  }
};

const formatFileSize = (size: number): string => {
  if (size >= 1024 * 1024) {
    return `${(size / 1024 / 1024).toFixed(1)} МБ`;
  }

  return `${Math.max(1, Math.round(size / 1024))} КБ`;
};
</script>

<template>
  <main id="main-content" class="appeal-page appeal-main" tabindex="-1">
    <section v-if="!submittedDraft" class="container appeal-frame" aria-label="Подача обращения или жалобы">
      <ol class="appeal-progress" aria-label="Шаги подачи обращения">
        <li
          v-for="step in steps"
          :key="step.number"
          :class="{ 'is-active': currentStep === step.number, 'is-done': currentStep > step.number }"
        >
          <button type="button" :disabled="step.number > maxStepReached" @click="goToStep(step.number)">
            <span>{{ step.number }}</span>
            <strong>{{ step.label }}</strong>
          </button>
        </li>
      </ol>

      <div class="appeal-workspace">
        <div class="appeal-form-column">
          <p v-if="loadingDraft" class="form-message form-message--success">
            Открываем обращение для редактирования.
          </p>

          <section v-show="currentStep === 1" class="appeal-panel" aria-labelledby="step-1-title">
            <h1 id="step-1-title">
              {{ isModerationEditMode ? 'Редактирование обращения — Шаг 1. Выберите тему' : 'Подача обращения / жалобы — Шаг 1. Выберите тему' }}
            </h1>
            <p class="appeal-lead">
              Выберите категорию, которая наиболее точно отражает суть проблемы. Это поможет быстрее направить обращение нужному специалисту.
            </p>

            <h2>Способ подачи обращения <span class="hint-dot"><LayoutAppIcon name="info" /></span></h2>
            <div class="choice-grid choice-grid-2">
              <button
                class="choice-card"
                :class="{ 'is-selected': form.submissionMode === 'anonymous' }"
                type="button"
                @click="form.submissionMode = 'anonymous'"
              >
                <LayoutAppIcon name="user" />
                <span><strong>Инкогнито</strong><small>Без контактов, но с обязательными фото, видео или документами</small></span>
              </button>
              <button
                class="choice-card"
                :class="{ 'is-selected': form.submissionMode === 'contacts' }"
                type="button"
                @click="form.submissionMode = 'contacts'"
              >
                <LayoutAppIcon name="phone" />
                <span><strong>С контактными данными</strong><small>Свяжемся, уточним детали и при необходимости выедем на место</small></span>
              </button>
            </div>

            <div class="appeal-note">
              <LayoutAppIcon name="info" />
              <p>Каждое обращение будет проверено. При подаче инкогнито личный кабинет не предлагается, поэтому нужны материалы для первичной проверки.</p>
            </div>

            <h2>Выберите категорию обращения</h2>
            <div class="category-choice-grid">
              <button
                v-for="category in categoryOptions"
                :key="category.slug"
                class="category-choice"
                :class="{ 'is-selected': form.category === category.slug }"
                type="button"
                @click="form.category = category.slug"
              >
                <LayoutAppIcon :name="category.icon" />
                <span>{{ category.label }}</span>
              </button>
            </div>

            <div class="wizard-actions">
              <button class="appeal-button appeal-button-primary" type="button" :disabled="saving" @click="nextStep">
                Далее
                <LayoutAppIcon name="arrow" />
              </button>
              <button class="appeal-button appeal-button-secondary" type="button" :disabled="saving" @click="saveDraft()">
                <LayoutAppIcon name="file" />
                {{ saveActionLabel }}
              </button>
            </div>
          </section>

          <section v-show="currentStep === 2" class="appeal-panel" aria-labelledby="step-2-title">
            <h1 id="step-2-title">
              {{ isModerationEditMode ? 'Редактирование обращения — Шаг 2. Опишите проблему' : 'Подача обращения / жалобы — Шаг 2. Опишите проблему' }}
            </h1>
            <p class="appeal-lead">
              Опишите суть проблемы подробно: что произошло, где, когда и какие последствия уже видны. Если место известно, укажите адрес или ориентир.
            </p>

            <div class="selected-summary">
              <LayoutAppIcon :name="selectedCategory.icon" />
              <span>Выбранная категория:</span>
              <strong>{{ selectedCategory.label }}</strong>
              <button type="button" @click="goToStep(1)">
                Изменить категорию
                <LayoutAppIcon name="edit" />
              </button>
            </div>

            <div class="form-field">
              <label for="appeal-title">Краткое название обращения <sup>*</sup></label>
              <input
                id="appeal-title"
                v-model="form.title"
                maxlength="180"
                placeholder="Например: Протекает крыша в подъезде"
              >
              <div class="field-meta"><span>Максимум 180 символов</span><span>{{ form.title.length }}/180</span></div>
            </div>

            <div class="form-field">
              <label for="appeal-description">Опишите проблему подробно <sup>*</sup></label>
              <textarea
                id="appeal-description"
                v-model="form.description"
                rows="6"
                maxlength="5000"
                placeholder="Укажите даты, адреса, обстоятельства, последствия и всех, кого затрагивает проблема."
              />
              <div class="field-meta"><span>Максимум 5000 символов</span><span>{{ form.description.length }}/5000</span></div>
            </div>

            <h2>Срочность обращения</h2>
            <div class="choice-grid choice-grid-4">
              <button
                class="choice-card compact"
                :class="{ 'is-selected': form.urgency === 'normal' }"
                type="button"
                @click="form.urgency = 'normal'"
              >
                <LayoutAppIcon name="check" />
                <span><strong>Обычная</strong><small>Нет прямой угрозы</small></span>
              </button>
              <button
                class="choice-card compact danger"
                :class="{ 'is-selected': form.urgency === 'urgent' }"
                type="button"
                @click="form.urgency = 'urgent'"
              >
                <LayoutAppIcon name="shield" />
                <span><strong>Срочно</strong><small>Требует быстрого решения</small></span>
              </button>
            </div>

            <h2>Место происшествия</h2>
            <div class="form-field">
              <label for="address-search">Адрес или место происшествия</label>
              <span class="search-input">
                <LayoutAppIcon name="pin" />
                <input id="address-search" v-model="form.location" placeholder="Город, улица, дом или ориентир">
              </span>
            </div>

            <AppealYandexLocationMap v-model="form.location" />

            <div class="form-field">
              <label for="landmark">Ориентир (необязательно)</label>
              <input id="landmark" v-model="form.landmark" placeholder="Например: рядом с остановкой, магазином, парком">
            </div>

            <div class="privacy-callout">
              <LayoutAppIcon name="shield" />
              <p><strong>Точное место помогает быстрее добиться результата.</strong> Даже при подаче инкогнито адрес нужен для проверки фактов.</p>
            </div>

            <div class="wizard-actions three">
              <button class="appeal-button appeal-button-secondary" type="button" @click="previousStep">
                <LayoutAppIcon name="back" />
                Назад
              </button>
              <button class="appeal-button appeal-button-secondary" type="button" :disabled="saving" @click="saveDraft()">
                <LayoutAppIcon name="file" />
                {{ saveActionLabel }}
              </button>
              <button class="appeal-button appeal-button-primary" type="button" :disabled="saving" @click="nextStep">
                Далее
                <LayoutAppIcon name="arrow" />
              </button>
            </div>
          </section>

          <section v-show="currentStep === 3" class="appeal-panel" aria-labelledby="step-3-title">
            <h1 id="step-3-title">
              {{ isModerationEditMode ? 'Редактирование обращения — Шаг 3. Приложите доказательства' : 'Подача обращения / жалобы — Шаг 3. Приложите доказательства' }}
            </h1>
            <p class="appeal-lead">
              Приложите материалы, подтверждающие проблему. Для подачи инкогнито нужен минимум один файл; при подаче с контактами материалы необязательны, но ускоряют проверку.
            </p>

            <input
              ref="fileInput"
              class="visually-hidden"
              type="file"
              :accept="fileAccept"
              multiple
              @change="handleFiles"
            >

            <h2>Загрузите файлы</h2>
            <div class="upload-layout">
              <button class="dropzone" type="button" :disabled="uploading" @click="chooseFiles(fileAccept)">
                <LayoutAppIcon name="upload" />
                <strong>{{ uploading ? 'Загружаем файлы' : 'Перетащите файлы сюда' }}</strong>
                <span>или выберите тип файла для загрузки</span>
              </button>
              <div class="upload-types">
                <button type="button" @click="chooseFiles('image/jpeg,image/png,image/webp')">
                  <LayoutAppIcon name="camera" />
                  <span><strong>Загрузить фото</strong><small>JPG, PNG, WEBP до 10 МБ</small></span>
                </button>
                <button type="button" @click="chooseFiles('video/mp4,video/quicktime')">
                  <LayoutAppIcon name="play" />
                  <span><strong>Загрузить видео</strong><small>MP4, MOV до 100 МБ</small></span>
                </button>
                <button type="button" @click="chooseFiles('application/pdf')">
                  <LayoutAppIcon name="file" />
                  <span><strong>Загрузить документы</strong><small>PDF до 20 МБ</small></span>
                </button>
              </div>
            </div>

            <h2>Прикреплённые файлы ({{ attachments.length }}/10)</h2>
            <div class="file-list">
              <article v-if="attachments.length === 0" class="file-item">
                <span class="file-preview file-extension"><b>0</b><small>Файлов</small></span>
                <div class="file-copy">
                  <strong>Файлы пока не прикреплены</strong>
                  <span>{{ isAnonymousMode ? 'Для инкогнито нужен хотя бы один файл.' : 'Можно пропустить, если вы оставляете контакты для проверки.' }}</span>
                </div>
              </article>
              <article v-for="attachment in attachments" :key="attachment.id" class="file-item">
                <span class="file-preview file-extension" :class="`is-${attachment.kind}`">
                  <b>{{ formatAttachmentKind(attachment.kind) }}</b>
                  <small>Тип файла</small>
                </span>
                <div class="file-copy">
                  <strong>{{ attachment.originalName }}</strong>
                  <span>{{ formatFileSize(attachment.size) }}</span>
                </div>
                <span class="ready-badge"><LayoutAppIcon name="check" />Готово</span>
                <div class="file-actions">
                  <button class="danger-icon" type="button" aria-label="Удалить файл" @click="deleteAttachment(attachment)">
                    <LayoutAppIcon name="refresh" />
                  </button>
                </div>
              </article>
            </div>

            <div class="file-rules">
              <article><LayoutAppIcon name="file" /><strong>Допустимые форматы:</strong><span>JPG, PNG, WEBP, MP4, MOV, PDF</span></article>
              <article><LayoutAppIcon name="shield" /><strong>Максимальный размер:</strong><span>Фото — 10 МБ, видео — 100 МБ, PDF — 20 МБ</span></article>
              <article><LayoutAppIcon name="check" /><strong>{{ isAnonymousMode ? 'Минимум 1 файл' : 'Максимум 10 файлов' }}</strong><span>{{ isAnonymousMode ? 'Фото, видео или документ обязательны для подачи инкогнито.' : 'Лишние материалы лучше объединить в один документ.' }}</span></article>
            </div>

            <div class="wizard-actions three">
              <button class="appeal-button appeal-button-secondary" type="button" @click="previousStep">
                <LayoutAppIcon name="back" />
                Назад
              </button>
              <button class="appeal-button appeal-button-primary" type="button" :disabled="saving || uploading" @click="nextStep">
                Далее
                <LayoutAppIcon name="arrow" />
              </button>
              <button class="appeal-button appeal-button-secondary" type="button" :disabled="saving" @click="saveDraft()">
                <LayoutAppIcon name="file" />
                {{ saveActionLabel }}
              </button>
            </div>
          </section>

          <section v-show="currentStep === 4" class="appeal-panel" aria-labelledby="step-4-title">
            <h1 id="step-4-title">
              {{ isModerationEditMode ? 'Редактирование обращения — Шаг 4. Проверьте изменения' : 'Подача обращения / жалобы — Шаг 4. Отправка и результат' }}
            </h1>
            <p class="appeal-lead">
              Проверьте данные и отправьте обращение. Мы проверяем все обращения независимо от выбранного способа подачи.
            </p>

            <h2>Способ подачи обращения</h2>
            <div class="choice-grid choice-grid-2">
              <button
                class="choice-card"
                :class="{ 'is-selected': form.submissionMode === 'anonymous' }"
                type="button"
                @click="form.submissionMode = 'anonymous'"
              >
                <LayoutAppIcon name="user" />
                <span><strong>Инкогнито</strong><small>Без контактов и без предложения личного кабинета</small></span>
              </button>
              <button
                class="choice-card"
                :class="{ 'is-selected': form.submissionMode === 'contacts' }"
                type="button"
                @click="form.submissionMode = 'contacts'"
              >
                <LayoutAppIcon name="phone" />
                <span><strong>С контактными данными</strong><small>Контакты нужны только для связи и проверки</small></span>
              </button>
            </div>

            <div v-if="isAnonymousMode" class="anonymous-submit-note">
              <LayoutAppIcon name="shield" />
              <div>
                <strong>Подача инкогнито</strong>
                <p>Контактные данные не запрашиваются. После отправки вы получите номер обращения, а проверка будет выполнена по описанию, месту и приложенным материалам.</p>
              </div>
            </div>

            <div v-else class="contacts-layout">
              <div class="contact-form">
                <h2>Ваши контактные данные</h2>
                <p>Мы используем контакты для уточнений, проверки и выезда на место. В публичной части обращения они не отображаются.</p>
                <label><span class="field-label">ФИО <sup>*</sup></span><input v-model="form.contactName" placeholder="Например: Иванов Иван Иванович"></label>
                <label><span class="field-label">Телефон</span><span class="verify-line"><input v-model="form.contactPhone" placeholder="+7 (___) ___-__-__"><button type="button">Подтвердить</button></span></label>
                <label><span class="field-label">Эл. почта</span><span class="verify-line"><input v-model="form.contactEmail" type="email" placeholder="example@mail.ru"><button type="button">Подтвердить</button></span></label>

                <label class="cabinet-checkbox">
                  <input v-model="form.createCabinet" type="checkbox">
                  <span><strong>Предложить личный кабинет после отправки</strong>Кабинет поможет отслеживать статус, получать уведомления и дополнять обращение материалами.</span>
                </label>
                <NuxtLink class="login-strip" to="/login"><LayoutAppIcon name="user" />У меня уже есть личный кабинет — войти и продолжить</NuxtLink>
              </div>

              <aside class="confirm-steps">
                <h2>Как используются контакты</h2>
                <article><span><LayoutAppIcon name="phone" /></span><div><strong>Телефон</strong><p>Нужен для оперативных уточнений и согласования проверки на месте.</p><em>Телефон или электронная почта обязательны</em></div></article>
                <article><span><LayoutAppIcon name="mail" /></span><div><strong>Эл. почта</strong><p>Подойдёт для письменной связи, статуса и ответа по обращению.</p><em>Телефон или электронная почта обязательны</em></div></article>
                <article><span><LayoutAppIcon name="shield" /></span><div><p>Контакты хранятся отдельно от публичной карточки обращения и не раскрываются посетителям сайта.</p></div></article>
              </aside>
            </div>

            <h2>Краткое содержание обращения</h2>
            <div class="short-summary">
              <article><LayoutAppIcon :name="selectedCategory.icon" /><span>Категория<strong>{{ selectedCategory.label }}</strong></span></article>
              <article><LayoutAppIcon name="user" /><span>Способ подачи<strong>{{ contactModeLabel }}</strong></span></article>
              <article><LayoutAppIcon name="file" /><span>Прикреплённые файлы<strong>{{ attachments.length }} файлов</strong></span></article>
            </div>

            <hr class="appeal-divider">

            <h2>Проверка от спама <small><LayoutAppIcon name="shield" />Защита от автоматических отправок</small></h2>
            <label class="captcha-box">
              <span class="captcha-check"><LayoutAppIcon name="check" /></span>
              <input v-model="form.captchaChecked" type="checkbox">
              <div><strong>Проверка пройдена</strong><p>В текущем окружении используется тестовая проверка.</p></div>
              <footer><span>Защита формы</span><span>Проверка обязательна перед отправкой</span></footer>
            </label>

            <h2>Проверка перед отправкой</h2>
            <div class="review-list">
              <article><LayoutAppIcon :name="selectedCategory.icon" /><div><span>Категория обращения</span><strong>{{ selectedCategory.label }}</strong></div><button type="button" @click="goToStep(1)"><LayoutAppIcon name="edit" />Изменить</button></article>
              <article><LayoutAppIcon name="file" /><div><span>Описание проблемы</span><strong>{{ form.title || 'Название не указано' }}</strong><p>{{ form.description || 'Описание не указано' }}</p></div><button type="button" @click="goToStep(2)"><LayoutAppIcon name="edit" />Изменить</button></article>
              <article><LayoutAppIcon name="upload" /><div><span>Прикреплённые файлы</span><strong>{{ attachments.length }} файлов</strong><div class="review-thumbs"><span v-for="attachment in attachments.slice(0, 5)" :key="attachment.id">{{ formatAttachmentKind(attachment.kind) }}</span></div></div><button type="button" @click="goToStep(3)"><LayoutAppIcon name="edit" />Изменить</button></article>
              <article><LayoutAppIcon name="pin" /><div><span>Место происшествия</span><strong>{{ locationLabel }}</strong></div><button type="button" @click="goToStep(2)"><LayoutAppIcon name="edit" />Изменить</button></article>
              <article v-if="!isAnonymousMode"><LayoutAppIcon name="phone" /><div><span>Способ связи</span><strong>{{ form.contactEmail || form.contactPhone || 'Контакты не указаны' }}</strong></div><button type="button" @click="goToStep(4)"><LayoutAppIcon name="edit" />Изменить</button></article>
              <article><LayoutAppIcon name="user" /><div><span>Подача обращения</span><strong>{{ contactModeLabel }}</strong><div v-if="!isAnonymousMode" class="cabinet-toggle"><span><strong>Личный кабинет</strong>{{ form.createCabinet ? 'Будет предложен для отслеживания' : 'Не предлагается после отправки' }}</span><i /></div></div><button type="button" @click="goToStep(4)"><LayoutAppIcon name="edit" />Изменить</button></article>
            </div>

            <label class="consent-row">
              <input v-model="form.consent" type="checkbox">
              <span>Я подтверждаю достоверность информации и согласен на обработку данных по правилам платформы.</span>
            </label>

            <div class="wizard-actions final">
              <button class="appeal-button appeal-button-secondary" type="button" @click="previousStep">
                <LayoutAppIcon name="back" />
                <span>Редактировать<small>Вернуться к редактированию</small></span>
              </button>
              <button class="appeal-button appeal-button-primary" type="button" :disabled="!canSubmit" @click="submitDraft">
                <LayoutAppIcon name="arrow" />
                <span>{{ primarySubmitLabel }}<small>{{ primarySubmitHint }}</small></span>
              </button>
              <button class="appeal-button appeal-button-secondary" type="button" :disabled="saving" @click="saveDraft()">
                <LayoutAppIcon name="file" />
                <span>{{ saveActionLabel }}<small>Сохранить и выйти</small></span>
              </button>
            </div>
          </section>

          <p v-if="saveMessage" class="form-message form-message--success">{{ saveMessage }}</p>
          <p v-if="errorMessage" class="form-message form-message--error">{{ errorMessage }}</p>
        </div>

        <aside class="appeal-side-card" aria-label="Информация о подаче обращения">
          <div class="appeal-side-art">
            <img src="/assets/verification-illustration.png" alt="" width="1536" height="864">
          </div>
          <div class="appeal-side-copy">
            <h2>Ваш голос важен для нашего города и страны</h2>
            <p>Мы работаем для того, чтобы решить проблемы граждан и сделать жизнь лучше. Каждое обращение получает внимание и контроль.</p>
            <div class="side-benefits">
              <article><span><LayoutAppIcon name="shield" /></span><div><strong>Защита ваших прав</strong><p>Мы поможем защитить ваши законные права и интересы.</p></div></article>
              <article><span><LayoutAppIcon name="file" /></span><div><strong>Контроль и результат</strong><p>Каждое обращение рассматривается, а результаты доступны вам.</p></div></article>
              <article><span><LayoutAppIcon name="users" /></span><div><strong>Вместе мы сильнее</strong><p>Объединяя усилия, мы делаем города безопаснее.</p></div></article>
            </div>
            <div class="side-secure"><LayoutAppIcon name="lock" /><strong>Ваши данные защищены и не передаются третьим лицам.</strong></div>
          </div>
        </aside>
      </div>
    </section>

    <section v-else class="container appeal-result-screen" aria-labelledby="sent-title">
      <div class="sent-layout">
        <div class="sent-card">
          <div class="success-heading">
            <span><LayoutAppIcon name="check" /></span>
            <div>
              <h1 id="sent-title">Обращение / жалоба успешно отправлено</h1>
              <p>{{ isAnonymousMode ? 'Обращение инкогнито принято. Мы проверим описание, место и приложенные материалы.' : 'Обращение получено. Мы свяжемся с вами при необходимости и проверим информацию на месте.' }}</p>
            </div>
          </div>

          <div class="receipt-card">
            <dl>
              <div><dt><LayoutAppIcon name="file" />Номер обращения</dt><dd><mark>{{ submittedDraft.id }}</mark></dd></div>
              <div><dt><LayoutAppIcon name="clock" />Дата отправки</dt><dd>{{ submittedDraft.submittedAt || 'только что' }}</dd></div>
              <div><dt><LayoutAppIcon :name="selectedCategory.icon" />Категория</dt><dd>{{ selectedCategory.label }} · {{ form.title }}</dd></div>
              <div><dt><LayoutAppIcon name="shield" />Статус</dt><dd><span class="status-pill green">Принято в проверку</span></dd></div>
            </dl>
            <div class="receipt-note">
              <LayoutAppIcon name="shield" />
              <p v-if="isAnonymousMode"><strong>Личный кабинет не предлагается для подачи инкогнито.</strong> Сохраните номер обращения, чтобы ориентироваться в публичном статусе после проверки.</p>
              <p v-else><strong>Контактные данные нужны только для проверки и связи.</strong> Если вы выбрали предложение кабинета, после входа сможете отслеживать статус и уведомления.</p>
            </div>
            <div class="result-actions">
              <NuxtLink v-if="!isAnonymousMode && form.createCabinet" class="appeal-button appeal-button-primary" to="/dashboard/profile"><LayoutAppIcon name="file" />Перейти в личный кабинет</NuxtLink>
              <NuxtLink class="appeal-button appeal-button-secondary" to="/appeals"><LayoutAppIcon name="eye" />К обращениям</NuxtLink>
              <NuxtLink class="appeal-button appeal-button-secondary" to="/"><LayoutAppIcon name="home" />На главную</NuxtLink>
            </div>
          </div>
          <p class="micro-note"><LayoutAppIcon name="shield" />{{ isAnonymousMode ? 'Проверка будет выполнена без связи с заявителем.' : 'После проверки мы сможем связаться с вами по указанным контактам.' }}</p>
        </div>

        <aside class="next-card">
          <img src="/assets/hero-community-meeting.png" alt="" width="1536" height="864">
          <h2>Что дальше?</h2>
          <article><span><LayoutAppIcon name="users" /></span><div><strong>Проверка обращения</strong><p>Мы проверим предоставленную информацию{{ isAnonymousMode ? ' и приложенные материалы.' : ', контакты и место происшествия.' }}</p></div></article>
          <article><span><LayoutAppIcon name="file" /></span><div><strong>Модерация</strong><p>После проверки обращение будет опубликовано или направлено ответственным.</p></div></article>
          <article v-if="!isAnonymousMode"><span><LayoutAppIcon name="bell" /></span><div><strong>Связь с заявителем</strong><p>При необходимости мы уточним детали и согласуем проверку на месте.</p></div></article>
          <article v-else><span><LayoutAppIcon name="shield" /></span><div><strong>Без обратной связи</strong><p>Мы не будем запрашивать аккаунт или контакты после подачи инкогнито.</p></div></article>
          <div class="side-secure"><LayoutAppIcon name="bell" /><strong>{{ isAnonymousMode ? 'Сохраните номер обращения: персональные уведомления для инкогнито недоступны.' : 'Мы уведомим вас о значимых изменениях по обращению.' }}</strong></div>
        </aside>
      </div>
    </section>
  </main>
</template>
