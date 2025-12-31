<script setup lang="ts">
import { Icon } from '#components'

defineProps<{
  icon?: string
  message: string
  searchQuery?: string
}>()

defineEmits<{
  clearSearch: []
}>()
</script>

<template>
  <div class="text-center py-12">
    <Icon :name="icon || 'heroicons:magnifying-glass'" class="w-16 h-16 mx-auto text-gray-600 mb-4" />
    <p class="text-gray-400 text-lg">{{ message }}</p>
    <button
      v-if="searchQuery"
      @click="$emit('clearSearch')"
      class="mt-4 text-cyan hover:text-cyan-light underline"
    >
      Clear search
    </button>
  </div>
</template>

