<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useDesigns, type DesignName, type DesignSet } from '~/composables/useDesigns'
import { Icon } from '#components'
import DesignSetFormModal from '~/components/modal/DesignSetFormModal.vue'

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

const handleModalSubmit = async (data: { name: string; description: string }) => {
  if (creating.value) return
  
  creating.value = true
  try {
    // Create design name first
    const designName = await createDesignName(data.name, data.description)
    
    // Then create design set with 78 empty cards
    const newSet = await createDesignSet(designName.id)
    
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
  if (!confirm(`Are you sure you want to delete "${designSet.designName}"? This will delete all 78 card images.`)) return
  
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
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
          Card Designs
        </h1>
        <p class="text-gray-300">Manage tarot card design styles and upload card images</p>
      </div>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-2"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        New Design Set
      </button>
    </div>

    <!-- Admin Navigation -->
    <div class="flex gap-3 mb-6">
      <NuxtLink
        to="/admin/categories"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Categories
      </NuxtLink>
      <NuxtLink
        to="/admin/games"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Games
      </NuxtLink>
      <NuxtLink
        to="/admin/rulesets"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Rulesets
      </NuxtLink>
      <NuxtLink
        to="/admin/rules"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Rules
      </NuxtLink>
      <NuxtLink
        to="/admin/designs"
        class="px-4 py-2 bg-cyan text-white rounded-lg font-semibold"
      >
        Card Designs
      </NuxtLink>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Design Sets Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="designSet in designSets"
        :key="designSet.id"
        class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-cyan transition-all"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-xl font-bold text-white mb-1">{{ designSet.designName }}</h3>
            <p class="text-gray-400 text-sm">78 Tarot Cards</p>
          </div>
          <button
            @click="handleDeleteDesignSet(designSet)"
            class="ml-2 text-red-400 hover:text-red-300 transition"
            title="Delete design set"
          >
            <Icon name="heroicons:trash" class="w-5 h-5" />
          </button>
        </div>

        <!-- Progress -->
        <div class="mb-4">
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-gray-300">Cards Completed</span>
            <span class="text-white font-semibold">
              {{ designSet.completedCards }} / 78
            </span>
          </div>
          <div class="w-full bg-gray-700 rounded-full h-2">
            <div
              class="h-2 rounded-full transition-all"
              :class="designSet.isComplete ? 'bg-green-500' : 'bg-cyan'"
              :style="{ width: `${(designSet.completedCards / 78) * 100}%` }"
            ></div>
          </div>
          <p v-if="designSet.isComplete" class="text-green-400 text-xs mt-1 flex items-center gap-1">
            <Icon name="heroicons:check-circle" class="w-4 h-4" />
            Complete
          </p>
        </div>

        <button
          @click="editDesignSet(designSet.id)"
          class="w-full px-4 py-2 bg-cyan hover:bg-cyan/80 text-white rounded-lg transition font-semibold"
        >
          Edit Card Images
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="designSets.length === 0" class="col-span-full text-center py-12 text-gray-400">
        No design sets yet. Create your first design set to get started!
      </div>
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

