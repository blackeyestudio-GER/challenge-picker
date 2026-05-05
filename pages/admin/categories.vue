<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAdmin, type AdminCategory, type AdminGame, type CreateCategoryRequest, type UpdateCategoryRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import AdminSearchBar from '~/components/admin/AdminSearchBar.vue'
import AdminEmptyState from '~/components/admin/AdminEmptyState.vue'
import AdminAddCard from '~/components/admin/AdminAddCard.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminCategories, fetchAdminGames, createCategory, updateCategory, deleteCategory, loading } = useAdmin()
const { success, notifyApiError } = useNotify()

const categories = ref<AdminCategory[]>([])
const allGames = ref<AdminGame[]>([])
const searchQuery = ref('')
const gameSearchQuery = ref('')
const showModal = ref(false)
const editingCategory = ref<AdminCategory | null>(null)
const errorMessage = ref('')
const formData = ref<CreateCategoryRequest & UpdateCategoryRequest & { id?: number }>({
  name: '',
  description: '',
  gameIds: []
})

// Computed filtered categories
const filteredCategories = computed(() => {
  if (!searchQuery.value.trim()) {
    return categories.value
  }
  const query = searchQuery.value.toLowerCase()
  return categories.value.filter(category =>
    category.name.toLowerCase().includes(query) ||
    (category.description?.toLowerCase() || '').includes(query)
  )
})

// Computed filtered games for modal
const filteredGames = computed(() => {
  if (!gameSearchQuery.value.trim()) {
    return allGames.value
  }
  const query = gameSearchQuery.value.toLowerCase()
  return allGames.value.filter(game =>
    game.name.toLowerCase().includes(query)
  )
})

onMounted(async () => {
  await loadCategories()
  await loadGames()
})

const loadCategories = async () => {
  errorMessage.value = ''
  try {
    categories.value = await fetchAdminCategories()
  } catch (err: unknown) {
    errorMessage.value = 'Failed to load categories'
    notifyApiError(err, 'Failed to load categories')
  }
}

const loadGames = async () => {
  try {
    const response = await fetchAdminGames(1, 1000)
    allGames.value = response.games.filter(g => !g.isCategoryRepresentative)
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to load games')
  }
}

const openCreateModal = () => {
  gameSearchQuery.value = ''
  editingCategory.value = null
  formData.value = {
    name: '',
    description: '',
    gameIds: []
  }
  showModal.value = true
}

const openEditModal = (category: AdminCategory) => {
  gameSearchQuery.value = ''
  editingCategory.value = category
  formData.value = {
    id: category.id,
    name: category.name,
    description: category.description || '',
    gameIds: category.games.map(g => g.id)
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingCategory.value = null
}

const handleSubmit = async () => {
  try {
    if (editingCategory.value) {
      await updateCategory(editingCategory.value.id, formData.value)
    } else {
      await createCategory(formData.value)
    }
    await loadCategories()
    closeModal()
    success(editingCategory.value ? 'Category updated' : 'Category created')
  } catch (err) {
    notifyApiError(err, 'Failed to save category')
  }
}

const handleDelete = async (category: AdminCategory) => {
  if (!confirm(`Are you sure you want to delete "${category.name}"? This will remove it from all games.`)) {
    return
  }

  try {
    await deleteCategory(category.id)
    await loadCategories()
    success('Category deleted')
  } catch (err) {
    notifyApiError(err, 'Failed to delete category')
  }
}

const toggleGame = (gameId: number) => {
  const index = formData.value.gameIds.indexOf(gameId)
  if (index > -1) {
    formData.value.gameIds.splice(index, 1)
  } else {
    formData.value.gameIds.push(gameId)
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <AdminHeader
      title="Categories"
      description="Organize games into categories"
    />

    <!-- Search Bar -->
    <div class="mb-6">
      <AdminSearchBar
        v-model="searchQuery"
        placeholder="Search categories..."
      />
    </div>

    <LoadingState v-if="loading && categories.length === 0" message="Loading categories..." />

    <ErrorState v-else-if="errorMessage && categories.length === 0" :message="errorMessage" />

    <!-- Categories Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Add New Category Card (Always First) -->
      <AdminAddCard
        label="Add New Category"
        @click="openCreateModal"
      />

      <!-- Category Cards -->
      <div
        v-for="category in filteredCategories"
        :key="category.id"
        class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 hover:border-cyan rounded-xl p-6 flex flex-col justify-between transition-all min-h-[200px]"
      >
        <div>
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-xl font-bold text-white">{{ category.name }}</h3>
            <span class="px-3 py-1 bg-cyan/20 text-cyan text-xs font-semibold rounded-full">
              {{ category.games.length }} {{ category.games.length === 1 ? 'game' : 'games' }}
            </span>
          </div>
          <p v-if="category.description" class="text-gray-400 text-sm mb-4 line-clamp-2">
            {{ category.description }}
          </p>
          <p v-else class="text-gray-500 text-sm italic mb-4">No description</p>
        </div>

        <div class="flex gap-2 mt-4">
          <button
            class="flex-1 px-4 py-2 bg-cyan hover:bg-cyan-dark text-white rounded-lg transition-all flex items-center justify-center gap-2 font-semibold"
            @click="openEditModal(category)"
          >
            <Icon name="heroicons:pencil" class="w-4 h-4" />
            Edit
          </button>
          <button
            class="btn btn-danger flex-1 flex items-center justify-center gap-2"
            @click="handleDelete(category)"
          >
            <Icon name="heroicons:trash" class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <AdminEmptyState
      v-if="!loading && filteredCategories.length === 0 && searchQuery"
      :message="`No categories found matching &quot;${searchQuery}&quot;`"
      :search-query="searchQuery"
      @clear-search="searchQuery = ''"
    />

    <!-- Category Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
      @click.self="closeModal"
    >
      <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-6">
          <div>
            <h2 class="text-3xl font-bold text-white mb-2">
              {{ editingCategory ? 'Edit Category' : 'Create Category' }}
            </h2>
            <p class="text-gray-400">{{ editingCategory ? 'Update category details' : 'Add a new game category' }}</p>
          </div>
          <button
            class="text-gray-400 hover:text-white transition-colors"
            @click="closeModal"
          >
            <Icon name="heroicons:x-mark" class="w-6 h-6" />
          </button>
        </div>

        <form class="space-y-6" @submit.prevent="handleSubmit">
          <!-- Name -->
          <div>
            <label class="block text-sm font-semibold text-white mb-2">
              Category Name *
            </label>
            <input
              v-model="formData.name"
              type="text"
              required
              placeholder="e.g., Horror, Shooter, RPG"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan focus:border-transparent"
            >
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-semibold text-white mb-2">
              Description
            </label>
            <textarea
              v-model="formData.description"
              rows="3"
              placeholder="Brief description of this category..."
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan focus:border-transparent resize-none"
            />
          </div>

          <!-- Games Selection -->
          <div>
            <label class="block text-sm font-semibold text-white mb-2">
              Assign Games ({{ formData.gameIds.length }} selected)
            </label>
            
            <!-- Game Search -->
            <div class="relative mb-3">
              <Icon name="heroicons:magnifying-glass" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="gameSearchQuery"
                type="text"
                placeholder="Search games..."
                class="w-full pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan focus:border-transparent"
              >
            </div>

            <!-- Games List -->
            <div class="max-h-64 overflow-y-auto bg-gray-800 border border-gray-700 rounded-lg p-3 space-y-2">
              <label
                v-for="game in filteredGames"
                :key="game.id"
                class="flex items-center gap-3 p-2 hover:bg-gray-700 rounded-lg cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  :checked="formData.gameIds.includes(game.id)"
                  class="w-4 h-4 rounded border-gray-600 text-cyan focus:ring-cyan focus:ring-offset-gray-900"
                  @change="toggleGame(game.id)"
                >
                <span class="text-white">{{ game.name }}</span>
              </label>
              <div v-if="filteredGames.length === 0" class="text-center py-4 text-gray-500">
                No games found
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-6 py-3 bg-cyan hover:bg-cyan-dark text-white rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ editingCategory ? 'Update Category' : 'Create Category' }}
            </button>
            <button
              type="button"
              class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-semibold transition-all"
              @click="closeModal"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
