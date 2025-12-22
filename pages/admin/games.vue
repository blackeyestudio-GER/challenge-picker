<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAdmin, type AdminGame, type CreateGameRequest, type GameName, type GamePagination } from '~/composables/useAdmin'
import { Icon } from '#components'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption } from '@headlessui/vue'
import GameFormModal from '~/components/modal/GameFormModal.vue'

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
    // Show all pages
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    // Smart pagination with ellipsis
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
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
          Admin - Games
        </h1>
        <p class="text-gray-300">
          {{ pagination ? `${pagination.total} total games` : 'Manage games in the system' }}
        </p>
      </div>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-2"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Game
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
        class="px-4 py-2 bg-cyan text-white rounded-lg font-semibold"
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
              class="w-full pl-12 pr-12 py-3 rounded-lg bg-gray-800/80 backdrop-blur-sm text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan"
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
          class="px-6 py-3 bg-cyan hover:bg-cyan/80 text-white font-semibold rounded-lg transition flex items-center gap-2"
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
      <p class="text-gray-400 text-sm mt-2">
        <Icon name="heroicons:information-circle" class="w-4 h-4 inline" />
        Showing {{ pagination ? `${(pagination.page - 1) * pagination.limit + 1}-${Math.min(pagination.page * pagination.limit, pagination.total)} of ${pagination.total}` : 'loading...' }}
        {{ searchQuery ? `results for "${searchQuery}"` : 'games' }}
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading && games.length === 0" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="games.length === 0" class="text-center py-12 bg-gray-800/80 backdrop-blur-sm rounded-lg border border-gray-700">
      <Icon name="heroicons:magnifying-glass" class="w-16 h-16 text-gray-600 mx-auto mb-4" />
      <p class="text-gray-400">
        {{ searchQuery ? 'No games found matching your search.' : 'No games found. Create your first game!' }}
      </p>
    </div>

    <!-- Games Grid -->
    <div v-else>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 mb-6">
        <div
          v-for="game in games"
          :key="game.id"
          @click="openEditModal(game)"
          :class="[
            'relative rounded-lg border-2 overflow-hidden transition-all hover:scale-105 cursor-pointer',
            game.isCategoryRepresentative 
              ? 'border-amber-500/50 bg-amber-900/20 backdrop-blur-sm hover:border-amber-400' 
              : 'border-gray-700 bg-gray-800/80 backdrop-blur-sm hover:border-cyan',
            !game.isActive && 'opacity-60'
          ]"
        >
          <!-- Inactive Overlay Badge -->
          <div v-if="!game.isActive" class="absolute top-2 left-2 z-10 px-2 py-1 bg-red-600/90 text-white text-xs font-bold rounded">
            INACTIVE
          </div>

          <!-- Category Representative Badge -->
          <div v-if="game.isCategoryRepresentative" class="absolute top-2 right-2 z-10 px-2 py-1 bg-amber-500/90 text-white text-xs font-bold rounded flex items-center gap-1">
            <Icon name="heroicons:tag" class="w-3 h-3" />
            CATEGORY
          </div>

          <!-- Card Content -->
          <div 
            :class="[
              'aspect-square flex flex-col items-center justify-center p-2',
              game.isCategoryRepresentative ? 'bg-amber-950/30' : 'bg-gray-900/50',
              !game.isActive && 'grayscale'
            ]"
          >
            <!-- Image -->
            <template v-if="game.image">
              <img
                :src="game.image"
                :alt="game.name"
                class="w-full h-full object-cover rounded"
              />
            </template>

            <!-- Empty State -->
            <template v-else>
              <Icon name="heroicons:photo" class="w-16 h-16 text-gray-600 mb-2" />
              <p class="text-gray-500 text-xs text-center">No Image</p>
            </template>
          </div>

          <!-- Game Info (at bottom) - Fixed Height -->
          <div 
            :class="[
              'p-3 border-t flex flex-col h-[7rem]',
              game.isCategoryRepresentative 
                ? 'bg-amber-950/30 border-amber-700/50' 
                : 'bg-gray-900 border-gray-700'
            ]"
          >
            <h3 
              :class="[
                'font-semibold text-sm leading-snug line-clamp-3 mb-2',
                game.isCategoryRepresentative ? 'text-amber-200' : 'text-white'
              ]" 
              :title="game.name"
            >
              {{ game.name }}
            </h3>
            
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5 mt-auto">
              <span>{{ game.rulesetCount }} ruleset{{ game.rulesetCount !== 1 ? 's' : '' }}</span>
            </div>
            
            <!-- Store Links Indicators - Always rendered with fixed height -->
            <div class="flex gap-1 h-5">
              <span v-if="game.steamLink" class="px-1.5 py-0.5 bg-blue-600/20 text-blue-300 rounded text-xs" title="Steam">
                S
              </span>
              <span v-if="game.epicLink" class="px-1.5 py-0.5 bg-purple-600/20 text-purple-300 rounded text-xs" title="Epic">
                E
              </span>
              <span v-if="game.gogLink" class="px-1.5 py-0.5 bg-pink-600/20 text-pink-300 rounded text-xs" title="GOG">
                G
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.totalPages > 1" class="flex items-center justify-center gap-2 mt-8">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Icon name="heroicons:chevron-left" class="w-5 h-5" />
        </button>
        
        <template v-for="(page, idx) in pageNumbers" :key="idx">
          <button
            v-if="typeof page === 'number'"
            @click="goToPage(page)"
            :class="[
              'px-4 py-2 rounded-lg transition font-semibold',
              page === currentPage
                ? 'bg-cyan text-white'
                : 'bg-gray-700 hover:bg-gray-600 text-white'
            ]"
          >
            {{ page }}
          </button>
          <span v-else class="px-2 text-gray-500">...</span>
        </template>
        
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === pagination.totalPages"
          class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Icon name="heroicons:chevron-right" class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Game Form Modal -->
    <GameFormModal
      :show="showModal"
      :editing-game="editingGame"
      :loading="loading"
      @close="closeModal"
      @submit="handleModalSubmit"
      @deactivate="handleDeactivate"
    />
  </div>
</template>
