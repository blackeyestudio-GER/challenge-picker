<script setup lang="ts">
import { Icon } from '#components'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption } from '@headlessui/vue'
import GameFormModal from '~/components/modal/GameFormModal.vue'
import AdminHeader from '~/components/admin/AdminHeader.vue'

definePageMeta({
  middleware: 'admin'
})

const {
  loading,
  games,
  pagination,
  searchQuery,
  selectedGame,
  errorMessage,
  showModal,
  editingGame,
  filteredGameNames,
  pageNumbers,
  displaySelectedGameName,
  bootstrap,
  handleSearch,
  handleGameSelect,
  clearSearch,
  goToPage,
  openCreateModal,
  openEditModal,
  closeModal,
  handleModalSubmit,
  handleDeactivate
} = useAdminGamesPage()

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <AdminHeader
      title="Games"
      :description="pagination ? `${pagination.total} total games` : 'Manage games in the system'"
    />

    <!-- Search Bar with Autocomplete -->
    <div class="mb-6">
      <div class="flex gap-3">
        <Combobox v-model="selectedGame" class="flex-1" @update:model-value="handleGameSelect">
          <div class="relative">
            <Icon name="heroicons:magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 z-10" />
            <ComboboxInput
              :display-value="displaySelectedGameName"
              placeholder="Search games... (type and press Enter or select from dropdown)"
              class="admin-games-page__search-input"
              @change="searchQuery = $event.target.value"
              @keydown.enter="handleSearch"
            />
            <ComboboxButton class="absolute right-4 top-1/2 -translate-y-1/2">
              <Icon name="heroicons:chevron-down" class="w-5 h-5 text-gray-400" />
            </ComboboxButton>
            
            <ComboboxOptions class="admin-games-page__search-options">
              <div v-if="filteredGameNames.length === 0" class="admin-games-page__search-empty">
                No games found
              </div>
              <ComboboxOption
                v-for="game in filteredGameNames"
                :key="game.id"
                v-slot="{ active, selected }"
                :value="game"
                class="cursor-pointer"
              >
                <div
                  :class="[
                    'admin-games-page__search-option',
                    active ? 'admin-games-page__search-option--active' : '',
                    selected ? 'admin-games-page__search-option--selected' : ''
                  ]"
                >
                  {{ game.name }}
                </div>
              </ComboboxOption>
            </ComboboxOptions>
          </div>
        </Combobox>
        
        <button
          class="btn btn-primary px-6 py-3 flex items-center gap-2"
          @click="handleSearch"
        >
          <Icon name="heroicons:magnifying-glass" class="w-5 h-5" />
          Search
        </button>
        
        <button
          :disabled="!searchQuery.trim()"
          :class="[
            'admin-games-page__clear-button',
            searchQuery.trim() 
              ? 'admin-games-page__clear-button--active' 
              : 'admin-games-page__clear-button--disabled'
          ]"
          @click="clearSearch"
        >
          <Icon name="heroicons:x-mark" class="w-5 h-5" />
          Clear
        </button>
      </div>
      <p class="admin-text-muted text-sm mt-2 flex items-center gap-1">
        <Icon name="heroicons:information-circle" class="w-4 h-4" />
        Showing {{ pagination ? `${(pagination.page - 1) * pagination.limit + 1}-${Math.min(pagination.page * pagination.limit, pagination.total)} of ${pagination.total}` : 'loading...' }}
        {{ searchQuery ? `results for "${searchQuery}"` : 'games' }}
      </p>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading && games.length === 0" message="Loading games..." />

    <ErrorState v-else-if="errorMessage && games.length === 0" :message="errorMessage" />

    <!-- Empty State -->
    <EmptyState
      v-else-if="games.length === 0"
      icon="heroicons:puzzle-piece"
      :title="searchQuery ? 'No games found' : 'No games yet'"
      :message="searchQuery ? 'No games match the current search.' : 'Create your first game to start building the catalog.'"
    >
      <button
        v-if="!searchQuery"
        class="btn btn-primary"
        @click="openCreateModal"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Game
      </button>
    </EmptyState>

    <!-- Games Grid -->
    <div v-else>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-6">
        <!-- Add New Game Card (Always First) -->
        <div
          class="aspect-[3/4] bg-gray-800/50 border-2 border-dashed border-gray-600 hover:border-cyan hover:bg-gray-800 rounded-lg flex flex-col items-center justify-center text-center transition-all cursor-pointer group"
          @click="openCreateModal"
        >
          <div class="w-12 h-12 rounded-full bg-gray-700 group-hover:bg-cyan/20 flex items-center justify-center mb-2 transition-all">
            <Icon name="heroicons:plus" class="w-6 h-6 text-gray-400 group-hover:text-cyan transition-colors" />
          </div>
          <p class="text-sm font-bold text-gray-400 group-hover:text-white transition-colors px-2">Add New Game</p>
        </div>

        <!-- Game Cards -->
        <div
          v-for="game in games"
          :key="game.id"
          :class="[
            'relative rounded-lg border-2 overflow-hidden transition-all hover:scale-105 cursor-pointer aspect-[3/4]',
            game.isCategoryRepresentative 
              ? 'border-amber-500/50 bg-amber-900/20 hover:border-amber-400' 
              : 'border-gray-700 bg-gray-800/80 hover:border-cyan',
            !game.isActive && 'opacity-60'
          ]"
          @click="openEditModal(game)"
        >
          <!-- Inactive Badge -->
          <div v-if="!game.isActive" class="absolute top-2 left-2 z-10 px-2 py-1 bg-red-600/90 text-white text-xs font-bold rounded">
            INACTIVE
          </div>

          <!-- Category Representative Badge -->
          <div v-if="game.isCategoryRepresentative" class="absolute top-2 right-2 z-10 px-2 py-1 bg-amber-500/90 text-white text-xs font-bold rounded flex items-center gap-1">
            <Icon name="heroicons:tag" class="w-3 h-3" />
            CAT
          </div>

          <!-- Game Image -->
          <div class="w-full h-full">
            <img
              v-if="game.image"
              :src="game.image"
              :alt="game.name"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full bg-gray-900 flex items-center justify-center">
              <Icon name="heroicons:photo" class="w-12 h-12 text-gray-600" />
            </div>
          </div>

          <!-- Game Name Overlay -->
          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/70 to-transparent p-3">
            <p class="text-white font-bold text-sm line-clamp-2">{{ game.name }}</p>
            <p v-if="game.categories.length > 0" class="text-gray-300 text-xs mt-1">
              {{ game.categories.map(c => c.name).join(', ') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.totalPages > 1" class="flex items-center justify-center gap-2">
        <button
          :disabled="pagination.page === 1"
          class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          @click="goToPage(pagination.page - 1)"
        >
          <Icon name="heroicons:chevron-left" class="w-5 h-5" />
        </button>
        
        <button
          v-for="(pageNum, index) in pageNumbers"
          :key="index"
          :disabled="pageNum === '...'"
          :class="[
            'px-4 py-2 rounded-lg border transition-all',
            pageNum === pagination.page
              ? 'bg-cyan text-white border-cyan font-bold'
              : pageNum === '...'
              ? 'bg-transparent border-transparent text-gray-500 cursor-default'
              : 'bg-gray-800 border-gray-700 text-white hover:bg-gray-700'
          ]"
          @click="typeof pageNum === 'number' ? goToPage(pageNum) : null"
        >
          {{ pageNum }}
        </button>
        
        <button
          :disabled="pagination.page === pagination.totalPages"
          class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          @click="goToPage(pagination.page + 1)"
        >
          <Icon name="heroicons:chevron-right" class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Game Form Modal -->
    <GameFormModal
      v-if="showModal"
      :game="editingGame"
      @close="closeModal"
      @submit="handleModalSubmit"
      @deactivate="handleDeactivate"
    />
  </div>
</template>

<style scoped>
.admin-games-page__search-input {
  width: 100%;
  padding: 0.75rem 3rem 0.75rem 3rem;
  border-radius: 0.75rem;
  border: 1px solid var(--color-border-primary);
  background: var(--color-surface-primary);
  color: var(--color-text-primary);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.admin-games-page__search-input::placeholder {
  color: var(--color-text-tertiary);
}

.admin-games-page__search-input:focus {
  outline: none;
  border-color: var(--color-accent-primary);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-accent-primary) 25%, transparent);
}

.admin-games-page__search-options {
  position: absolute;
  z-index: 20;
  width: 100%;
  max-height: 15rem;
  overflow: auto;
  margin-top: 0.25rem;
  border-radius: 0.75rem;
  border: 1px solid var(--color-border-primary);
  background: var(--color-surface-primary);
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.25);
}

.admin-games-page__search-empty {
  padding: 0.75rem 1rem;
  color: var(--color-text-tertiary);
}

.admin-games-page__search-option {
  padding: 0.5rem 1rem;
  color: var(--color-text-primary);
}

.admin-games-page__search-option--active {
  background: color-mix(in srgb, var(--color-accent-primary) 16%, transparent);
}

.admin-games-page__search-option--selected {
  font-weight: 600;
  background: color-mix(in srgb, var(--color-accent-primary) 24%, transparent);
}

.admin-games-page__clear-button {
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  border: 1px solid var(--color-border-primary);
  font-weight: 600;
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease, opacity 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.admin-games-page__clear-button--active {
  background: var(--color-surface-secondary);
  color: var(--color-text-primary);
  cursor: pointer;
}

.admin-games-page__clear-button--active:hover {
  background: var(--color-surface-hover);
}

.admin-games-page__clear-button--disabled {
  background: var(--color-surface-primary);
  color: var(--color-text-muted);
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
