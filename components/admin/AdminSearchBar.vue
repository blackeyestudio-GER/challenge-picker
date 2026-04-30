<script setup lang="ts">
import { Icon } from '#components'

defineProps<{
  modelValue: string
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const clearSearch = () => {
  emit('update:modelValue', '')
}
</script>

<template>
  <div class="admin-search-bar">
    <Icon name="heroicons:magnifying-glass" class="admin-search-bar__icon" />
    <input
      :value="modelValue"
      type="text"
      :placeholder="placeholder || 'Search...'"
      class="admin-search-bar__input"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    >
    <button
      v-if="modelValue"
      class="admin-search-bar__clear"
      @click="clearSearch"
    >
      <Icon name="heroicons:x-mark" class="admin-search-bar__clear-icon" />
    </button>
  </div>
</template>
