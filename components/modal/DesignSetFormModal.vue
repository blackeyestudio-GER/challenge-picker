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
  <div v-if="show" class="admin-modal-backdrop z-50" @click.self="handleClose">
    <div class="admin-modal-surface max-w-md">
      <div class="admin-modal-header">
        <h2 class="text-2xl font-bold text-[var(--color-text-primary)]">
          <template v-if="editMode && (initialData?.designName || initialData?.name)">
            Edit Design Set: <span class="text-[var(--color-accent-primary)]">{{ initialData?.designName || initialData?.name }}</span>
          </template>
          <template v-else>
            {{ editMode ? 'Edit Design Set' : 'New Design Set' }}
          </template>
        </h2>
        <button class="admin-modal-close" :disabled="loading" @click="handleClose">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>

      <form class="admin-modal-body space-y-4 max-h-[80vh] overflow-y-auto" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Design Set Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            :disabled="loading"
            placeholder="e.g., Gothic, Cyberpunk, Horror"
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] disabled:opacity-50"
          >
          <p class="text-xs text-[var(--color-text-muted)] mt-1">Unique identifier for this design set (can be changed anytime)</p>
        </div>

        <div v-if="!editMode">
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Type *</label>
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              :class="[
                'px-4 py-3 rounded-xl border-2 transition-all text-left',
                formData.type === 'full'
                  ? 'border-[var(--color-accent-primary)] bg-[color-mix(in_srgb,var(--color-accent-primary)_10%,transparent)] text-[var(--color-text-primary)]'
                  : 'border-[var(--color-border-secondary)] bg-[var(--color-bg-card)] text-[var(--color-text-muted)] hover:border-[var(--color-border-primary)]'
              ]"
              :disabled="loading"
              @click="formData.type = 'full'"
            >
              <div class="font-semibold mb-1">Full Set</div>
              <div class="text-xs opacity-75">78 complete card artworks</div>
            </button>
            <button
              type="button"
              :class="[
                'px-4 py-3 rounded-xl border-2 transition-all text-left',
                formData.type === 'template'
                  ? 'border-[var(--color-accent-secondary)] bg-[color-mix(in_srgb,var(--color-accent-secondary)_12%,transparent)] text-[var(--color-text-primary)]'
                  : 'border-[var(--color-border-secondary)] bg-[var(--color-bg-card)] text-[var(--color-text-muted)] hover:border-[var(--color-border-primary)]'
              ]"
              :disabled="loading"
              @click="formData.type = 'template'"
            >
              <div class="font-semibold mb-1">Template</div>
              <div class="text-xs opacity-75">3 frames + icon composition</div>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            :disabled="loading"
            placeholder="Describe this design set..."
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] disabled:opacity-50"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Theme</label>
          <input
            v-model="formData.theme"
            type="text"
            :disabled="loading"
            placeholder="e.g., horror, cyberpunk, fantasy"
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] disabled:opacity-50"
          >
          <p class="text-xs text-[var(--color-text-muted)] mt-1">Used for filtering and categorization</p>
        </div>

        <div class="border-t border-[var(--color-border-secondary)] pt-4">
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Price (USD)</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--color-text-muted)]">$</span>
            <input
              v-model="formData.price"
              type="number"
              step="0.01"
              min="0"
              :disabled="loading"
              placeholder="2.99"
              class="w-full pl-8 pr-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] disabled:opacity-50"
            >
          </div>
          <p class="text-xs text-[var(--color-text-muted)] mt-1">Set the price for this design set</p>
        </div>

        <div class="border-t border-[var(--color-border-secondary)] pt-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="formData.isFree"
              type="checkbox"
              :disabled="loading"
              class="w-5 h-5 rounded border-[var(--color-border-secondary)] bg-[var(--color-bg-card)] text-[var(--status-active-text)] focus:ring-[var(--status-active-text)]"
            >
            <div class="flex-1">
              <div class="text-sm font-medium text-[var(--color-text-secondary)]">Currently Free</div>
              <div class="text-xs text-[var(--color-text-muted)]">
                Make this design set free for all users. Price is preserved and can be re-enabled later.
              </div>
            </div>
          </label>
        </div>

        <div v-if="!editMode" class="bg-[var(--color-bg-overlay)] border border-[var(--color-border-secondary)] rounded-xl p-3">
          <p class="text-sm text-[var(--color-text-secondary)]">
            <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
            This will create a set with <strong>{{ cardCountText }}</strong>
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button type="button" :disabled="loading" class="btn btn-secondary px-6 py-2 disabled:opacity-50" @click="handleClose">
            Cancel
          </button>
          <button type="submit" :disabled="loading" class="btn btn-primary px-6 py-2 font-bold disabled:opacity-50 flex items-center gap-2">
            <span v-if="loading">{{ editMode ? 'Saving...' : 'Creating...' }}</span>
            <span v-else>{{ editMode ? 'Save Changes' : 'Create & Edit Cards' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
