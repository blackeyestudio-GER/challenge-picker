<script setup lang="ts">
import { Icon } from '#components'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption } from '@headlessui/vue'
import GameFormModal from '~/components/modal/GameFormModal.vue'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import { useAdminGamesPage as useAdminGamesPageComposable } from '~/composables/pages/useAdminGamesPage'

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
} = useAdminGamesPageComposable()

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="admin-games-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
            <Icon name="heroicons:magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-text-muted)] z-10" />
            <ComboboxInput
              :display-value="displaySelectedGameName"
              placeholder="Search games... (type and press Enter or select from dropdown)"
              class="admin-games-page__search-input"
              @change="searchQuery = $event.target.value"
              @keydown.enter="handleSearch"
            />
            <ComboboxButton class="absolute right-4 top-1/2 -translate-y-1/2">
              <Icon name="heroicons:chevron-down" class="w-5 h-5 text-[var(--color-text-muted)]" />
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
          class="aspect-[3/4] admin-add-card admin-games-page__add-card group"
          @click="openCreateModal"
        >
          <div class="admin-add-card__icon-wrapper">
            <Icon name="heroicons:plus" class="admin-add-card__icon" />
          </div>
          <p class="admin-add-card__label px-2">Add New Game</p>
        </div>

        <!-- Game Cards -->
        <div
          v-for="game in games"
          :key="game.id"
          :class="[
            'relative rounded-lg overflow-hidden transition-all cursor-pointer aspect-[3/4] admin-panel admin-panel--interactive',
            game.isCategoryRepresentative 
              ? 'admin-games-page__card admin-games-page__card--category' 
              : 'admin-games-page__card',
            !game.isActive && 'opacity-60'
          ]"
          @click="openEditModal(game)"
        >
          <!-- Inactive Badge -->
          <div v-if="!game.isActive" class="absolute top-2 left-2 z-10 px-2 py-1 bg-[var(--status-failed-text)]/90 text-white text-xs font-bold rounded">
            INACTIVE
          </div>

          <!-- Category Representative Badge -->
          <div v-if="game.isCategoryRepresentative" class="absolute top-2 right-2 z-10 px-2 py-1 bg-[var(--status-pending-text)]/90 text-white text-xs font-bold rounded flex items-center gap-1">
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
            <div v-else class="w-full h-full admin-surface-muted flex items-center justify-center">
              <Icon name="heroicons:photo" class="w-12 h-12 admin-text-subtle" />
            </div>
          </div>

          <!-- Game Name Overlay -->
          <div class="absolute bottom-0 left-0 right-0 admin-games-page__overlay p-3">
            <p class="text-[var(--color-text-primary)] font-bold text-sm line-clamp-2">{{ game.name }}</p>
            <p v-if="game.categories.length > 0" class="admin-games-page__overlay-copy text-xs mt-1">
              {{ game.categories.map(c => c.name).join(', ') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.totalPages > 1" class="flex items-center justify-center gap-2">
        <button
          :disabled="pagination.page === 1"
          class="admin-pager-button"
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
              ? 'admin-pager-button admin-pager-button--active font-bold'
              : pageNum === '...'
              ? 'bg-transparent border-transparent text-[var(--color-text-muted)] cursor-default'
              : 'admin-pager-button'
          ]"
          @click="typeof pageNum === 'number' ? goToPage(pageNum) : null"
        >
          {{ pageNum }}
        </button>
        
        <button
          :disabled="pagination.page === pagination.totalPages"
          class="admin-pager-button"
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
.admin-games-page__add-card {
  min-height: 100%;
  padding: 1rem;
  border-radius: 1rem;
}

.admin-games-page__card {
  border-width: 1px;
}

.admin-games-page__card--category {
  border-color: color-mix(in srgb, var(--color-btn-warning-border) 65%, var(--color-border-secondary));
}

.admin-games-page__overlay {
  background: linear-gradient(to top, rgba(0, 0, 0, 0.82), rgba(0, 0, 0, 0.48), transparent);
}

.admin-games-page__overlay-copy {
  color: rgba(255, 255, 255, 0.78);
}

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
