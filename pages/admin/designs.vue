<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useDesigns, type DesignName, type DesignSet } from '~/composables/useDesigns'
import { Icon } from '#components'
import DesignSetFormModal from '~/components/modal/DesignSetFormModal.vue'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import AdminAddCard from '~/components/admin/AdminAddCard.vue'

definePageMeta({
  middleware: 'admin'
})

const router = useRouter()
const route = useRoute()
const { fetchDesignNames, createDesignName, deleteDesignName, fetchDesignSets, createDesignSet, loading } = useDesigns()

const designSets = ref<DesignSet[]>([])
const showCreateModal = ref(false)
const creating = ref(false)

onMounted(async () => {
  await loadData()
})

// Reload data when navigating back to this page from the editor
watch(() => route.path, async (newPath) => {
  if (newPath === '/admin/designs') {
    await loadData()
  }
})

const loadData = async () => {
  try {
    designSets.value = await fetchDesignSets()
  } catch (err) {
    console.error('Failed to load designs:', err)
  }
}

const openCreateModal = () => {
  showCreateModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
}

const handleModalSubmit = async (data: { 
  name: string
  description: string
  type: 'full' | 'template'
  isPremium: boolean
  price: string
  theme: string
  sortOrder: number
}) => {
  if (creating.value) return
  
  creating.value = true
  try {
    // Create design name first
    const designName = await createDesignName(data.name, data.description)
    
    // Then create design set with all the options
    const newSet = await createDesignSet({
      designNameId: designName.id,
      type: data.type,
      isPremium: data.isPremium,
      price: data.isPremium && data.price ? data.price : null,
      theme: data.theme || null,
      description: data.description || null,
      sortOrder: data.sortOrder || 0
    })
    
    closeModal()
    
    // Navigate to the design set editor
    navigateTo(`/admin/design-set/${newSet.id}`)
  } catch (err) {
    console.error('Failed to create design set:', err)
    alert('Failed to create design set')
  } finally {
    creating.value = false
  }
}

const handleDeleteDesignSet = async (designSet: DesignSet) => {
  const cardText = designSet.type === 'template' 
    ? `${designSet.cardCount} template frames` 
    : `${designSet.cardCount} card images`
  
  if (!confirm(`Are you sure you want to delete "${designSet.designName}"? This will delete all ${cardText}.`)) return
  
  try {
    // Deleting the design name will cascade delete the design set
    await deleteDesignName(designSet.designNameId)
    await loadData()
  } catch (err) {
    console.error('Failed to delete design set:', err)
    alert('Failed to delete design set')
  }
}

const editDesignSet = (setId: number) => {
  navigateTo(`/admin/design-set/${setId}`)
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <AdminHeader
      title="Card Designs"
      description="Manage tarot card design styles and upload card images"
    />

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan mb-4"></div>
      <p class="text-white">Loading designs...</p>
    </div>

    <!-- Design Sets Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <!-- Add New Design Set Card -->
      <AdminAddCard
        label="New Design Set"
        @click="openCreateModal"
      />

      <!-- Design Set Cards -->
      <div
        v-for="designSet in designSets"
        :key="designSet.id"
        :class="[
          'bg-gray-800/80 backdrop-blur-sm border rounded-lg overflow-hidden hover:border-cyan transition-all flex flex-col',
          designSet.isPremium ? 'border-amber-500/50' : 'border-gray-700'
        ]"
      >
        <!-- Card Preview Image -->
        <div class="relative w-full aspect-[3/4] bg-gray-900">
          <img
            v-if="designSet.previewImage"
            :src="designSet.previewImage"
            :alt="designSet.designName"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <div class="text-center">
              <Icon name="heroicons:photo" class="w-16 h-16 text-gray-600 mx-auto mb-2" />
              <p class="text-gray-500 text-sm">No cards uploaded</p>
            </div>
          </div>

          <!-- Premium Badge Overlay -->
          <div
            v-if="designSet.isPremium"
            class="absolute top-2 right-2 px-2 py-1 bg-amber-500/90 text-white text-xs font-bold rounded flex items-center gap-1"
          >
            <Icon name="heroicons:star" class="w-3 h-3" />
            ${{ designSet.price }}
          </div>

          <!-- Type Badge Overlay -->
          <div
            class="absolute top-2 left-2 px-2 py-1 text-white text-xs font-bold rounded"
            :class="designSet.type === 'template' ? 'bg-magenta/90' : 'bg-cyan/90'"
          >
            {{ designSet.type === 'template' ? 'Template' : 'Full' }}
          </div>
        </div>

        <!-- Card Info -->
        <div class="p-4 flex-1 flex flex-col">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="text-lg font-bold text-white mb-1">{{ designSet.designName }}</h3>
              
              <!-- Info Row -->
              <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-400">
                  {{ designSet.cardCount }} {{ designSet.type === 'template' ? 'templates' : 'cards' }}
                </span>
                
                <!-- Free Badge -->
                <span
                  v-if="!designSet.isPremium"
                  class="px-2 py-0.5 bg-green-500/20 text-green-300 text-xs font-semibold rounded"
                >
                  FREE
                </span>
              </div>

              <!-- Theme -->
              <p v-if="designSet.theme" class="text-gray-500 text-xs mt-1">
                {{ designSet.theme }}
              </p>
            </div>
            
            <button
              @click="handleDeleteDesignSet(designSet)"
              class="text-red-400 hover:text-red-300 transition"
              title="Delete design set"
            >
              <Icon name="heroicons:trash" class="w-5 h-5" />
            </button>
          </div>

          <!-- Progress -->
          <div class="mb-3 flex-1">
            <div class="flex items-center justify-between text-sm mb-1">
              <span class="text-gray-400">Progress</span>
              <span class="text-white font-semibold">
                {{ designSet.completedCards }} / {{ designSet.cardCount }}
              </span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-1.5">
              <div
                class="h-1.5 rounded-full transition-all"
                :class="designSet.isComplete ? 'bg-green-500' : 'bg-cyan'"
                :style="{ width: `${(designSet.completedCards / designSet.cardCount) * 100}%` }"
              ></div>
            </div>
            <p v-if="designSet.isComplete" class="text-green-400 text-xs mt-1 flex items-center gap-1">
              <Icon name="heroicons:check-circle" class="w-3 h-3" />
              Complete
            </p>
          </div>

          <button
            @click="editDesignSet(designSet.id)"
            class="w-full px-4 py-2 bg-cyan hover:bg-cyan-dark text-white rounded-lg transition font-semibold"
          >
            {{ designSet.type === 'template' ? 'Upload Templates' : 'Edit Card Images' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && designSets.length === 0" class="text-center py-12">
      <Icon name="heroicons:paint-brush" class="w-16 h-16 mx-auto text-gray-600 mb-4" />
      <p class="text-gray-400 text-lg mb-4">No design sets yet</p>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-cyan hover:bg-cyan-dark text-white font-bold rounded-lg transition-all flex items-center gap-2 mx-auto"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Your First Design Set
      </button>
    </div>

    <!-- Design Set Form Modal -->
    <DesignSetFormModal
      :show="showCreateModal"
      :loading="creating"
      @close="closeModal"
      @submit="handleModalSubmit"
    />
  </div>
</template>
