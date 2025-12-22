<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAdmin, type AdminCategory, type AdminGame, type CreateCategoryRequest, type UpdateCategoryRequest } from '~/composables/useAdmin'
import { Icon } from '#components'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminCategories, fetchAdminGames, createCategory, updateCategory, deleteCategory, loading } = useAdmin()

const categories = ref<AdminCategory[]>([])
const allGames = ref<AdminGame[]>([])
const gameSearchQuery = ref('')
const showModal = ref(false)
const editingCategory = ref<AdminCategory | null>(null)
const formData = ref<CreateCategoryRequest & UpdateCategoryRequest & { id?: number }>({
  name: '',
  description: '',
  gameIds: []
})

// Computed filtered games
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
  try {
    categories.value = await fetchAdminCategories()
  } catch (err) {
    console.error('Failed to load categories:', err)
  }
}

const loadGames = async () => {
  try {
    // Fetch all games (active and inactive, excluding category representatives)
    const response = await fetchAdminGames(1, 1000)
    allGames.value = response.games.filter(g => !g.isCategoryRepresentative)
  } catch (err) {
    console.error('Failed to load games:', err)
  }
}

const openCreateModal = () => {
  gameSearchQuery.value = '' // Clear search
  editingCategory.value = null
  formData.value = {
    name: '',
    description: '',
    gameIds: []
  }
  showModal.value = true
}

const openEditModal = (category: AdminCategory) => {
  gameSearchQuery.value = '' // Clear search
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
  } catch (err) {
    console.error('Failed to save category:', err)
    alert('Failed to save category')
  }
}

const handleDelete = async (category: AdminCategory) => {
  if (!confirm(`Are you sure you want to delete "${category.name}"? This will also delete its representative game.`)) return
  
  try {
    await deleteCategory(category.id)
    await loadCategories()
  } catch (err) {
    console.error('Failed to delete category:', err)
    alert('Failed to delete category')
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
          Admin - Categories
        </h1>
        <p class="text-gray-300">Manage game categories (auto-creates representative games)</p>
      </div>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-2"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Category
      </button>
    </div>

    <!-- Admin Navigation -->
    <div class="flex gap-3 mb-6">
      <NuxtLink
        to="/admin/categories"
        class="px-4 py-2 bg-cyan text-white rounded-lg font-semibold"
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
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Card Designs
      </NuxtLink>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Categories Table -->
    <div v-else class="bg-gray-800/80 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-900 border-b border-gray-700">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Name</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Slug</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Games</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Description</th>
            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id" class="border-b border-gray-700 hover:bg-gray-700/50 transition">
            <td class="px-6 py-4 text-white font-medium">{{ category.name }}</td>
            <td class="px-6 py-4 text-gray-400 text-sm font-mono">{{ category.slug }}</td>
            <td class="px-6 py-4 text-gray-300">{{ category.gameCount }} game{{ category.gameCount !== 1 ? 's' : '' }}</td>
            <td class="px-6 py-4 text-gray-300 text-sm">
              <div class="max-w-md truncate" :title="category.description || ''">
                {{ category.description || '-' }}
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openEditModal(category)"
                  class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(category)"
                  class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="categories.length === 0" class="text-center py-12 text-gray-400">
        No categories found. Create your first category!
      </div>
    </div>

    <!-- Category Form Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-white">
            {{ editingCategory ? 'Edit Category' : 'Create Category' }}
          </h2>
          <button @click="closeModal" class="text-gray-400 hover:text-white">
            <Icon name="heroicons:x-mark" class="w-6 h-6" />
          </button>
        </div>
        
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
              placeholder="e.g., FPS, Horror, RPG"
            />
            <p class="text-xs text-gray-400 mt-1">A representative game will be auto-created with this name</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
            <textarea
              v-model="formData.description"
              rows="3"
              class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
              placeholder="Optional description"
            ></textarea>
          </div>
          
          <!-- Games -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Games</label>
            
            <!-- Search Input -->
            <div class="mb-2">
              <input
                v-model="gameSearchQuery"
                type="text"
                placeholder="Search games..."
                class="w-full px-3 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
              />
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-3 bg-gray-900 rounded-lg border border-gray-600">
              <label
                v-for="game in filteredGames"
                :key="game.id"
                class="flex items-center gap-2 cursor-pointer hover:bg-gray-800 p-2 rounded transition"
                :class="{ 'opacity-50': !game.isActive }"
              >
                <input
                  type="checkbox"
                  :value="game.id"
                  v-model="formData.gameIds"
                  class="w-4 h-4 rounded bg-gray-900 border-gray-600 text-cyan focus:ring-cyan"
                />
                <span class="text-sm text-gray-300 truncate" :title="game.name">
                  {{ game.name }}
                  <span v-if="!game.isActive" class="text-xs text-gray-500">(inactive)</span>
                </span>
              </label>
              <div v-if="filteredGames.length === 0" class="col-span-full text-center text-gray-500 text-sm py-4">
                No games found
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              {{ formData.gameIds?.length || 0 }} games selected
              <span v-if="gameSearchQuery" class="text-gray-400">({{ filteredGames.length }} shown)</span>
            </p>
          </div>
          
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-6 py-2 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ editingCategory ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

