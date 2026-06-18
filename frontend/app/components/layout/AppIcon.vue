<script setup lang="ts">
export type AppIconName =
  | 'arrow-up'
  | 'arrow'
  | 'back'
  | 'bell'
  | 'bolt'
  | 'book'
  | 'camera'
  | 'check'
  | 'clock'
  | 'comment'
  | 'edit'
  | 'eye'
  | 'file'
  | 'flag'
  | 'grid'
  | 'heart'
  | 'home'
  | 'lock'
  | 'mail'
  | 'menu'
  | 'phone'
  | 'pin'
  | 'play'
  | 'refresh'
  | 'scale'
  | 'share'
  | 'shield'
  | 'social-max'
  | 'social-ok'
  | 'social-vk'
  | 'trend'
  | 'upload'
  | 'user'
  | 'users';

defineProps<{
  name: AppIconName;
}>();
</script>

<template>
  <svg class="app-icon" aria-hidden="true" focusable="false">
    <use :href="`#${name}`" />
  </svg>
</template>
