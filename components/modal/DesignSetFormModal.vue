<script setup lang="ts">
import { ref, watch } from 'vue'
import { Icon } from '#components'

interface FormData {
  name: string
  description: string
}

interface Props {
  show: boolean
  loading?: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'submit', data: FormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formData = ref<FormData>({
  name: '',
  description: ''
})

watch(() => props.show, (isShown) => {
  if (isShown) {
    formData.value = {
      name: '',
      description: ''
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
        <h2 class="text-2xl font-bold text-white">New Design Set</h2>
        <button @click="handleClose" class="text-gray-400 hover:text-white" :disabled="loading">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Design Set Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            :disabled="loading"
            placeholder="e.g., Gothic, Modern, Classic"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            :disabled="loading"
            placeholder="Optional description of this design set"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan disabled:opacity-50"
          ></textarea>
        </div>

        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-3">
          <p class="text-sm text-gray-300">
            <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
            This will create a set with 78 empty card slots
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
            <span v-if="loading">Creating...</span>
            <span v-else>Create & Edit Cards</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

