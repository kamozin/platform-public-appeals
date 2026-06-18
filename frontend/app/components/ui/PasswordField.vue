<script setup lang="ts">
const model = defineModel<string>({ default: '' });

const props = withDefaults(
  defineProps<{
    id?: string | undefined;
    label?: string;
    name?: string;
    placeholder?: string;
    autocomplete?: string;
    required?: boolean;
  }>(),
  {
    id: undefined,
    label: 'Пароль',
    name: 'password',
    placeholder: 'Пароль',
    autocomplete: 'current-password',
    required: false,
  },
);

const fallbackId = useId();
const passwordVisible = ref(false);

const fieldId = computed(() => props.id || `password-${fallbackId}`);
const inputType = computed(() => passwordVisible.value ? 'text' : 'password');
const toggleLabel = computed(() => passwordVisible.value ? 'Скрыть пароль' : 'Показать пароль');

const togglePassword = (): void => {
  passwordVisible.value = !passwordVisible.value;
};
</script>

<template>
  <label class="auth-field password-field" :for="fieldId">
    <span>{{ label }}</span>
    <span class="auth-input">
      <LayoutAppIcon name="lock" />
      <input
        :id="fieldId"
        v-model="model"
        :type="inputType"
        :name="name"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        :required="required"
      >
      <button
        class="password-toggle"
        type="button"
        :aria-label="toggleLabel"
        @click="togglePassword"
      >
        <LayoutAppIcon name="eye" />
      </button>
    </span>
  </label>
</template>
