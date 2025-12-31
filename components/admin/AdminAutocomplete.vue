<script setup lang="ts" generic="T extends { id: number; name: string }">
import { ref, computed, onBeforeUnmount } from 'vue'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption } from '@headlessui/vue'
import { Icon } from '#components'

interface Props {
  modelValue: number | null
  options: T[]
  placeholder?: string
  label?: string
  nullable?: boolean
  emptyMessage?: string
  allOptionLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Search...',
  label: 'Select',
  nullable: true,
  emptyMessage: 'No results found',
  allOptionLabel: 'All'
})

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
}>()

const query = ref('')
const debouncedQuery = ref('')
let debounceTimeout: ReturnType<typeof setTimeout> | null = null

const selectedItem = computed(() => {
  if (props.modelValue === null) return null
  return props.options.find(item => item.id === props.modelValue) || null
})

const filteredOptions = computed(() => {
  if (debouncedQuery.value === '') {
    return props.options
  }
  return props.options.filter((item) => {
    return item.name.toLowerCase().includes(debouncedQuery.value.toLowerCase())
  })
})

const updateQuery = (value: string) => {
  query.value = value
  
  // Clear existing timeout
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }
  
  // Set new timeout for 500ms
  debounceTimeout = setTimeout(() => {
    debouncedQuery.value = value
  }, 500)
}

const handleSelect = (item: T | null) => {
  emit('update:modelValue', item?.id ?? null)
  query.value = ''
  debouncedQuery.value = ''
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }
}

// Cleanup on unmount
onBeforeUnmount(() => {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }
})
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-300 mb-2">
      {{ label }}
    </label>
    <Combobox :model-value="selectedItem" @update:model-value="handleSelect" nullable>
      <div class="relative">
        <div class="relative">
          <ComboboxInput
            class="w-full px-4 py-2 pr-10 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-cyan transition"
            :display-value="(item: any) => item?.name ?? ''"
            :placeholder="placeholder"
            @change="updateQuery($event.target.value)"
          />
          <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-3">
            <Icon name="heroicons:chevron-up-down" class="w-5 h-5 text-gray-400" />
          </ComboboxButton>
        </div>

        <ComboboxOptions
          class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-gray-800 border-2 border-cyan shadow-2xl py-1 focus:outline-none"
        >
          <!-- "All" option if nullable -->
          <ComboboxOption
            v-if="nullable"
            :value="null"
            v-slot="{ active, selected }"
            class="cursor-pointer"
          >
            <div
              :class="[
                'relative px-4 py-2 text-white',
                active ? 'bg-cyan text-white' : 'hover:bg-gray-700'
              ]"
            >
              <span :class="['block truncate', selected ? 'font-semibold' : 'font-normal']">
                {{ allOptionLabel }}
              </span>
              <span
                v-if="selected"
                class="absolute inset-y-0 right-0 flex items-center pr-4"
                :class="active ? 'text-white' : 'text-cyan'"
              >
                <Icon name="heroicons:check" class="w-5 h-5" />
              </span>
            </div>
          </ComboboxOption>

          <!-- Filtered options -->
          <ComboboxOption
            v-for="item in filteredOptions"
            :key="item.id"
            :value="item"
            v-slot="{ active, selected }"
            class="cursor-pointer"
          >
            <div
              :class="[
                'relative px-4 py-2 text-white',
                active ? 'bg-cyan text-white' : 'hover:bg-gray-700'
              ]"
            >
              <span :class="['block truncate', selected ? 'font-semibold' : 'font-normal']">
                {{ item.name }}
              </span>
              <span
                v-if="selected"
                class="absolute inset-y-0 right-0 flex items-center pr-4"
                :class="active ? 'text-white' : 'text-cyan'"
              >
                <Icon name="heroicons:check" class="w-5 h-5" />
              </span>
            </div>
          </ComboboxOption>

          <!-- Empty state -->
          <div
            v-if="filteredOptions.length === 0 && debouncedQuery !== ''"
            class="px-4 py-2 text-gray-400 text-sm"
          >
            {{ emptyMessage }}
          </div>
        </ComboboxOptions>
      </div>
    </Combobox>
  </div>
</template>

