<script setup lang="ts">
import GameCard from '~/components/GameCard.vue'
import CategoryFilterList from '~/components/CategoryFilterList.vue'
import { useFavorites } from '~/composables/useFavorites'

definePageMeta({
  middleware: 'auth'
})

const { fetchGames, fetchRulesets, createPlaythrough, fetchActivePlaythrough, games, rulesets, activePlaythrough, loading, error } = usePlaythrough()
const { categories, fetchCategories } = useCategories()
const { getGameCategories, toggleVote } = useGameCategories()
const { toggleFavorite } = useFavorites()
const { isAuthenticated, loadAuth } = useAuth()
const router = useRouter()

const step = ref<1 | 2>(1)
const selectedGame = ref<number | null>(null)
const selectedRuleset = ref<number | null>(null)
const maxConcurrentRules = ref(3)
const creating = ref(false)
const searchQuery = ref('')
const selectedCategories = ref<Set<number>>(new Set())
const filterMode = ref<'AND' | 'OR'>('AND')
const showRandomGames = ref(false)
const showFavorites = ref(false)
const randomSeed = ref(0) // Used to trigger re-randomization

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

const selectGame = async (gameId: number) => {
  selectedGame.value = gameId
  await fetchRulesets(gameId)
  step.value = 2
}

const back = () => {
  step.value = 1
  selectedRuleset.value = null
}

const createSession = async (rulesetId: number) => {
  if (!selectedGame.value) return
  
  creating.value = true
  
  try {
    const playthrough = await createPlaythrough(
      selectedGame.value,
      rulesetId,
      maxConcurrentRules.value
    )
    
    // Redirect to setup page
    router.push(`/playthrough/${playthrough.uuid}/setup`)
  } catch (err) {
    console.error('Failed to create playthrough:', err)
  } finally {
    creating.value = false
  }
}

const selectedGameData = computed(() => 
  games.value.find(g => g.id === selectedGame.value)
)

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
    .filter(game => !game.isCategoryRepresentative)
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
  let filtered = games.value.filter(game => !game.isCategoryRepresentative)
  
  // Filter by favorites
  if (showFavorites.value) {
    filtered = filtered.filter(game => game.isFavorited)
  }
  
  // Filter by selected categories
  if (selectedCategories.value.size > 0) {
    filtered = filtered.filter(game => {
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
  
  // If "I Feel Lucky" is active, take 5 random games from the filtered pool
  if (showRandomGames.value && filtered.length > 0) {
    // Use randomSeed to ensure re-computation when button is clicked again
    const seed = randomSeed.value
    const shuffled = [...filtered].sort(() => Math.random() - 0.5)
    return shuffled.slice(0, 5)
  }
  
  return filtered
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
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">Create New Session</h1>
        <p class="text-gray-300">
          <span v-if="step === 1">Select a game to play</span>
          <span v-else>Choose a ruleset for {{ selectedGameData?.name }}</span>
        </p>
      </div>

      <!-- Step Indicator -->
      <div class="flex justify-center mb-8">
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-bold',
              step === 1 ? 'bg-gradient-to-br from-cyan to-magenta text-white' : 'bg-gray-700 text-gray-400'
            ]">
              1
            </div>
            <span class="ml-2 text-white font-medium">Select Game</span>
          </div>
          <div :class="['w-16 h-1', step === 2 ? 'bg-gradient-to-r from-cyan to-magenta' : 'bg-gray-700']"></div>
          <div class="flex items-center">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-bold',
              step === 2 ? 'bg-gradient-to-br from-cyan to-magenta text-white' : 'bg-gray-700 text-gray-400'
            ]">
              2
            </div>
            <span class="ml-2 text-white font-medium">Select Ruleset</span>
          </div>
        </div>
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

      <!-- Step 1: Select Game -->
      <div v-else-if="step === 1">
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
          <p v-else-if="searchQuery || selectedCategories.size > 0 || showRandomGames" class="text-center text-white/80 mt-2 text-sm">
            <span v-if="showRandomGames" class="text-magenta font-semibold">🎲 Random Pick: </span>
            {{ filteredGames.length }} game{{ filteredGames.length !== 1 ? 's' : '' }} found
          </p>
        </div>

        <!-- Game Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <GameCard
            v-for="game in filteredGames"
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
      </div>

      <!-- Step 2: Select Ruleset -->
      <div v-else-if="step === 2">
        <button
          @click="back"
          class="mb-6 text-gray-300 hover:text-white flex items-center"
        >
          <span class="mr-2">←</span> Back to games
        </button>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <button
            v-for="ruleset in rulesets"
            :key="ruleset.id"
            @click="createSession(ruleset.id)"
            :disabled="creating"
            class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-magenta hover:shadow-xl hover:shadow-magenta/20 transition-all transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <div class="mb-4">
              <h3 class="text-xl font-bold text-white mb-2">{{ ruleset.name }}</h3>
              <p v-if="ruleset.description" class="text-gray-400 text-sm mb-3">{{ ruleset.description }}</p>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-magenta font-medium">{{ ruleset.ruleCount }} rules</span>
              <span v-if="creating" class="text-gray-400">Creating...</span>
              <span v-else class="text-gray-400">Select →</span>
            </div>
          </button>
        </div>
      </div>
  </div>
</template>


