<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';

definePageMeta({
  layout: 'auth',
});

type ChallengeResponse = {
  id: string;
  expiresAt?: string;
  devCode?: string;
};

const api = useApi();
const email = ref('');
const code = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const challengeId = ref('');
const devCode = ref('');
const expiresAt = ref('');
const statusMessage = ref('');
const errorMessage = ref('');
const sendPending = ref(false);
const completePending = ref(false);
const passwordVisible = ref(false);
const passwordConfirmationVisible = ref(false);

const passwordInputType = computed(() => passwordVisible.value ? 'text' : 'password');
const passwordConfirmationInputType = computed(() => passwordConfirmationVisible.value ? 'text' : 'password');
const passwordToggleLabel = computed(() => passwordVisible.value ? 'Скрыть пароль' : 'Показать пароль');
const passwordConfirmationToggleLabel = computed(() => passwordConfirmationVisible.value ? 'Скрыть пароль' : 'Показать пароль');
const timerLabel = computed(() => {
  if (!expiresAt.value) {
    return '';
  }

  const secondsLeft = Math.max(0, Math.ceil((Date.parse(expiresAt.value) - Date.now()) / 1000));
  const minutes = Math.floor(secondsLeft / 60).toString().padStart(2, '0');
  const seconds = (secondsLeft % 60).toString().padStart(2, '0');

  return `${minutes}:${seconds}`;
});

useSeoMeta({
  title: 'Восстановление пароля',
  description: 'Экран восстановления пароля личного кабинета Рука добра.',
});
useNoindexSeo();

const togglePassword = (): void => {
  passwordVisible.value = !passwordVisible.value;
};

const togglePasswordConfirmation = (): void => {
  passwordConfirmationVisible.value = !passwordConfirmationVisible.value;
};

const sendReset = async (): Promise<void> => {
  sendPending.value = true;
  errorMessage.value = '';
  statusMessage.value = '';

  try {
    const response = await api.request<ApiDataEnvelope<ChallengeResponse>>('/auth/password-reset/send', 'POST', {
      body: { email: email.value },
    });
    challengeId.value = response.data.id;
    devCode.value = response.data.devCode ?? '';
    expiresAt.value = response.data.expiresAt ?? '';
    statusMessage.value = 'Код восстановления отправлен.';
  } catch {
    errorMessage.value = 'Не удалось отправить код восстановления.';
  } finally {
    sendPending.value = false;
  }
};

const completeReset = async (): Promise<void> => {
  completePending.value = true;
  errorMessage.value = '';
  statusMessage.value = '';

  try {
    await api.request('/auth/password-reset/complete', 'POST', {
      body: {
        challenge_id: challengeId.value,
        code: code.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      },
    });
    statusMessage.value = 'Пароль обновлён. Теперь можно войти.';
  } catch {
    errorMessage.value = 'Не удалось обновить пароль.';
  } finally {
    completePending.value = false;
  }
};
</script>

<template>
  <main id="main-content" class="screen-main" tabindex="-1">
    <section class="container screen-card flow-card" aria-labelledby="reset-title">
      <div class="flow-form-side">
        <div class="flow-form-wrap">
          <h1 id="reset-title">Восстановление пароля</h1>
          <p class="screen-lead">
            Мы поможем вам восстановить доступ к личному кабинету. Получите код и задайте новый пароль.
          </p>

          <form class="flow-form" @submit.prevent="completeReset">
            <section class="flow-step" aria-labelledby="reset-step-1">
              <h2 id="reset-step-1">Шаг 1. Получите код подтверждения</h2>
              <label class="auth-field">
                <span>Email</span>
                <span class="auth-input">
                  <LayoutAppIcon name="mail" />
                  <input
                    v-model="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    placeholder="Введите email"
                    required
                  >
                </span>
              </label>
              <button
                class="auth-primary"
                type="button"
                :disabled="sendPending || !email"
                @click="sendReset"
              >
                Получить код
              </button>
            </section>

            <div class="auth-divider">
              <span>или</span>
            </div>

            <section class="flow-step" aria-labelledby="reset-step-2">
              <h2 id="reset-step-2">Шаг 2. Введите код подтверждения</h2>
              <label class="auth-field">
                <span class="sr-only">Код подтверждения</span>
                <span class="auth-input code-input">
                  <LayoutAppIcon name="shield" />
                  <input
                    v-model="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    placeholder="Введите код из email"
                    required
                  >
                  <span v-if="timerLabel" class="input-timer" :aria-label="`Код действует ещё ${timerLabel}`">
                    <LayoutAppIcon name="refresh" />
                    {{ timerLabel }}
                  </span>
                </span>
              </label>
            </section>

            <section class="flow-step" aria-labelledby="reset-step-3">
              <h2 id="reset-step-3">Шаг 3. Задайте новый пароль</h2>
              <label class="auth-field">
                <span class="sr-only">Новый пароль</span>
                <span class="auth-input">
                  <LayoutAppIcon name="lock" />
                  <input
                    v-model="password"
                    :type="passwordInputType"
                    name="password"
                    autocomplete="new-password"
                    placeholder="Новый пароль"
                    minlength="8"
                    required
                  >
                  <button
                    class="password-toggle"
                    type="button"
                    :aria-label="passwordToggleLabel"
                    @click="togglePassword"
                  >
                    <LayoutAppIcon name="eye" />
                  </button>
                </span>
              </label>
              <label class="auth-field">
                <span class="sr-only">Повторите пароль</span>
                <span class="auth-input">
                  <LayoutAppIcon name="lock" />
                  <input
                    v-model="passwordConfirmation"
                    :type="passwordConfirmationInputType"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Повторите пароль"
                    minlength="8"
                    required
                  >
                  <button
                    class="password-toggle"
                    type="button"
                    :aria-label="passwordConfirmationToggleLabel"
                    @click="togglePasswordConfirmation"
                  >
                    <LayoutAppIcon name="eye" />
                  </button>
                </span>
              </label>

              <p v-if="devCode" class="form-message">
                Код для локальной проверки: {{ devCode }}
              </p>
              <p v-if="statusMessage" class="form-message form-message--success">
                {{ statusMessage }}
              </p>
              <p v-if="errorMessage" class="form-message form-message--error">
                {{ errorMessage }}
              </p>

              <button
                class="auth-primary"
                type="submit"
                :disabled="completePending || !challengeId"
              >
                Сохранить новый пароль
              </button>
            </section>
          </form>

          <NuxtLink class="back-link" to="/login">
            <LayoutAppIcon name="back" />
            Вернуться ко входу
          </NuxtLink>
        </div>
      </div>

      <aside class="flow-info-side" aria-label="Преимущества восстановления доступа">
        <img
          class="screen-illustration flow-illustration"
          src="/assets/password-reset-illustration.png"
          alt=""
          width="1536"
          height="864"
        >
        <div class="flow-benefits">
          <article>
            <span>
              <LayoutAppIcon name="shield" />
            </span>
            <div>
              <h2>Надёжная защита</h2>
              <p>Мы используем современные технологии для защиты ваших данных.</p>
            </div>
          </article>
          <article>
            <span>
              <LayoutAppIcon name="bolt" />
            </span>
            <div>
              <h2>Быстрый доступ</h2>
              <p>Восстановите доступ за несколько минут и продолжайте пользоваться сервисом.</p>
            </div>
          </article>
          <article>
            <span>
              <LayoutAppIcon name="lock" />
            </span>
            <div>
              <h2>Конфиденциальность</h2>
              <p>Ваши данные и обращения строго конфиденциальны и не передаются третьим лицам.</p>
            </div>
          </article>
        </div>
      </aside>
    </section>
  </main>
</template>
