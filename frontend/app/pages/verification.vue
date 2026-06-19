<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';

definePageMeta({
  layout: 'auth',
});

type ChallengeResponse = {
  id: string;
  expiresAt: string;
  devCode?: string;
};

const api = useApi();
const channel = ref<'email' | 'phone'>('email');
const target = ref('');
const code = ref('');
const challengeId = ref('');
const devCode = ref('');
const statusMessage = ref('');
const errorMessage = ref('');
const sendPending = ref(false);
const verifyPending = ref(false);

const targetLabel = computed(() => channel.value === 'email' ? 'Email' : 'Телефон');
const targetInputType = computed(() => channel.value === 'email' ? 'email' : 'tel');

useSeoMeta({
  title: 'Подтверждение контакта',
  description: 'Подтверждение email или телефона для личного кабинета Рука добра.',
});
useNoindexSeo();

const sendCode = async (): Promise<void> => {
  errorMessage.value = '';
  statusMessage.value = '';
  sendPending.value = true;

  try {
    const response = await api.request<ApiDataEnvelope<ChallengeResponse>>('/auth/verification/send', 'POST', {
      body: {
        channel: channel.value,
        target: target.value,
      },
    });
    challengeId.value = response.data.id;
    devCode.value = response.data.devCode ?? '';
    statusMessage.value = 'Код отправлен.';
  } catch {
    errorMessage.value = 'Не удалось отправить код подтверждения.';
  } finally {
    sendPending.value = false;
  }
};

const verifyCode = async (): Promise<void> => {
  errorMessage.value = '';
  statusMessage.value = '';
  verifyPending.value = true;

  try {
    await api.request('/auth/verification/verify', 'POST', {
      body: {
        challenge_id: challengeId.value,
        code: code.value,
      },
    });
    statusMessage.value = 'Контакт подтверждён.';
  } catch {
    errorMessage.value = 'Неверный или истекший код.';
  } finally {
    verifyPending.value = false;
  }
};
</script>

<template>
  <main id="main-content" class="screen-main" tabindex="-1">
    <section class="container screen-card flow-card verification-card" aria-labelledby="verification-title">
      <div class="flow-form-side">
        <div class="flow-form-wrap">
          <h1 id="verification-title">Подтвердите контакт</h1>
          <p class="screen-lead">
            Выберите удобный канал, получите одноразовый код и подтвердите контакт для уведомлений по обращениям.
          </p>

          <form class="flow-form verify-form" @submit.prevent="challengeId ? verifyCode() : sendCode()">
            <section class="method-section" aria-labelledby="verification-method-title">
              <h2 id="verification-method-title">Канал подтверждения</h2>
              <div class="method-grid">
                <label class="method-card" :class="{ active: channel === 'email' }">
                  <input v-model="channel" type="radio" name="channel" value="email">
                  <LayoutAppIcon name="mail" />
                  <span>Email</span>
                  <i><LayoutAppIcon name="check" /></i>
                </label>
                <label class="method-card" :class="{ active: channel === 'phone' }">
                  <input v-model="channel" type="radio" name="channel" value="phone">
                  <LayoutAppIcon name="phone" />
                  <span>Телефон</span>
                  <i><LayoutAppIcon name="check" /></i>
                </label>
              </div>
            </section>

            <section class="verification-target" aria-labelledby="verification-target-title">
              <h2 id="verification-target-title">{{ targetLabel }}</h2>
              <label class="auth-field">
                <span class="sr-only">{{ targetLabel }}</span>
                <span class="auth-input">
                  <LayoutAppIcon :name="channel === 'email' ? 'mail' : 'phone'" />
                  <input
                    v-model="target"
                    :type="targetInputType"
                    name="target"
                    :autocomplete="channel === 'email' ? 'email' : 'tel'"
                    :placeholder="channel === 'email' ? 'Введите email' : 'Введите телефон'"
                    required
                  >
                </span>
              </label>
              <button
                class="auth-primary"
                type="button"
                :disabled="sendPending || !target"
                @click="sendCode"
              >
                Отправить код
              </button>
            </section>

            <section class="otp-section" aria-labelledby="verification-code-title">
              <h2 id="verification-code-title">Код подтверждения</h2>
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
                    placeholder="Введите код"
                    required
                  >
                </span>
              </label>
            </section>

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
              :disabled="verifyPending || !challengeId"
            >
              Подтвердить контакт
            </button>
          </form>
        </div>
      </div>

      <aside class="flow-info-side" aria-label="Зачем подтверждать контакт">
        <img
          class="screen-illustration flow-illustration"
          src="/assets/verification-illustration.png"
          alt=""
          width="1536"
          height="864"
        >
        <div class="flow-benefits">
          <article>
            <span>
              <LayoutAppIcon name="bell" />
            </span>
            <div>
              <h2>Статусы без задержек</h2>
              <p>Получайте уведомления, когда обращение проходит проверку, публикуется или получает ответ.</p>
            </div>
          </article>
          <article>
            <span>
              <LayoutAppIcon name="shield" />
            </span>
            <div>
              <h2>Защита аккаунта</h2>
              <p>Подтверждённый контакт помогает безопасно восстановить доступ к личному кабинету.</p>
            </div>
          </article>
          <article>
            <span>
              <LayoutAppIcon name="check" />
            </span>
            <div>
              <h2>Доверие к обращениям</h2>
              <p>Контактная проверка снижает ошибки в обратной связи и ускоряет сопровождение жалоб.</p>
            </div>
          </article>
        </div>
      </aside>
    </section>
  </main>
</template>
