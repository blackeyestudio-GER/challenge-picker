<script setup lang="ts" generic="T extends { id: number; name: string; [key: string]: any }">
import { ref, computed } from 'vue'
import { Icon } from '#components'

interface Props {
  modelValue: Set<number>
  options: T[]
  label?: string
  placeholder?: string
  emptyMessage?: string
  maxHeight?: string
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Select items',
  placeholder: 'Type to search...',
  emptyMessage: 'No items found',
  maxHeight: 'max-h-60'
})

const emit = defineEmits<{
  'update:modelValue': [value: Set<number>]
}>()

const searchQuery = ref('')
const showDropdown = ref(false)

const selectedItems = computed(() => {
  return props.options.filter(item => props.modelValue.has(item.id))
})

const filteredOptions = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.options
  }
  return props.options.filter(item => 
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const toggleItem = (id: number) => {
  const newSet = new Set(props.modelValue)
  if (newSet.has(id)) {
    newSet.delete(id)
  } else {
    newSet.add(id)
  }
  emit('update:modelValue', newSet)
}

const removeItem = (id: number) => {
  const newSet = new Set(props.modelValue)
  newSet.delete(id)
  emit('update:modelValue', newSet)
}

const clearAll = () => {
  emit('update:modelValue', new Set())
}
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-300 mb-2">
      {{ label }}
    </label>
    
    <!-- Selected Items (Tags) -->
    <div v-if="selectedItems.length > 0" class="flex flex-wrap gap-2 mb-2">
      <div
        v-for="item in selectedItems"
        :key="item.id"
        class="inline-flex items-center gap-1 px-3 py-1 bg-cyan/20 border border-cyan rounded-full text-cyan text-sm"
      >
        <span>{{ item.name }}</span>
        <button
          @click="removeItem(item.id)"
          class="hover:bg-cyan/30 rounded-full p-0.5 transition"
        >
          <Icon name="heroicons:x-mark" class="w-4 h-4" />
        </button>
      </div>
      <button
        @click="clearAll"
        class="inline-flex items-center gap-1 px-3 py-1 bg-red-600/20 border border-red-600 rounded-full text-red-400 text-sm hover:bg-red-600/30 transition"
      >
        Clear All
        <Icon name="heroicons:x-mark" class="w-4 h-4" />
      </button>
    </div>

    <!-- Search Input with Dropdown -->
    <div class="relative">
      <div class="relative">
        <Icon name="heroicons:magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input
          v-model="searchQuery"
          @focus="showDropdown = true"
          @blur="setTimeout(() => showDropdown = false, 200)"
          type="text"
          :placeholder="placeholder"
          class="w-full pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-cyan transition"
        />
        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">
          {{ modelValue.size }} selected
        </div>
      </div>

      <!-- Dropdown Options -->
      <div
        v-if="showDropdown && filteredOptions.length > 0"
        :class="['absolute z-50 mt-1 w-full overflow-auto rounded-lg bg-gray-800 border-2 border-cyan shadow-2xl py-1', maxHeight]"
      >
        <button
          v-for="item in filteredOptions"
          :key="item.id"
          @click="toggleItem(item.id)"
          class="w-full px-4 py-2 text-left hover:bg-gray-700 transition flex items-center gap-2 text-white"
        >
          <Icon 
            :name="modelValue.has(item.id) ? 'heroicons:check-circle' : 'heroicons:circle'" 
            class="w-5 h-5"
            :class="modelValue.has(item.id) ? 'text-cyan' : 'text-gray-600'"
          />
          <span class="flex-1">{{ item.name }}</span>
          <slot name="item-badge" :item="item"></slot>
        </button>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="showDropdown && filteredOptions.length === 0"
        class="absolute z-50 mt-1 w-full rounded-lg bg-gray-800 border border-gray-700 shadow-lg py-4 px-4"
      >
        <p class="text-sm text-gray-400 text-center">{{ emptyMessage }}</p>
      </div>
    </div>
  </div>
</template>

