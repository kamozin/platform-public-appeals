<script setup lang="ts">
export type AppIconName =
  | 'arrow-up'
  | 'arrow'
  | 'back'
  | 'bell'
  | 'bolt'
  | 'book'
  | 'building'
  | 'camera'
  | 'check'
  | 'clock'
  | 'comment'
  | 'edit'
  | 'education'
  | 'eye'
  | 'file'
  | 'flag'
  | 'grid'
  | 'hand-heart'
  | 'heart'
  | 'home'
  | 'info'
  | 'lock'
  | 'mail'
  | 'medical'
  | 'menu'
  | 'more'
  | 'parking'
  | 'phone'
  | 'pin'
  | 'plus'
  | 'play'
  | 'refresh'
  | 'road'
  | 'scale'
  | 'share'
  | 'shield'
  | 'shop'
  | 'social-max'
  | 'social-ok'
  | 'social-vk'
  | 'star'
  | 'trend'
  | 'trash'
  | 'tree'
  | 'upload'
  | 'user'
  | 'users'
  | 'volume'
  | 'wallet';

defineProps<{
  name: AppIconName;
}>();
</script>

<template>
  <svg class="app-icon" aria-hidden="true" focusable="false">
    <use :href="`#${name}`" />
  </svg>
</template>
