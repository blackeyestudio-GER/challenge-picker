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
  <div class="admin-empty-state">
    <Icon :name="icon || 'heroicons:magnifying-glass'" class="admin-empty-state__icon" />
    <p class="admin-empty-state__message">{{ message }}</p>
    <button
      v-if="searchQuery"
      class="admin-empty-state__clear-button"
      @click="$emit('clearSearch')"
    >
      Clear search
    </button>
  </div>
</template>

