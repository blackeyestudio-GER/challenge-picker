<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAdmin, type AdminGame, type CreateGameRequest, type GameName, type GamePagination } from '~/composables/useAdmin'
import { Icon } from '#components'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption } from '@headlessui/vue'
import GameFormModal from '~/components/modal/GameFormModal.vue'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import AdminAddCard from '~/components/admin/AdminAddCard.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminGames, fetchGameNames, createGame, updateGame, deactivateGame, loading } = useAdmin()

const games = ref<AdminGame[]>([])
const gameNames = ref<GameName[]>([])
const pagination = ref<GamePagination | null>(null)
const currentPage = ref(1)
const searchQuery = ref('')
const selectedGame = ref<GameName | null>(null)

const showModal = ref(false)
const editingGame = ref<AdminGame | null>(null)

const filteredGameNames = computed(() => {
  if (!searchQuery.value.trim()) return gameNames.value
  
  const query = searchQuery.value.toLowerCase()
  return gameNames.value.filter(game =>
    game.name.toLowerCase().includes(query)
  )
})

onMounted(async () => {
  await Promise.all([
    loadGames(),
    loadGameNames()
  ])
})

const loadGames = async (page: number = 1, search: string = '') => {
  try {
    const response = await fetchAdminGames(page, 24, search)
    games.value = response.games
    pagination.value = response.pagination
    currentPage.value = page
  } catch (err) {
    console.error('Failed to load games:', err)
  }
}

const loadGameNames = async () => {
  try {
    gameNames.value = await fetchGameNames()
  } catch (err) {
    console.error('Failed to load game names:', err)
  }
}

const handleSearch = async () => {
  currentPage.value = 1
  await loadGames(1, searchQuery.value)
}

const handleGameSelect = async (game: GameName | null) => {
  if (game) {
    selectedGame.value = game
    searchQuery.value = game.name
    await handleSearch()
  }
}

const clearSearch = async () => {
  searchQuery.value = ''
  selectedGame.value = null
  currentPage.value = 1
  await loadGames(1, '')
}

const goToPage = async (page: number) => {
  await loadGames(page, searchQuery.value)
}

const openCreateModal = () => {
  editingGame.value = null
  showModal.value = true
}

const openEditModal = (game: AdminGame) => {
  editingGame.value = game
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingGame.value = null
}

const handleModalSubmit = async (data: CreateGameRequest & { id?: number }) => {
  try {
    if (editingGame.value) {
      await updateGame(editingGame.value.id, data)
    } else {
      await createGame(data)
    }
    await loadGames(currentPage.value, searchQuery.value)
    closeModal()
  } catch (err) {
    console.error('Failed to save game:', err)
    alert('Failed to save game')
  }
}

const handleDeactivate = async () => {
  if (!editingGame.value) return
  
  if (!confirm(`Are you sure you want to deactivate "${editingGame.value.name}"? This will hide it from the system but preserve its history.`)) return
  
  try {
    await deactivateGame(editingGame.value.id)
    closeModal()
    await loadGames(currentPage.value, searchQuery.value)
  } catch (err) {
    console.error('Failed to deactivate game:', err)
    alert('Failed to deactivate game')
  }
}

// Generate page numbers for pagination
const pageNumbers = computed(() => {
  if (!pagination.value) return []
  
  const { page, totalPages } = pagination.value
  const pages: (number | string)[] = []
  const maxVisible = 7
  
  if (totalPages <= maxVisible) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    if (page <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages)
    } else if (page >= totalPages - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = totalPages - 4; i <= totalPages; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = page - 1; i <= page + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages)
    }
  }
  
  return pages
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
        <Combobox v-model="selectedGame" @update:modelValue="handleGameSelect" class="flex-1">
          <div class="relative">
            <Icon name="heroicons:magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 z-10" />
            <ComboboxInput
              @change="searchQuery = $event.target.value"
              @keydown.enter="handleSearch"
              :displayValue="(game: any) => game?.name || searchQuery"
              placeholder="Search games... (type and press Enter or select from dropdown)"
              class="w-full pl-12 pr-12 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan"
            />
            <ComboboxButton class="absolute right-4 top-1/2 -translate-y-1/2">
              <Icon name="heroicons:chevron-down" class="w-5 h-5 text-gray-400" />
            </ComboboxButton>
            
            <ComboboxOptions class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-gray-800 border border-gray-700 shadow-xl">
              <div v-if="filteredGameNames.length === 0" class="px-4 py-3 text-gray-400">
                No games found
              </div>
              <ComboboxOption
                v-for="game in filteredGameNames"
                :key="game.id"
                :value="game"
                v-slot="{ active, selected }"
                class="cursor-pointer"
              >
                <div
                  :class="[
                    'px-4 py-2 text-white',
                    active ? 'bg-cyan/20' : '',
                    selected ? 'bg-cyan/30 font-semibold' : ''
                  ]"
                >
                  {{ game.name }}
                </div>
              </ComboboxOption>
            </ComboboxOptions>
          </div>
        </Combobox>
        
        <button
          @click="handleSearch"
          class="px-6 py-3 bg-cyan hover:bg-cyan-dark text-white font-semibold rounded-lg transition flex items-center gap-2"
        >
          <Icon name="heroicons:magnifying-glass" class="w-5 h-5" />
          Search
        </button>
        
        <button
          @click="clearSearch"
          :disabled="!searchQuery.trim()"
          :class="[
            'px-6 py-3 font-semibold rounded-lg transition flex items-center gap-2',
            searchQuery.trim() 
              ? 'bg-gray-700 hover:bg-gray-600 text-white cursor-pointer' 
              : 'bg-gray-800 text-gray-500 cursor-not-allowed opacity-50'
          ]"
        >
          <Icon name="heroicons:x-mark" class="w-5 h-5" />
          Clear
        </button>
      </div>
      <p class="text-gray-400 text-sm mt-2 flex items-center gap-1">
        <Icon name="heroicons:information-circle" class="w-4 h-4" />
        Showing {{ pagination ? `${(pagination.page - 1) * pagination.limit + 1}-${Math.min(pagination.page * pagination.limit, pagination.total)} of ${pagination.total}` : 'loading...' }}
        {{ searchQuery ? `results for "${searchQuery}"` : 'games' }}
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading && games.length === 0" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan mb-4"></div>
      <p class="text-white">Loading games...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="games.length === 0" class="text-center py-12">
      <Icon name="heroicons:puzzle-piece" class="w-16 h-16 text-gray-600 mx-auto mb-4" />
      <p class="text-gray-400 text-lg mb-4">
        {{ searchQuery ? 'No games found matching your search.' : 'No games found. Create your first game!' }}
      </p>
      <button
        v-if="!searchQuery"
        @click="openCreateModal"
        class="px-6 py-3 bg-cyan hover:bg-cyan-dark text-white font-bold rounded-lg transition-all flex items-center gap-2 mx-auto"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Game
      </button>
    </div>

    <!-- Games Grid -->
    <div v-else>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-6">
        <!-- Add New Game Card (Always First) -->
        <div
          @click="openCreateModal"
          class="aspect-[3/4] bg-gray-800/50 border-2 border-dashed border-gray-600 hover:border-cyan hover:bg-gray-800 rounded-lg flex flex-col items-center justify-center text-center transition-all cursor-pointer group"
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
          @click="openEditModal(game)"
          :class="[
            'relative rounded-lg border-2 overflow-hidden transition-all hover:scale-105 cursor-pointer aspect-[3/4]',
            game.isCategoryRepresentative 
              ? 'border-amber-500/50 bg-amber-900/20 hover:border-amber-400' 
              : 'border-gray-700 bg-gray-800/80 hover:border-cyan',
            !game.isActive && 'opacity-60'
          ]"
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
            />
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
          @click="goToPage(pagination.page - 1)"
          :disabled="pagination.page === 1"
          class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          <Icon name="heroicons:chevron-left" class="w-5 h-5" />
        </button>
        
        <button
          v-for="(pageNum, index) in pageNumbers"
          :key="index"
          @click="typeof pageNum === 'number' ? goToPage(pageNum) : null"
          :disabled="pageNum === '...'"
          :class="[
            'px-4 py-2 rounded-lg border transition-all',
            pageNum === pagination.page
              ? 'bg-cyan text-white border-cyan font-bold'
              : pageNum === '...'
              ? 'bg-transparent border-transparent text-gray-500 cursor-default'
              : 'bg-gray-800 border-gray-700 text-white hover:bg-gray-700'
          ]"
        >
          {{ pageNum }}
        </button>
        
        <button
          @click="goToPage(pagination.page + 1)"
          :disabled="pagination.page === pagination.totalPages"
          class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
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
