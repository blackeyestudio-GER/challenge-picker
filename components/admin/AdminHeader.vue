<script setup lang="ts">
import { Icon } from '#components'

defineProps<{
  title: string
  description: string
  backTo?: string
}>()
</script>

<template>
  <div class="mb-8">
    <NuxtLink
      :to="backTo || '/admin'"
      class="inline-flex items-center gap-2 text-cyan hover:text-cyan-light mb-4 transition-colors"
    >
      <Icon name="heroicons:arrow-left" class="w-5 h-5" />
      Back to Admin Dashboard
    </NuxtLink>
    <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
      {{ title }}
    </h1>
    <p class="text-gray-300">{{ description }}</p>
  </div>
</template>

