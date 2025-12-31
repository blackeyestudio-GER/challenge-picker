<script setup lang="ts">
import { ref, onMounted, computed, onErrorCaptured } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDesigns, type DesignSet, type CardDesign } from '~/composables/useDesigns'
import { Icon } from '#components'

definePageMeta({
  middleware: 'admin'
})

const route = useRoute()
const router = useRouter()
const { fetchDesignSet, updateCardDesign, loading } = useDesigns()

const designSet = ref<DesignSet | null>(null)
const uploadingCardId = ref<number | null>(null)
const selectedCard = ref<CardDesign | null>(null)
const showImageModal = ref(false)
const pageError = ref<string | null>(null)

const designSetId = computed(() => {
  const id = route.params.id as string
  if (!id || isNaN(parseInt(id))) {
    pageError.value = 'Invalid design set ID'
    return 0
  }
  return parseInt(id)
})

// Capture any errors that occur in the component tree
onErrorCaptured((err, instance, info) => {
  console.error('Component error:', err, info)
  pageError.value = `Component error: ${err.message}`
  return false
})

onMounted(async () => {
  await loadDesignSet()
})

const loadDesignSet = async () => {
  if (designSetId.value === 0) {
    return // Error already set in computed
  }
  
  try {
    designSet.value = await fetchDesignSet(designSetId.value)
    if (!designSet.value) {
      pageError.value = 'Design set not found'
    }
  } catch (err: any) {
    console.error('Failed to load design set:', err)
    const errorMsg = err.data?.error?.message || err.message || 'Failed to load design set'
    pageError.value = errorMsg
  }
}

const resizeAndCompressImage = (file: File, maxWidth: number, maxHeight: number, quality: number): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        // Calculate dimensions maintaining aspect ratio (fit inside maxWidth x maxHeight)
        let width = img.width
        let height = img.height
        
        // Calculate scale to fit within max dimensions while maintaining aspect ratio
        const scaleWidth = maxWidth / width
        const scaleHeight = maxHeight / height
        const scale = Math.min(scaleWidth, scaleHeight, 1) // Don't upscale
        
        width = Math.round(width * scale)
        height = Math.round(height * scale)
        
        // Create canvas and draw resized image
        const canvas = document.createElement('canvas')
        canvas.width = width
        canvas.height = height
        const ctx = canvas.getContext('2d')
        
        if (!ctx) {
          reject(new Error('Failed to get canvas context'))
          return
        }
        
        // Enable image smoothing for better quality
        ctx.imageSmoothingEnabled = true
        ctx.imageSmoothingQuality = 'high'
        
        // Draw the scaled image
        ctx.drawImage(img, 0, 0, width, height)
        
        // Convert to base64 with compression
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

const handleFileSelect = async (cardDesign: CardDesign, event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files || !input.files[0]) return

  const file = input.files[0]
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Please select an image file')
    return
  }

  // Validate file size (max 10MB for original)
  if (file.size > 10 * 1024 * 1024) {
    alert('Image must be smaller than 10MB')
    return
  }

  uploadingCardId.value = cardDesign.id

  try {
    // Resize and compress image
    // Target: 400x600px @ 85% quality for streaming
    const base64 = await resizeAndCompressImage(file, 400, 600, 0.85)
    
    // Verify the compressed size
    const compressedSize = Math.round((base64.length * 3) / 4 / 1024) // KB
    console.log(`Compressed image size: ${compressedSize}KB (original: ${Math.round(file.size / 1024)}KB)`)
    
    try {
      await updateCardDesign(cardDesign.id, base64)
      await loadDesignSet()
      uploadingCardId.value = null
    } catch (err) {
      console.error('Failed to upload image:', err)
      alert('Failed to upload image')
      uploadingCardId.value = null
    }
  } catch (err) {
    console.error('Error processing file:', err)
    alert('Failed to process image. Please try a different file.')
    uploadingCardId.value = null
  }
}

const viewCardImage = (card: CardDesign) => {
  if (!card.hasImage) return
  selectedCard.value = card
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  selectedCard.value = null
}

const removeCardImage = async (card: CardDesign) => {
  if (!confirm(`Remove image for ${card.displayName}?`)) return
  
  try {
    await updateCardDesign(card.id, null)
    await loadDesignSet()
  } catch (err) {
    console.error('Failed to remove image:', err)
    alert('Failed to remove image')
  }
}

const handleCardClick = (cardId: number, event: MouseEvent) => {
  // Don't trigger if clicking on action buttons
  const target = event.target as HTMLElement
  if (target.closest('button')) return
  
  // Find and click the hidden file input
  if (process.client) {
    const input = document.getElementById(`upload-${cardId}`) as HTMLInputElement
    if (input) input.click()
  }
}

const getRarityColor = (rarity: string) => {
  switch (rarity) {
    case 'legendary': return 'border-yellow-500 bg-yellow-500/10' // ARPG Yellow/Gold
    case 'rare': return 'border-purple-500 bg-purple-500/10' // ARPG Purple
    case 'uncommon': return 'border-blue-500 bg-blue-500/10' // ARPG Blue
    case 'common': return 'border-gray-500 bg-gray-700/50' // ARPG Gray/White
    default: return 'border-gray-600 bg-gray-800/50'
  }
}

const getRarityBadge = (rarity: string) => {
  switch (rarity) {
    case 'legendary': return 'bg-yellow-500/20 text-yellow-300 border-yellow-400' // ARPG Yellow/Gold
    case 'rare': return 'bg-purple-500/20 text-purple-300 border-purple-400' // ARPG Purple
    case 'uncommon': return 'bg-blue-500/20 text-blue-300 border-blue-400' // ARPG Blue
    case 'common': return 'bg-gray-600/50 text-gray-300 border-gray-500' // ARPG Gray/White
    default: return 'bg-gray-700/50 text-gray-400 border-gray-600'
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
      <button
        @click="navigateTo('/admin/designs')"
        class="mb-4 text-gray-300 hover:text-white flex items-center gap-2"
      >
        <Icon name="heroicons:arrow-left" class="w-5 h-5" />
        Back to Designs
      </button>

      <div v-if="designSet" class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta">
              {{ designSet.designName }}
            </h1>
            <!-- Type Badge -->
            <span
              :class="[
                'px-3 py-1 text-sm font-semibold rounded',
                designSet.type === 'template' 
                  ? 'bg-magenta/20 text-magenta border-2 border-magenta/50'
                  : 'bg-cyan/20 text-cyan border-2 border-cyan/50'
              ]"
            >
              {{ designSet.type === 'template' ? 'TEMPLATE SET' : 'FULL SET' }}
            </span>
            <!-- Premium Badge -->
            <span
              v-if="designSet.isPremium"
              class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm font-semibold rounded border-2 border-amber-500/50 flex items-center gap-1"
            >
              <Icon name="heroicons:star" class="w-4 h-4" />
              PREMIUM ${{ designSet.price }}
            </span>
          </div>
          
          <p class="text-gray-300">
            {{ designSet.type === 'template' 
              ? 'Upload 3 template frames (Basic/Court/Legendary)' 
              : 'Upload images for all 78 tarot cards' }}
          </p>
          <p v-if="designSet.description" class="text-gray-400 text-sm mt-1">
            {{ designSet.description }}
          </p>
          <p class="text-gray-400 text-sm mt-1">
            <Icon name="heroicons:information-circle" class="w-4 h-4 inline" />
            Images are automatically resized to 400×600px @ 85% quality (~50-150KB each)
          </p>
        </div>
        
        <div class="text-right">
          <div class="text-3xl font-bold text-white mb-1">
            {{ designSet.completedCards }} / {{ designSet.cardCount }}
          </div>
          <div class="text-sm text-gray-400">
            {{ designSet.type === 'template' ? 'Templates' : 'Cards' }} Complete
          </div>
          <div v-if="designSet.isComplete" class="mt-2 flex items-center gap-1 text-green-400">
            <Icon name="heroicons:check-circle" class="w-5 h-5" />
            <span class="font-semibold">Set Complete!</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div v-if="designSet" class="mb-8">
      <div class="w-full bg-gray-700 rounded-full h-3">
        <div
          class="h-3 rounded-full transition-all"
          :class="designSet.isComplete ? 'bg-green-500' : 'bg-gradient-to-r from-cyan to-magenta'"
          :style="{ width: `${(designSet.completedCards / designSet.cardCount) * 100}%` }"
        ></div>
      </div>
    </div>

    <!-- Page Error State -->
    <div v-if="pageError" class="text-center py-12">
      <p class="text-red-400 text-xl mb-4">⚠️ Error</p>
      <p class="text-white">{{ pageError }}</p>
      <button
        @click="navigateTo('/admin/designs')"
        class="mt-4 px-4 py-2 bg-cyan hover:bg-cyan/80 text-white rounded transition"
      >
        Back to Designs
      </button>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading && !designSet" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="designSet && !designSet.cards" class="text-center py-12">
      <p class="text-red-400 text-xl">Error: No cards found for this design set</p>
      <button
        @click="navigateTo('/admin/designs')"
        class="mt-4 px-4 py-2 bg-cyan hover:bg-cyan/80 text-white rounded transition"
      >
        Back to Designs
      </button>
    </div>

    <!-- Card Grid -->
    <div v-else-if="designSet && designSet.cards" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div
        v-for="card in designSet.cards"
        :key="card.id"
        :class="[
          'relative rounded-lg border-2 overflow-hidden transition-all hover:scale-105 cursor-pointer group',
          getRarityColor(card.rarity)
        ]"
        @click="(e) => handleCardClick(card.id, e)"
      >
        <!-- Card Content -->
        <div class="aspect-[2/3] flex flex-col items-center justify-center p-2">
          <!-- Image Preview -->
          <template v-if="card.hasImage && card.imageBase64">
            <img
              :src="card.imageBase64"
              :alt="card.displayName"
              class="w-full h-full object-cover rounded"
            />
          </template>

          <!-- Empty State -->
          <template v-else>
            <Icon name="heroicons:photo" class="w-12 h-12 text-gray-600 mb-2" />
            <p class="text-gray-400 text-xs text-center font-medium">{{ card.displayName }}</p>
          </template>

          <!-- Hover Overlay - Upload Icon -->
          <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <div class="text-center">
              <Icon name="heroicons:arrow-up-tray" class="w-8 h-8 text-white mx-auto mb-1" />
              <p class="text-white text-xs font-semibold">
                {{ card.hasImage ? 'Change Image' : 'Upload Image' }}
              </p>
            </div>
          </div>

          <!-- Uploading Overlay -->
          <div v-if="uploadingCardId === card.id" class="absolute inset-0 bg-black/75 flex items-center justify-center z-10">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-cyan"></div>
          </div>
        </div>

        <!-- Rarity Badge -->
        <div :class="['absolute top-1 left-1 px-2 py-0.5 rounded text-xs font-semibold border', getRarityBadge(card.rarity)]">
          {{ card.rarity }}
        </div>

        <!-- Hidden file input -->
        <input
          :id="`upload-${card.id}`"
          type="file"
          accept="image/*"
          class="hidden"
          @change="handleFileSelect(card, $event)"
          @click.stop
        />

        <!-- Action Buttons (if has image) - show on hover -->
        <div v-if="card.hasImage" class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
          <!-- View Button -->
          <button
            @click.stop="viewCardImage(card)"
            class="p-1.5 rounded-full bg-cyan hover:bg-cyan/80 text-white transition"
            title="View full image"
          >
            <Icon name="heroicons:eye" class="w-4 h-4" />
          </button>
          
          <!-- Remove Button -->
          <button
            @click.stop="removeCardImage(card)"
            class="p-1.5 rounded-full bg-red-600 hover:bg-red-700 text-white transition"
            title="Remove image"
          >
            <Icon name="heroicons:trash" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div v-if="showImageModal && selectedCard" class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4" @click="closeImageModal">
      <div class="relative max-w-2xl w-full" @click.stop>
        <button
          @click="closeImageModal"
          class="absolute top-4 right-4 p-2 rounded-full bg-gray-900/80 text-white hover:bg-gray-800 transition z-10"
        >
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
        
        <div class="bg-gray-900 rounded-lg p-4">
          <h3 class="text-xl font-bold text-white mb-4">{{ selectedCard.displayName }}</h3>
          <img
            :src="selectedCard.imageBase64!"
            :alt="selectedCard.displayName"
            class="w-full h-auto rounded"
          />
        </div>
      </div>
    </div>
  </div>
</template>

