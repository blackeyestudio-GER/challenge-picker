<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Icon } from '#components'

interface FormData {
  name: string
  description: string
  type: 'full' | 'template'
  isFree: boolean
  price: string
  theme: string
}

interface Props {
  show: boolean
  loading?: boolean
  editMode?: boolean
  initialData?: Partial<FormData> & { designName?: string }
}

interface Emits {
  (e: 'close'): void
  (e: 'submit', data: FormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formData = ref<FormData>({
  name: '',
  description: '',
  type: 'full',
  isFree: true,
  price: '',
  theme: ''
})

const cardCountText = computed(() => {
  return formData.value.type === 'template' ? '3 template frames' : '78 complete cards'
})

watch(() => props.show, (isShown) => {
  if (isShown) {
    if (props.editMode && props.initialData) {
      // Edit mode: populate with existing data
      // Support both 'designName' (from DesignSet) and 'name' (from FormData)
      formData.value = {
        name: props.initialData.designName || props.initialData.name || '',
        description: props.initialData.description || '',
        type: props.initialData.type || 'full',
        isFree: props.initialData.isFree ?? true,
        price: props.initialData.price || '',
        theme: props.initialData.theme || ''
      }
    } else {
      // Create mode: reset to defaults
      formData.value = {
        name: '',
        description: '',
        type: 'full',
        isFree: true,
        price: '',
        theme: ''
      }
    }
  }
})

const handleSubmit = () => {
  emit('submit', formData.value)
}

const handleClose = () => {
  emit('close')
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4" @click.self="handleClose">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-md w-full border border-gray-700">
      <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-white">
          <template v-if="editMode && (initialData?.designName || initialData?.name)">
            Edit Design Set: <span class="text-cyan">{{ initialData?.designName || initialData?.name }}</span>
          </template>
          <template v-else>
            {{ editMode ? 'Edit Design Set' : 'New Design Set' }}
          </template>
        </h2>
        <button @click="handleClose" class="text-gray-400 hover:text-white" :disabled="loading">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Design Set Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            :disabled="loading"
            placeholder="e.g., Gothic, Cyberpunk, Horror"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50"
          />
          <p class="text-xs text-gray-400 mt-1">Unique identifier for this design set (can be changed anytime)</p>
        </div>

        <div v-if="!editMode">
          <label class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              @click="formData.type = 'full'"
              :class="[
                'px-4 py-3 rounded-lg border-2 transition-all text-left',
                formData.type === 'full'
                  ? 'border-cyan bg-cyan/10 text-white'
                  : 'border-gray-600 bg-gray-900 text-gray-400 hover:border-gray-500'
              ]"
              :disabled="loading"
            >
              <div class="font-semibold mb-1">Full Set</div>
              <div class="text-xs opacity-75">78 complete card artworks</div>
            </button>
            <button
              type="button"
              @click="formData.type = 'template'"
              :class="[
                'px-4 py-3 rounded-lg border-2 transition-all text-left',
                formData.type === 'template'
                  ? 'border-magenta bg-magenta/10 text-white'
                  : 'border-gray-600 bg-gray-900 text-gray-400 hover:border-gray-500'
              ]"
              :disabled="loading"
            >
              <div class="font-semibold mb-1">Template</div>
              <div class="text-xs opacity-75">3 frames + icon composition</div>
            </button>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            :disabled="loading"
            placeholder="Describe this design set..."
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50"
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Theme</label>
          <input
            v-model="formData.theme"
            type="text"
            :disabled="loading"
            placeholder="e.g., horror, cyberpunk, fantasy"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50"
          />
          <p class="text-xs text-gray-400 mt-1">Used for filtering and categorization</p>
        </div>

        <div class="border-t border-gray-700 pt-4">
          <label class="block text-sm font-medium text-gray-300 mb-2">Price (USD)</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
            <input
              v-model="formData.price"
              type="number"
              step="0.01"
              min="0"
              :disabled="loading"
              placeholder="2.99"
              class="w-full pl-8 pr-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50"
            />
          </div>
          <p class="text-xs text-gray-400 mt-1">Set the price for this design set</p>
        </div>

        <div class="border-t border-gray-700 pt-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              type="checkbox"
              v-model="formData.isFree"
              :disabled="loading"
              class="w-5 h-5 rounded border-gray-600 bg-gray-900 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800"
            />
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-300">Currently Free</div>
              <div class="text-xs text-gray-400">
                Make this design set free for all users. Price is preserved and can be re-enabled later.
              </div>
            </div>
          </label>
        </div>

        <div v-if="!editMode" class="bg-gray-900/50 border border-gray-700 rounded-lg p-3">
          <p class="text-sm text-gray-300">
            <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
            This will create a set with <strong>{{ cardCountText }}</strong>
          </p>
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="handleClose"
            :disabled="loading"
            class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-2 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all disabled:opacity-50 flex items-center gap-2"
          >
            <span v-if="loading">{{ editMode ? 'Saving...' : 'Creating...' }}</span>
            <span v-else>{{ editMode ? 'Save Changes' : 'Create & Edit Cards' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
