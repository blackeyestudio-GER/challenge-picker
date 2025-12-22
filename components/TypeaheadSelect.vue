<script setup lang="ts" generic="T extends { id: number; name: string }">
import { ref, computed } from 'vue'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption, TransitionRoot } from '@headlessui/vue'
import { Icon } from '#components'

interface Props {
  modelValue: number | null
  options: T[]
  placeholder?: string
  disabled?: boolean
  label?: string
  required?: boolean
  getDisplayValue?: (option: T) => string
  getSearchValue?: (option: T) => string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option...',
  disabled: false,
  label: '',
  required: false,
  getDisplayValue: (option: T) => option.name,
  getSearchValue: (option: T) => option.name
})

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
}>()

const query = ref('')

const selectedOption = computed(() => {
  return props.options.find(o => o.id === props.modelValue) || null
})

const filteredOptions = computed(() => {
  if (query.value === '') {
    return props.options
  }
  
  return props.options.filter((option) => {
    const searchValue = props.getSearchValue(option).toLowerCase()
    return searchValue.includes(query.value.toLowerCase())
  })
})

const handleSelect = (option: T | null) => {
  emit('update:modelValue', option?.id || null)
  query.value = ''
}

const displayValue = (option: T | null) => {
  return option ? props.getDisplayValue(option) : ''
}
</script>

<template>
  <div class="w-full">
    <Combobox
      :model-value="selectedOption"
      @update:model-value="handleSelect"
      :disabled="disabled"
    >
      <div class="relative">
        <div class="relative w-full">
          <ComboboxInput
            class="w-full px-4 py-2 pr-10 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50 disabled:cursor-not-allowed"
            :display-value="displayValue"
            @change="query = $event.target.value"
            :placeholder="placeholder"
            :required="required"
          />
          <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-3">
            <Icon
              name="heroicons:chevron-up-down"
              class="w-5 h-5 text-gray-400"
              aria-hidden="true"
            />
          </ComboboxButton>
        </div>
        
        <TransitionRoot
          leave="transition ease-in duration-100"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
          @after-leave="query = ''"
        >
          <ComboboxOptions
            class="absolute z-10 mt-1 w-full max-h-60 overflow-auto rounded-lg bg-gray-900 border border-gray-600 py-1 text-base shadow-lg focus:outline-none"
          >
            <div
              v-if="filteredOptions.length === 0 && query !== ''"
              class="relative cursor-default select-none py-2 px-4 text-gray-400"
            >
              No results found.
            </div>

            <ComboboxOption
              v-for="option in filteredOptions"
              :key="option.id"
              :value="option"
              v-slot="{ selected, active }"
              as="template"
            >
              <li
                :class="[
                  'relative cursor-pointer select-none py-2 px-4 transition',
                  active ? 'bg-cyan/20 text-white' : 'text-gray-300',
                ]"
              >
                <span
                  :class="[
                    'block truncate',
                    selected ? 'font-semibold text-cyan' : 'font-normal'
                  ]"
                >
                  {{ getDisplayValue(option) }}
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-cyan"
                >
                  <Icon name="heroicons:check" class="w-5 h-5" aria-hidden="true" />
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </TransitionRoot>
      </div>
    </Combobox>
  </div>
</template>

