<script setup lang="ts">
definePageMeta({
  middleware: ['auth', 'admin']
})

const { games, loading, fetchGames, createGame, updateGame } = useGames()

// Form state
const showForm = ref(false)
const editingGame = ref<number | null>(null)
const formData = ref({
  name: '',
  description: '',
  image: ''
})
const imagePreview = ref<string | null>(null)
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

onMounted(async () => {
  await fetchGames()
})

// Image resizing function (256x256)
const resizeImage = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        canvas.width = 256
        canvas.height = 256
        const ctx = canvas.getContext('2d')!
        
        // Draw the image scaled to 256x256
        ctx.drawImage(img, 0, 0, 256, 256)
        
        // Convert to base64
        const base64 = canvas.toDataURL('image/jpeg', 0.9)
        resolve(base64)
      }
      img.onerror = reject
      img.src = e.target?.result as string
    }
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

const handleImageUpload = async (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return

  const file = input.files[0]
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Please select an image file'
    return
  }

  // Validate file size (max 5MB before resizing)
  if (file.size > 5 * 1024 * 1024) {
    errorMessage.value = 'Image size must be less than 5MB'
    return
  }

  try {
    const resizedBase64 = await resizeImage(file)
    formData.value.image = resizedBase64
    imagePreview.value = resizedBase64
    errorMessage.value = ''
  } catch (err) {
    console.error('Failed to process image:', err)
    errorMessage.value = 'Failed to process image'
  }
}

const openCreateForm = () => {
  formData.value = { name: '', description: '', image: '' }
  imagePreview.value = null
  editingGame.value = null
  showForm.value = true
  errorMessage.value = ''
  successMessage.value = ''
}

const openEditForm = (game: any) => {
  formData.value = {
    name: game.name,
    description: game.description || '',
    image: game.image || ''
  }
  imagePreview.value = game.image || null
  editingGame.value = game.id
  showForm.value = true
  errorMessage.value = ''
  successMessage.value = ''
}

const closeForm = () => {
  showForm.value = false
  formData.value = { name: '', description: '', image: '' }
  imagePreview.value = null
  editingGame.value = null
}

const submitForm = async () => {
  if (!formData.value.name.trim()) {
    errorMessage.value = 'Game name is required'
    return
  }

  submitting.value = true
  errorMessage.value = ''

  try {
    if (editingGame.value) {
      await updateGame(editingGame.value, formData.value)
      successMessage.value = 'Game updated successfully!'
    } else {
      await createGame(formData.value)
      successMessage.value = 'Game created successfully!'
    }
    
    closeForm()
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err: any) {
    errorMessage.value = err.data?.error?.message || 'Failed to save game'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 py-8 px-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <NuxtLink to="/dashboard" class="text-gray-400 hover:text-white mb-4 inline-flex items-center gap-2">
          ← Back to Dashboard
        </NuxtLink>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">Manage Games</h1>
            <p class="text-gray-300">Create and edit game entries with images</p>
          </div>
          <button
            @click="openCreateForm"
            class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white rounded-lg hover:opacity-90 transition font-medium"
          >
            + Add New Game
          </button>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-300">{{ successMessage }}</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !showForm" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan"></div>
        <p class="text-white mt-4">Loading games...</p>
      </div>

      <!-- Games Grid -->
      <div v-else-if="!showForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="game in games"
          :key="game.id"
          class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-cyan hover:shadow-xl hover:shadow-cyan/20 transition-all"
        >
          <div v-if="game.image" class="mb-4 h-48 flex items-center justify-center bg-gray-900 rounded">
            <img :src="game.image" :alt="game.name" class="max-h-full max-w-full object-contain" />
          </div>
          <div v-else class="mb-4 h-48 flex items-center justify-center bg-gray-900 rounded">
            <span class="text-6xl">🎮</span>
          </div>
          <h3 class="text-xl font-bold text-white mb-2">{{ game.name }}</h3>
          <p v-if="game.description" class="text-gray-400 text-sm mb-4">{{ game.description }}</p>
          <div class="flex items-center justify-between">
            <span class="text-cyan text-sm">{{ game.rulesetCount }} rulesets</span>
            <button
              @click="openEditForm(game)"
              class="px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg hover:bg-gray-600 transition text-sm"
            >
              Edit
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="games.length === 0" class="col-span-full text-center py-12">
          <div class="text-6xl mb-4">🎮</div>
          <p class="text-gray-400 mb-4">No games yet. Create your first game!</p>
          <button
            @click="openCreateForm"
            class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white rounded-lg hover:opacity-90 transition font-medium"
          >
            + Add New Game
          </button>
        </div>
      </div>

      <!-- Create/Edit Form -->
      <div v-if="showForm" class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-8">
        <h2 class="text-2xl font-bold text-white mb-6">
          {{ editingGame ? 'Edit Game' : 'Create New Game' }}
        </h2>

        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
          <p class="text-red-300">{{ errorMessage }}</p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Game Name -->
          <div>
            <label class="block text-white font-medium mb-2">Game Name *</label>
            <input
              v-model="formData.name"
              type="text"
              placeholder="Enter game name"
              class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan"
              required
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-white font-medium mb-2">Description (Optional)</label>
            <textarea
              v-model="formData.description"
              placeholder="Enter game description"
              rows="3"
              class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan resize-none"
            ></textarea>
          </div>

          <!-- Image Upload -->
          <div>
            <label class="block text-white font-medium mb-2">Game Image (Optional)</label>
            <p class="text-gray-400 text-sm mb-3">Image will be resized to 256x256px and saved as base64</p>
            
            <div class="flex items-start gap-4">
              <!-- Current/Preview Image -->
              <div v-if="imagePreview" class="flex-shrink-0">
                <div class="w-32 h-32 bg-gray-900 border-2 border-gray-600 rounded-lg overflow-hidden">
                  <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                </div>
              </div>

              <!-- Upload Button -->
              <div class="flex-1">
                <input
                  type="file"
                  accept="image/*"
                  @change="handleImageUpload"
                  class="hidden"
                  id="game-image-upload"
                />
                <label
                  for="game-image-upload"
                  class="inline-block px-6 py-3 bg-gray-700 border border-gray-600 text-white rounded-lg hover:bg-gray-600 transition cursor-pointer"
                >
                  {{ imagePreview ? 'Change Image' : 'Upload Image' }}
                </label>
                <p class="text-gray-500 text-sm mt-2">Supported: JPG, PNG, GIF, WebP (max 5MB)</p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-4 pt-4">
            <button
              type="button"
              @click="closeForm"
              class="px-6 py-3 bg-gray-700 border border-gray-600 text-white rounded-lg hover:bg-gray-600 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white rounded-lg hover:opacity-90 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Saving...' : editingGame ? 'Update Game' : 'Create Game' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

