<script setup lang="ts">
import { Icon } from '#components'

defineProps<{
  title: string
  description: string
  backTo?: string
}>()
</script>

<template>
  <div class="admin-header">
    <NuxtLink
      :to="backTo || '/admin'"
      class="admin-header__back-link"
    >
      <Icon name="heroicons:arrow-left" class="w-5 h-5" />
      Back to Admin Dashboard
    </NuxtLink>
    <h1 class="admin-header__title">
      {{ title }}
    </h1>
    <p class="admin-header__description">{{ description }}</p>
  </div>
</template>

