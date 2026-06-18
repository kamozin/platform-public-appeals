<script setup lang="ts">
import type { ApiDataEnvelope } from '~/types/api/common';

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

useNoindexSeo();

const sendCode = async (): Promise<void> => {
  errorMessage.value = '';
  statusMessage.value = '';
  const response = await api.request<ApiDataEnvelope<ChallengeResponse>>('/auth/verification/send', 'POST', {
    body: {
      channel: channel.value,
      target: target.value,
    },
  });
  challengeId.value = response.data.id;
  devCode.value = response.data.devCode ?? '';
  statusMessage.value = 'Код отправлен.';
};

const verifyCode = async (): Promise<void> => {
  errorMessage.value = '';
  statusMessage.value = '';

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
  }
};
</script>

<template>
  <main class="content-page private-page">
    <section class="auth-panel">
      <p class="content-eyebrow">Подтверждение</p>
      <h1>Подтвердите контакт</h1>
      <form class="private-form" @submit.prevent="challengeId ? verifyCode() : sendCode()">
        <label>
          <span>Канал</span>
          <select v-model="channel">
            <option value="email">Email</option>
            <option value="phone">Телефон</option>
          </select>
        </label>
        <label>
          <span>Email или телефон</span>
          <input v-model="target" type="text" required>
        </label>
        <label v-if="challengeId">
          <span>Код</span>
          <input v-model="code" type="text" inputmode="numeric" maxlength="6" required>
        </label>
        <p v-if="devCode" class="form-message">Код для локальной проверки: {{ devCode }}</p>
        <p v-if="statusMessage" class="form-message form-message--success">{{ statusMessage }}</p>
        <p v-if="errorMessage" class="form-message form-message--error">{{ errorMessage }}</p>
        <button class="btn btn-red" type="submit">
          {{ challengeId ? 'Подтвердить' : 'Отправить код' }}
        </button>
      </form>
    </section>
  </main>
</template>
