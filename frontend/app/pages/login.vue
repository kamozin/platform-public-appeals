<script setup lang="ts">
definePageMeta({
  layout: 'auth',
});

const auth = useAuth();
const loginValue = ref('');
const password = ref('');
const remember = ref(true);
const pending = ref(false);
const errorMessage = ref('');
const passwordVisible = ref(false);
const twoFactorChallenge = ref<{
  challengeId: string;
  maskedTarget: string;
  expiresAt: string | null;
} | null>(null);
const twoFactorCode = ref('');

const passwordInputType = computed(() => passwordVisible.value ? 'text' : 'password');
const passwordToggleLabel = computed(() => passwordVisible.value ? 'Скрыть пароль' : 'Показать пароль');
const isTwoFactorStep = computed(() => twoFactorChallenge.value !== null);

useSeoMeta({
  title: 'Вход в личный кабинет',
  description: 'Вход в личный кабинет НКО Рука добра для отслеживания обращений и жалоб.',
});
useNoindexSeo();

const togglePassword = (): void => {
  passwordVisible.value = !passwordVisible.value;
};

const submitLogin = async (): Promise<void> => {
  pending.value = true;
  errorMessage.value = '';

  try {
    const result = await auth.login({
      login: loginValue.value,
      password: password.value,
      remember: remember.value,
    });

    if ('requiresTwoFactor' in result) {
      twoFactorChallenge.value = {
        challengeId: result.challengeId,
        maskedTarget: result.maskedTarget,
        expiresAt: result.expiresAt,
      };
      twoFactorCode.value = '';

      return;
    }

    await navigateTo('/dashboard/profile');
  } catch {
    errorMessage.value = 'Проверьте логин и пароль.';
  } finally {
    pending.value = false;
  }
};

const submitTwoFactor = async (): Promise<void> => {
  if (!twoFactorChallenge.value) {
    return;
  }

  pending.value = true;
  errorMessage.value = '';

  try {
    await auth.verifyTwoFactor({
      challenge_id: twoFactorChallenge.value.challengeId,
      code: twoFactorCode.value,
    });
    await navigateTo('/dashboard/profile');
  } catch {
    errorMessage.value = 'Проверьте код из письма.';
  } finally {
    pending.value = false;
  }
};

const resetTwoFactorStep = (): void => {
  twoFactorChallenge.value = null;
  twoFactorCode.value = '';
  errorMessage.value = '';
};
</script>

<template>
  <main id="main-content" class="auth-main" tabindex="-1">
    <section class="container auth-card auth-card-login" aria-labelledby="login-title">
      <div class="auth-form-side">
        <div class="auth-form-wrap">
          <h1 id="login-title">Вход в личный кабинет</h1>
          <p class="auth-lead">
            Отслеживайте статус обращений, получайте ответы и будьте в курсе всех изменений.
          </p>

          <form v-if="!isTwoFactorStep" class="auth-form" @submit.prevent="submitLogin">
            <label class="auth-field">
              <span>Телефон или email</span>
              <span class="auth-input">
                <LayoutAppIcon name="user" />
                <input
                  v-model="loginValue"
                  type="text"
                  name="login"
                  autocomplete="username"
                  placeholder="Введите номер телефона или email"
                  required
                >
              </span>
            </label>

            <label class="auth-field">
              <span>Пароль</span>
              <span class="auth-input">
                <LayoutAppIcon name="lock" />
                <input
                  v-model="password"
                  :type="passwordInputType"
                  name="password"
                  autocomplete="current-password"
                  placeholder="Введите пароль"
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

            <div class="auth-options">
              <label class="auth-check">
                <input v-model="remember" type="checkbox" name="remember">
                <span>Запомнить меня</span>
              </label>
              <NuxtLink to="/password-reset">
                Забыли пароль?
              </NuxtLink>
            </div>

            <p v-if="errorMessage" class="form-message form-message--error">
              {{ errorMessage }}
            </p>

            <button class="auth-primary" type="submit" :disabled="pending">
              Войти
            </button>
          </form>

          <form v-else class="auth-form" @submit.prevent="submitTwoFactor">
            <p class="auth-lead">
              Мы отправили код подтверждения на {{ twoFactorChallenge?.maskedTarget }}.
            </p>

            <label class="auth-field">
              <span>Код из письма</span>
              <span class="auth-input">
                <LayoutAppIcon name="shield" />
                <input
                  v-model="twoFactorCode"
                  type="text"
                  name="code"
                  inputmode="numeric"
                  autocomplete="one-time-code"
                  maxlength="6"
                  placeholder="Введите 6 цифр"
                  required
                >
              </span>
            </label>

            <p v-if="errorMessage" class="form-message form-message--error">
              {{ errorMessage }}
            </p>

            <button class="auth-primary" type="submit" :disabled="pending">
              Подтвердить вход
            </button>

            <button class="auth-secondary" type="button" :disabled="pending" @click="resetTwoFactorStep">
              Вернуться к паролю
            </button>
          </form>

          <div class="auth-security-note">
            <LayoutAppIcon name="shield" />
            <span>
              Мы заботимся о безопасности ваших данных и используем современные технологии шифрования.
            </span>
          </div>

          <p class="auth-switch">
            Нет аккаунта?
            <NuxtLink to="/register">
              Зарегистрироваться
            </NuxtLink>
          </p>
        </div>
      </div>

      <aside class="auth-info-side" aria-label="Преимущества личного кабинета">
        <img
          class="auth-illustration"
          src="/assets/auth-login-illustration.svg"
          alt=""
          width="620"
          height="360"
        >

        <div class="auth-benefits">
          <h2>Ваши обращения под защитой</h2>
          <article>
            <span class="auth-benefit-icon">
              <LayoutAppIcon name="lock" />
            </span>
            <div>
              <h3>Конфиденциальность</h3>
              <p>Ваши данные и обращения строго конфиденциальны и не передаются третьим лицам.</p>
            </div>
          </article>
          <article>
            <span class="auth-benefit-icon">
              <LayoutAppIcon name="shield" />
            </span>
            <div>
              <h3>Надёжность</h3>
              <p>Платформа обеспечивает защиту информации и помогает безопасно отслеживать обращения.</p>
            </div>
          </article>
          <article>
            <span class="auth-benefit-icon">
              <LayoutAppIcon name="bell" />
            </span>
            <div>
              <h3>Уведомления</h3>
              <p>Получайте уведомления о статусе ваших обращений на email или в личном кабинете.</p>
            </div>
          </article>
        </div>
      </aside>
    </section>
  </main>
</template>
