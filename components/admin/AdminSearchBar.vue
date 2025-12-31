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
  <div class="relative mb-6">
    <Icon name="heroicons:magnifying-glass" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      type="text"
      :placeholder="placeholder || 'Search...'"
      class="w-full pl-12 pr-12 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan focus:border-transparent"
    />
    <button
      v-if="modelValue"
      @click="clearSearch"
      class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition"
    >
      <Icon name="heroicons:x-mark" class="w-5 h-5" />
    </button>
  </div>
</template>

