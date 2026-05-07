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
  <div class="admin-categories-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
        class="admin-panel admin-panel--interactive admin-categories-page__card p-6 flex flex-col justify-between min-h-[200px]"
      >
        <div>
          <div class="flex items-start justify-between mb-3">
            <h3 class="admin-categories-page__card-title">{{ category.name }}</h3>
            <span class="admin-badge admin-badge--info">
              {{ category.games.length }} {{ category.games.length === 1 ? 'game' : 'games' }}
            </span>
          </div>
          <p v-if="category.description" class="admin-text-muted text-sm mb-4 line-clamp-2">
            {{ category.description }}
          </p>
          <p v-else class="admin-text-subtle text-sm italic mb-4">No description</p>
        </div>

        <div class="flex gap-2 mt-4">
          <button
            class="btn btn-primary flex-1 flex items-center justify-center gap-2"
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
      class="admin-modal-backdrop z-50"
      @click.self="closeModal"
    >
      <div class="admin-modal-surface max-w-2xl">
        <div class="admin-modal-header">
          <div>
            <h2 class="text-3xl font-bold text-[var(--color-text-primary)] mb-2">
              {{ editingCategory ? 'Edit Category' : 'Create Category' }}
            </h2>
            <p class="text-[var(--color-text-muted)]">{{ editingCategory ? 'Update category details' : 'Add a new game category' }}</p>
          </div>
          <button class="admin-modal-close" @click="closeModal">
            <Icon name="heroicons:x-mark" class="w-6 h-6" />
          </button>
        </div>

        <form class="admin-modal-body space-y-6" @submit.prevent="handleSubmit">
          <div>
            <label class="block text-sm font-semibold text-[var(--color-text-primary)] mb-2">
              Category Name *
            </label>
            <input
              v-model="formData.name"
              type="text"
              required
              placeholder="e.g., Horror, Shooter, RPG"
              class="w-full px-4 py-3 bg-[var(--color-bg-card)] border border-[var(--color-border-secondary)] rounded-xl text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            >
          </div>

          <div>
            <label class="block text-sm font-semibold text-[var(--color-text-primary)] mb-2">
              Description
            </label>
            <textarea
              v-model="formData.description"
              rows="3"
              placeholder="Brief description of this category..."
              class="w-full px-4 py-3 bg-[var(--color-bg-card)] border border-[var(--color-border-secondary)] rounded-xl text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] resize-none"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-[var(--color-text-primary)] mb-2">
              Assign Games ({{ formData.gameIds.length }} selected)
            </label>
            
            <div class="relative mb-3">
              <Icon name="heroicons:magnifying-glass" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-[var(--color-text-muted)]" />
              <input
                v-model="gameSearchQuery"
                type="text"
                placeholder="Search games..."
                class="w-full pl-10 pr-4 py-2 bg-[var(--color-bg-card)] border border-[var(--color-border-secondary)] rounded-xl text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
              >
            </div>

            <div class="max-h-64 overflow-y-auto bg-[var(--color-bg-overlay)] border border-[var(--color-border-secondary)] rounded-xl p-3 space-y-2">
              <label
                v-for="game in filteredGames"
                :key="game.id"
                class="flex items-center gap-3 p-2 hover:bg-[var(--color-bg-card-hover)] rounded-lg cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  :checked="formData.gameIds.includes(game.id)"
                  class="w-4 h-4 rounded border-[var(--color-border-secondary)] text-[var(--color-accent-primary)] focus:ring-[var(--color-accent-primary)]"
                  @change="toggleGame(game.id)"
                >
                <span class="text-[var(--color-text-primary)]">{{ game.name }}</span>
              </label>
              <div v-if="filteredGames.length === 0" class="text-center py-4 text-[var(--color-text-muted)]">
                No games found
              </div>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 btn btn-primary px-6 py-3 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ editingCategory ? 'Update Category' : 'Create Category' }}
            </button>
            <button
              type="button"
              class="px-6 py-3 btn btn-secondary font-semibold"
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

<style scoped>
.admin-categories-page__card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-primary);
}
</style>
