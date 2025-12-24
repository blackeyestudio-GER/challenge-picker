<script setup lang="ts">
import GameCard from '~/components/GameCard.vue'
import CategoryFilterList from '~/components/CategoryFilterList.vue'
import { useFavorites } from '~/composables/useFavorites'

definePageMeta({
  middleware: 'auth'
})

const { fetchGames, fetchActivePlaythrough, games, activePlaythrough, loading, error } = usePlaythrough()
const { categories, fetchCategories } = useCategories()
const { getGameCategories, toggleVote } = useGameCategories()
const { toggleFavorite } = useFavorites()
const { isAuthenticated, loadAuth } = useAuth()
const router = useRouter()

const searchQuery = ref('')
const selectedCategories = ref<Set<number>>(new Set())
const filterMode = ref<'AND' | 'OR'>('AND')
const showRandomGames = ref(false)
const showFavorites = ref(false)
const randomSeed = ref(0) // Used to trigger re-randomization

// Pagination
const currentPage = ref(1)
const gamesPerPage = 24

// Check for active playthrough on mount
onMounted(async () => {
  // Load auth state first
  loadAuth()
  
  await fetchActivePlaythrough()
  
  if (activePlaythrough.value) {
    // User already has an active session, redirect to setup
    router.push(`/playthrough/${activePlaythrough.value.uuid}/setup`)
  } else {
    // Load games and categories
    await Promise.all([
      fetchGames(),
      fetchCategories()
    ])
    
    // Load category details for all games immediately
    await loadGameCategories()
  }
})

const selectGame = (gameId: number) => {
  // Navigate to dedicated ruleset selection page
  router.push(`/playthrough/game/${gameId}/rulesets`)
}

// Get category vote counts for each game
const gameCategoryMap = ref<Map<number, Set<number>>>(new Map())
const gameCategoryDetails = ref<Map<number, any[]>>(new Map()) // Stores full category details with vote counts
const categoriesLoaded = ref(false)

// Load category votes for all games (only when user interacts with filters)
const loadGameCategories = async () => {
  if (categoriesLoaded.value) return
  
  categoriesLoaded.value = true
  const map = new Map<number, Set<number>>()
  const detailsMap = new Map<number, any[]>()
  
  const promises = games.value
    .map(async (game) => {
      try {
        const categories = await getGameCategories(game.id)
        // Show all categories, regardless of vote count (since association is separate from votes now)
        const categoryIds = new Set(categories.map(c => c.id))
        return { gameId: game.id, categoryIds, categories }
      } catch (err) {
        console.error(`Failed to load categories for game ${game.id}:`, err)
        return { gameId: game.id, categoryIds: new Set<number>(), categories: [] }
      }
    })
  
  const results = await Promise.all(promises)
  results.forEach(({ gameId, categoryIds, categories }) => {
    map.set(gameId, categoryIds)
    detailsMap.set(gameId, categories)
  })
  
  gameCategoryMap.value = map
  gameCategoryDetails.value = detailsMap
}

// Re-randomize when search query changes (if random mode is active)
watch(searchQuery, () => {
  if (showRandomGames.value) {
    randomSeed.value++
  }
})

// Calculate actual game counts per category based on votes
const categoryGameCounts = computed(() => {
  const counts = new Map<number, number>()
  
  categories.value.forEach(cat => {
    let count = 0
    games.value.forEach(game => {
      const gameCategories = gameCategoryMap.value.get(game.id)
      if (gameCategories?.has(cat.id)) {
        count++
      }
    })
    counts.set(cat.id, count)
  })
  
  return counts
})

// Filter games based on search query and selected categories
const filteredGames = computed(() => {
  // Include ALL games (including category representatives)
  let filtered = [...games.value]
  
  // Filter by favorites (exclude category representatives from favorites filter)
  if (showFavorites.value) {
    filtered = filtered.filter(game => game.isFavorited && !game.isCategoryRepresentative)
  }
  
  // Filter by selected categories
  if (selectedCategories.value.size > 0) {
    filtered = filtered.filter(game => {
      // Category representative games should appear if their category is selected
      if (game.isCategoryRepresentative) {
        // Get the category this representative game belongs to
        const gameCategories = gameCategoryMap.value.get(game.id)
        if (!gameCategories || gameCategories.size === 0) return false
        
        // Show if ANY of its categories are selected
        return Array.from(selectedCategories.value).some(catId => 
          gameCategories.has(catId)
        )
      }
      
      // Regular games use existing logic
      const gameCategories = gameCategoryMap.value.get(game.id)
      if (!gameCategories || gameCategories.size === 0) return false
      
      if (filterMode.value === 'AND') {
        // Game must have ALL selected categories
        return Array.from(selectedCategories.value).every(catId => 
          gameCategories.has(catId)
        )
      } else {
        // Game must have AT LEAST ONE selected category
        return Array.from(selectedCategories.value).some(catId => 
          gameCategories.has(catId)
        )
      }
    })
  }
  
  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(game => 
      game.name.toLowerCase().includes(query) ||
      game.description?.toLowerCase().includes(query)
    )
  }
  
  // If "I Feel Lucky" is active, take 5 random games from the filtered pool (exclude category representatives)
  if (showRandomGames.value && filtered.length > 0) {
    const nonRepGames = filtered.filter(game => !game.isCategoryRepresentative)
    if (nonRepGames.length > 0) {
      // Use randomSeed to ensure re-computation when button is clicked again
      const seed = randomSeed.value
      const shuffled = [...nonRepGames].sort(() => Math.random() - 0.5)
      return shuffled.slice(0, 5)
    }
  }
  
  // Sort alphabetically by name
  return filtered.sort((a, b) => a.name.localeCompare(b.name))
})

// Paginated games
const paginatedGames = computed(() => {
  const start = (currentPage.value - 1) * gamesPerPage
  const end = start + gamesPerPage
  return filteredGames.value.slice(start, end)
})

// Calculate total pages
const totalPages = computed(() => {
  return Math.ceil(filteredGames.value.length / gamesPerPage)
})

// Generate page numbers for pagination
const pageNumbers = computed(() => {
  const pages: (number | string)[] = []
  const maxVisible = 7
  
  if (totalPages.value <= maxVisible) {
    // Show all pages
    for (let i = 1; i <= totalPages.value; i++) {
      pages.push(i)
    }
  } else {
    // Smart pagination with ellipsis
    if (currentPage.value <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages.value)
    } else if (currentPage.value >= totalPages.value - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = totalPages.value - 4; i <= totalPages.value; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = currentPage.value - 1; i <= currentPage.value + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages.value)
    }
  }
  
  return pages
})

// Go to specific page
const goToPage = (page: number) => {
  currentPage.value = page
  // Scroll to top of game grid
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Reset to page 1 when filters change
watch([searchQuery, selectedCategories, showRandomGames, showFavorites], () => {
  currentPage.value = 1
})

const toggleCategoryFilter = (categoryId: number) => {
  if (selectedCategories.value.has(categoryId)) {
    selectedCategories.value.delete(categoryId)
  } else {
    selectedCategories.value.add(categoryId)
  }
  
  // If random mode is active, re-randomize with new filters
  if (showRandomGames.value) {
    randomSeed.value++
  }
  
  // Trigger reactivity
  selectedCategories.value = new Set(selectedCategories.value)
}

const clearFilters = () => {
  selectedCategories.value.clear()
  selectedCategories.value = new Set()
  showRandomGames.value = false
  showFavorites.value = false
  searchQuery.value = ''
}

const toggleFavoritesFilter = () => {
  showFavorites.value = !showFavorites.value
  showRandomGames.value = false // Disable random mode when toggling favorites
}

// Handle favorite toggling from game cards
const handleFavoriteToggle = async (gameId: number) => {
  if (!isAuthenticated.value) {
    alert('Please log in to favorite games')
    return
  }
  
  try {
    const isFavorited = await toggleFavorite(gameId)
    
    // Update the game's favorite status in the games array
    const game = games.value.find(g => g.id === gameId)
    if (game) {
      game.isFavorited = isFavorited
    }
  } catch (err) {
    console.error('Failed to toggle favorite:', err)
    alert('Failed to toggle favorite. Please try again.')
  }
}

const toggleFilterMode = () => {
  filterMode.value = filterMode.value === 'AND' ? 'OR' : 'AND'
  
  // If random mode is active, re-randomize with new filter mode
  if (showRandomGames.value) {
    randomSeed.value++
  }
}

const selectRandomGames = () => {
  // Toggle random mode on/off
  showRandomGames.value = !showRandomGames.value
  
  // If enabling, trigger randomization
  if (showRandomGames.value) {
    randomSeed.value++
  }
}

// Handle voting from game cards
const handleVote = async (payload: { gameId: number; categoryId: number; voteType: 1 | -1; currentVoteType: number | null }) => {
  const { gameId, categoryId, voteType, currentVoteType } = payload
  
  if (!isAuthenticated.value) {
    alert('Please log in to vote')
    return
  }
  
  try {
    const result = await toggleVote(gameId, categoryId, voteType, currentVoteType)
    
    // Update gameCategoryDetails for the game
    const gameDetails = gameCategoryDetails.value.get(gameId) || []
    const detailCategory = gameDetails.find(c => c.id === categoryId)
    if (detailCategory) {
      detailCategory.voteCount = result.voteCount
      detailCategory.userVoted = result.userVoted
      detailCategory.userVoteType = result.userVoteType
    }
    
    // Only remove category from game if it has -50 or fewer votes (net)
    if (result.voteCount <= -50) {
      const categoryIds = gameCategoryMap.value.get(gameId)
      if (categoryIds) {
        categoryIds.delete(categoryId)
      }
      // Remove from details
      const updatedDetails = gameDetails.filter(c => c.id !== categoryId)
      gameCategoryDetails.value.set(gameId, updatedDetails)
    } else {
      // If this was a new vote and category wasn't in the map, add it
      const categoryIds = gameCategoryMap.value.get(gameId)
      if (categoryIds && result.voteCount > -50) {
        categoryIds.add(categoryId)
      }
    }
  } catch (err) {
    console.error('Failed to toggle vote:', err)
    alert('Failed to vote. Please try again.')
  }
}

</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">Select a Game</h1>
        <p class="text-gray-300">Choose your game to start a new playthrough</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
        <p class="text-white mt-4">Loading...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/20 border border-red-300 rounded-lg p-6 text-center">
        <p class="text-white">{{ error }}</p>
      </div>

      <!-- Game Selection -->
      <div v-else>
        <!-- Category Filter Section -->
        <CategoryFilterList
          v-if="categories.length > 0"
          class="mb-6"
          :categories="categories"
          :selected-categories="selectedCategories"
          :filter-mode="filterMode"
          :show-random-games="showRandomGames"
          :category-game-counts="categoryGameCounts"
          @toggle-category="toggleCategoryFilter"
          @toggle-filter-mode="toggleFilterMode"
          @clear-filters="clearFilters"
          @reroll-random="randomSeed++"
        />

        <!-- Search Bar with I Feel Lucky Button -->
        <div class="mb-6 max-w-2xl mx-auto">
          <div class="flex items-center gap-3">
            <div class="relative flex-1">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search games..."
                class="w-full px-4 py-3 pl-12 rounded-lg bg-gray-800/80 backdrop-blur-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan border border-gray-700 shadow-lg"
              />
              <svg 
                class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            
            <!-- Favorites Button -->
            <button
              @click="toggleFavoritesFilter"
              :class="[
                'px-4 py-3 text-sm font-semibold rounded-lg transition-all whitespace-nowrap flex items-center gap-2',
                showFavorites
                  ? 'bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white shadow-lg hover:shadow-xl ring-2 ring-yellow-400'
                  : 'bg-gray-700 hover:bg-gradient-to-r hover:from-yellow-500 hover:to-orange-500 text-gray-300 hover:text-white shadow-md hover:shadow-lg'
              ]"
              :title="showFavorites ? 'Show all games' : 'Show only favorite games'"
            >
              <Icon name="heroicons:star-solid" class="w-4 h-4" />
              <span>Favorites</span>
            </button>
            
            <!-- I Feel Lucky Button -->
            <button
              @click="selectRandomGames"
              :class="[
                'px-4 py-3 text-sm font-semibold rounded-lg transition-all whitespace-nowrap flex items-center gap-2',
                showRandomGames
                  ? 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-lg hover:shadow-xl ring-2 ring-purple-400'
                  : 'bg-gray-700 hover:bg-gradient-to-r hover:from-purple-600 hover:to-pink-600 text-gray-300 hover:text-white shadow-md hover:shadow-lg'
              ]"
              :title="showRandomGames ? 'Click to turn off random mode' : 'Show 5 random games based on current filters'"
            >
              <Icon name="heroicons:sparkles" class="w-4 h-4" />
              <span>I Feel Lucky</span>
            </button>
          </div>
          <p v-if="(searchQuery || selectedCategories.size > 0) && filteredGames.length === 0" class="text-center text-white mt-4">
            No games found matching your filters
          </p>
          <p v-else-if="searchQuery || selectedCategories.size > 0 || showRandomGames || filteredGames.length > 0" class="text-center text-white/80 mt-2 text-sm">
            <span v-if="showRandomGames" class="text-magenta font-semibold">🎲 Random Pick: </span>
            <span v-if="totalPages > 1">
              Showing {{ (currentPage - 1) * gamesPerPage + 1 }}-{{ Math.min(currentPage * gamesPerPage, filteredGames.length) }} of 
            </span>
            {{ filteredGames.length }} game{{ filteredGames.length !== 1 ? 's' : '' }}
            <span v-if="searchQuery || selectedCategories.size > 0"> found</span>
          </p>
        </div>

        <!-- Game Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <GameCard
            v-for="game in paginatedGames"
            :key="game.id"
            :game="game"
            :categories="gameCategoryDetails.get(game.id) || []"
            :selected-category-ids="selectedCategories"
            :all-categories="categories"
            :is-authenticated="isAuthenticated"
            @select="selectGame"
            @vote="handleVote"
            @toggle-favorite="handleFavoriteToggle"
          />
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-8">
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
                  ? 'bg-gradient-to-r from-cyan to-magenta text-white'
                  : 'bg-gray-700 hover:bg-gray-600 text-white'
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="px-2 text-gray-500">...</span>
          </template>
          
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Icon name="heroicons:chevron-right" class="w-5 h-5" />
          </button>
        </div>
      </div>

  </div>
</template>


