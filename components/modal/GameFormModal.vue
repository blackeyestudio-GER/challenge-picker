<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import type { AdminGame, CreateGameRequest, AdminCategory } from '~/composables/useAdmin'
import { useAdmin } from '~/composables/useAdmin'
import { Icon } from '#components'

interface Props {
  show: boolean
  editingGame: AdminGame | null
  loading?: boolean
}

interface Emits {
  (e: 'close' | 'deactivate'): void
  (e: 'submit', data: CreateGameRequest & { id?: number; categoryIds?: number[] }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const { fetchAdminCategories } = useAdmin()
const { warning, notifyApiError } = useNotify()

const uploadingImage = ref(false)
const categories = ref<AdminCategory[]>([])
const categorySearchQuery = ref('')
const formData = ref<CreateGameRequest & { id?: number; categoryIds?: number[] }>({
  name: '',
  description: '',
  image: '',
  categoryIds: [],
  isCategoryRepresentative: false,
  steamLink: '',
  epicLink: '',
  gogLink: '',
  twitchCategory: ''
})

// Computed filtered categories
const filteredCategories = computed(() => {
  let availableCategories = categories.value
  
  // If this game is a category representative, exclude the category it represents
  if (props.editingGame?.isCategoryRepresentative) {
    availableCategories = categories.value.filter(category => 
      category.name !== props.editingGame?.name
    )
  }
  
  // Apply search filter
  if (!categorySearchQuery.value.trim()) {
    return availableCategories
  }
  
  const query = categorySearchQuery.value.toLowerCase()
  return availableCategories.filter(category =>
    category.name.toLowerCase().includes(query) ||
    category.slug.toLowerCase().includes(query)
  )
})

// Fetch categories on mount
onMounted(async () => {
  try {
    categories.value = await fetchAdminCategories()
  } catch (err) {
    notifyApiError(err, 'Failed to load categories')
  }
})

// Watch for changes to editingGame and update formData
watch(() => props.editingGame, (game) => {
  categorySearchQuery.value = '' // Clear search on modal open/edit
  if (game) {
    formData.value = {
      id: game.id,
      name: game.name,
      description: game.description || '',
      image: game.image || '',
      categoryIds: game.categories.map(c => c.id),
      isCategoryRepresentative: game.isCategoryRepresentative,
      steamLink: game.steamLink || '',
      epicLink: game.epicLink || '',
      gogLink: game.gogLink || '',
      twitchCategory: game.twitchCategory || ''
    }
  } else {
    formData.value = {
      name: '',
      description: '',
      image: '',
      categoryIds: [],
      isCategoryRepresentative: false,
      steamLink: '',
      epicLink: '',
      gogLink: '',
      twitchCategory: ''
    }
  }
}, { immediate: true })

const resizeAndCompressImage = (file: File, size: number, quality: number): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        canvas.width = size
        canvas.height = size
        const ctx = canvas.getContext('2d')
        
        if (!ctx) {
          reject(new Error('Failed to get canvas context'))
          return
        }
        
        const sourceSize = Math.min(img.width, img.height)
        const offsetX = (img.width - sourceSize) / 2
        const offsetY = (img.height - sourceSize) / 2
        
        ctx.imageSmoothingEnabled = true
        ctx.imageSmoothingQuality = 'high'
        ctx.drawImage(img, offsetX, offsetY, sourceSize, sourceSize, 0, 0, size, size)
        
        const base64 = canvas.toDataURL('image/jpeg', quality)
        resolve(base64)
      }
      img.onerror = () => reject(new Error('Failed to load image'))
      img.src = e.target?.result as string
    }
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.readAsDataURL(file)
  })
}

const handleImageSelect = async (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files || !input.files[0]) return

  const file = input.files[0]
  
  if (!file.type.startsWith('image/')) {
    warning('Please select an image file')
    return
  }

  if (file.size > 10 * 1024 * 1024) {
    warning('Image must be smaller than 10MB')
    return
  }

  uploadingImage.value = true

  try {
    const base64 = await resizeAndCompressImage(file, 256, 0.85)
    formData.value.image = base64
  } catch (err) {
    notifyApiError(err, 'Failed to process image. Please try a different file.')
  } finally {
    uploadingImage.value = false
  }
}

const removeImage = () => {
  formData.value.image = ''
}

const handleSubmit = () => {
  emit('submit', formData.value)
}

const handleClose = () => {
  emit('close')
}

const handleDeactivate = () => {
  emit('deactivate')
}
</script>

<template>
  <div v-if="show" class="admin-modal-backdrop z-50" @click.self="handleClose">
    <div class="admin-modal-surface max-w-3xl">
      <div class="admin-modal-header">
        <h2 class="admin-modal-title">
          {{ editingGame ? 'Edit Game' : 'Create Game' }}
        </h2>
        <button class="admin-modal-close" @click="handleClose">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form class="admin-modal-body space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">
            Game Cover Image
            <span class="text-[var(--color-text-muted)] font-normal ml-2">(Auto-resized to 256×256px square)</span>
          </label>
          
          <div v-if="formData.image" class="mb-4">
            <div class="relative inline-block">
              <img
                :src="formData.image"
                alt="Preview"
                class="w-48 h-48 object-cover rounded-xl border border-[var(--color-border-secondary)]"
              >
              <button
                type="button"
                class="btn btn-danger absolute top-2 right-2 p-2 rounded-full"
                title="Remove image"
                @click="removeImage"
              >
                <Icon name="heroicons:trash" class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <div class="flex items-center gap-3">
            <label class="cursor-pointer btn btn-secondary flex items-center gap-2">
              <Icon name="heroicons:arrow-up-tray" class="w-5 h-5" />
              {{ formData.image ? 'Change Image' : 'Upload Image' }}
              <input
                type="file"
                accept="image/*"
                class="hidden"
                :disabled="uploadingImage"
                @change="handleImageSelect"
              >
            </label>
            <span v-if="uploadingImage" class="text-sm text-[var(--color-text-muted)]">Processing image...</span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            placeholder="Enter game name"
          >
        </div>
        
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            placeholder="Enter game description"
          />
        </div>
        
        <!-- Categories -->
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Categories</label>
          
          <!-- Search Input -->
          <div class="mb-2">
            <input
              v-model="categorySearchQuery"
              type="text"
              placeholder="Search categories..."
              class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
            >
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-3 bg-[var(--color-bg-overlay)] rounded-xl border border-[var(--color-border-secondary)]">
            <label
              v-for="category in filteredCategories"
              :key="category.id"
              class="flex items-center gap-2 cursor-pointer hover:bg-[var(--color-bg-card-hover)] p-2 rounded-lg transition"
            >
              <input
                v-model="formData.categoryIds"
                type="checkbox"
                :value="category.id"
                class="w-4 h-4 rounded bg-[var(--color-bg-card)] border-[var(--color-border-secondary)] text-[var(--color-accent-primary)] focus:ring-[var(--color-accent-primary)]"
              >
              <span class="text-sm text-[var(--color-text-secondary)]">{{ category.name }}</span>
            </label>
            <div v-if="filteredCategories.length === 0" class="col-span-full text-center text-[var(--color-text-muted)] text-sm py-4">
              No categories found
            </div>
          </div>
          <p class="text-xs text-[var(--color-text-muted)] mt-1">
            {{ formData.categoryIds?.length || 0 }} categories selected
            <span v-if="categorySearchQuery" class="text-[var(--color-text-secondary)]">({{ filteredCategories.length }} shown)</span>
          </p>
        </div>
        
        <!-- Store Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Steam Link</label>
            <input
              v-model="formData.steamLink"
              type="url"
              class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
              placeholder="https://store.steampowered.com/app/..."
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Epic Games Link</label>
            <input
              v-model="formData.epicLink"
              type="url"
              class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
              placeholder="https://store.epicgames.com/..."
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">GOG Link</label>
            <input
              v-model="formData.gogLink"
              type="url"
              class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
              placeholder="https://www.gog.com/game/..."
            >
          </div>
          
        </div>
        
        <!-- Category Representative -->
        <div class="flex items-center gap-3">
          <input
            id="categoryRep"
            v-model="formData.isCategoryRepresentative"
            type="checkbox"
            class="w-4 h-4 rounded bg-[var(--color-bg-card)] border-[var(--color-border-secondary)] text-[var(--color-accent-primary)] focus:ring-[var(--color-accent-primary)]"
          >
          <label for="categoryRep" class="text-sm font-medium text-[var(--color-text-secondary)]">
            Category Representative (won't appear in voting/selection)
          </label>
        </div>
        
        <!-- Submit Buttons -->
        <div class="flex justify-between items-center gap-3 pt-4">
          <!-- Deactivate button (only shown when editing) -->
          <button
            v-if="editingGame"
            type="button"
            class="btn btn-danger flex items-center gap-2"
            @click="handleDeactivate"
          >
            <Icon name="heroicons:archive-box-x-mark" class="w-5 h-5" />
            Deactivate Game
          </button>
          <div v-else/>
          
          <!-- Right side buttons -->
          <div class="flex gap-3">
            <button
              type="button"
              class="btn btn-secondary px-6 py-2"
              @click="handleClose"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="uploadingImage || loading"
              class="btn btn-primary px-6 py-2 font-bold disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ editingGame ? 'Update Game' : 'Create Game' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
