<script setup lang="ts">
definePageMeta({
  layout: 'auth',
});

const auth = useAuth();
const form = reactive({
  name: '',
  phone: '',
  email: '',
  password: '',
  password_confirmation: '',
  privacy: true,
  notifications: true,
});
const pending = ref(false);
const errorMessage = ref('');
const passwordVisible = ref(false);
const passwordConfirmationVisible = ref(false);

const passwordInputType = computed(() => passwordVisible.value ? 'text' : 'password');
const passwordConfirmationInputType = computed(() => passwordConfirmationVisible.value ? 'text' : 'password');
const passwordToggleLabel = computed(() => passwordVisible.value ? 'Скрыть пароль' : 'Показать пароль');
const passwordConfirmationToggleLabel = computed(() => passwordConfirmationVisible.value ? 'Скрыть пароль' : 'Показать пароль');

useSeoMeta({
  title: 'Регистрация',
  description: 'Регистрация личного кабинета НКО Рука добра для подачи обращений и жалоб.',
});
useNoindexSeo();

const togglePassword = (): void => {
  passwordVisible.value = !passwordVisible.value;
};

const togglePasswordConfirmation = (): void => {
  passwordConfirmationVisible.value = !passwordConfirmationVisible.value;
};

const submitRegister = async (): Promise<void> => {
  pending.value = true;
  errorMessage.value = '';

  try {
    await auth.register(form);
    await navigateTo('/dashboard/profile');
  } catch {
    errorMessage.value = 'Не удалось создать аккаунт. Проверьте поля формы.';
  } finally {
    pending.value = false;
  }
};
</script>

<template>
  <main id="main-content" class="auth-main" tabindex="-1">
    <section class="container auth-card auth-card-register" aria-labelledby="register-title">
      <div class="auth-form-side">
        <div class="auth-form-wrap">
          <h1 id="register-title">Регистрация</h1>
          <p class="auth-lead">
            Создайте личный кабинет, чтобы подавать обращения и жалобы, отслеживать статусы и получать ответы.
          </p>

          <form class="auth-form" @submit.prevent="submitRegister">
            <label class="auth-field">
              <span>Имя и фамилия</span>
              <span class="auth-input">
                <LayoutAppIcon name="user" />
                <input
                  v-model="form.name"
                  type="text"
                  name="name"
                  autocomplete="name"
                  placeholder="Введите ваши имя и фамилию"
                  required
                >
              </span>
            </label>

            <label class="auth-field">
              <span>Телефон</span>
              <span class="auth-input">
                <LayoutAppIcon name="phone" />
                <input
                  v-model="form.phone"
                  type="tel"
                  name="phone"
                  autocomplete="tel"
                  placeholder="Введите номер телефона"
                >
              </span>
            </label>

            <label class="auth-field">
              <span>Email</span>
              <span class="auth-input">
                <LayoutAppIcon name="mail" />
                <input
                  v-model="form.email"
                  type="email"
                  name="email"
                  autocomplete="email"
                  placeholder="Введите email"
                  required
                >
              </span>
            </label>

            <label class="auth-field">
              <span>Пароль</span>
              <span class="auth-input">
                <LayoutAppIcon name="lock" />
                <input
                  v-model="form.password"
                  :type="passwordInputType"
                  name="password"
                  autocomplete="new-password"
                  placeholder="Придумайте пароль"
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
              <span>Повторите пароль</span>
              <span class="auth-input">
                <LayoutAppIcon name="lock" />
                <input
                  v-model="form.password_confirmation"
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

            <div class="auth-consents">
              <label class="auth-check">
                <input v-model="form.privacy" type="checkbox" name="privacy" required>
                <span>
                  Я согласен с
                  <NuxtLink to="/privacy">
                    политикой конфиденциальности
                  </NuxtLink>
                </span>
              </label>
              <label class="auth-check">
                <input v-model="form.notifications" type="checkbox" name="notifications">
                <span>Хочу получать уведомления о статусе обращений</span>
              </label>
            </div>

            <p v-if="errorMessage" class="form-message form-message--error">
              {{ errorMessage }}
            </p>

            <button class="auth-primary" type="submit" :disabled="pending">
              Зарегистрироваться
            </button>
          </form>

          <p class="auth-switch">
            Уже есть аккаунт?
            <NuxtLink to="/login">
              Войти
            </NuxtLink>
          </p>
        </div>
      </div>

      <aside class="auth-info-side" aria-label="Возможности личного кабинета">
        <img
          class="auth-illustration"
          src="/assets/auth-register-illustration.svg"
          alt=""
          width="620"
          height="360"
        >

        <div class="auth-benefits">
          <h2>Личный кабинет для обращений и жалоб</h2>
          <article>
            <span class="auth-benefit-icon">
              <LayoutAppIcon name="edit" />
            </span>
            <div>
              <h3>Подача обращений</h3>
              <p>Отправляйте обращения и жалобы в несколько кликов.</p>
            </div>
          </article>
          <article>
            <span class="auth-benefit-icon">
              <LayoutAppIcon name="clock" />
            </span>
            <div>
              <h3>Отслеживание статуса</h3>
              <p>Следите за рассмотрением и результатами в личном кабинете.</p>
            </div>
          </article>
          <article>
            <span class="auth-benefit-icon">
              <LayoutAppIcon name="bell" />
            </span>
            <div>
              <h3>Уведомления</h3>
              <p>Получайте ответы и важные обновления на email или телефон.</p>
            </div>
          </article>
        </div>
      </aside>
    </section>
  </main>
</template>
