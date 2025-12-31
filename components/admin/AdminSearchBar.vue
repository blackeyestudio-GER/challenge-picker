<script setup lang="ts">
import { Icon } from '#components'

const props = defineProps<{
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
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      type="text"
      :placeholder="placeholder || 'Search...'"
      class="admin-search-bar__input"
    />
    <button
      v-if="modelValue"
      @click="clearSearch"
      class="admin-search-bar__clear"
    >
      <Icon name="heroicons:x-mark" class="admin-search-bar__clear-icon" />
    </button>
  </div>
</template>

