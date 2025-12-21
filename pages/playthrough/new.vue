<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { fetchGames, fetchRulesets, createPlaythrough, fetchActivePlaythrough, games, rulesets, activePlaythrough, loading, error } = usePlaythrough()
const { categories, fetchCategories } = useCategories()
const { getGameCategories, toggleVote } = useGameCategories()
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
const showCategoryModal = ref(false)
const selectedGameForCategories = ref<number | null>(null)
const gameCategories = ref<any[]>([])
const loadingCategories = ref(false)
const showRandomGames = ref(false)
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
        const categoryIds = new Set(categories.filter(c => c.voteCount > 0).map(c => c.id))
        return { gameId: game.id, categoryIds, categories: categories.filter(c => c.voteCount > 0) }
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

// Sort categories with selected ones first
const sortedCategories = computed(() => {
  return [...categories.value].sort((a, b) => {
    const aSelected = selectedCategories.value.has(a.id)
    const bSelected = selectedCategories.value.has(b.id)
    
    if (aSelected && !bSelected) return -1
    if (!aSelected && bSelected) return 1
    return 0 // Keep original order for same selection state
  })
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
  searchQuery.value = ''
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

const openCategoryModal = async (gameId: number) => {
  selectedGameForCategories.value = gameId
  showCategoryModal.value = true
  loadingCategories.value = true
  
  try {
    gameCategories.value = await getGameCategories(gameId)
  } catch (err) {
    console.error('Failed to load categories:', err)
    gameCategories.value = []
  } finally {
    loadingCategories.value = false
  }
}

const closeCategoryModal = () => {
  showCategoryModal.value = false
  selectedGameForCategories.value = null
  gameCategories.value = []
}

// Handle quick voting from game cards
const handleQuickVote = async (event: Event, gameId: number, categoryId: number, currentlyVoted: boolean) => {
  event.stopPropagation() // Prevent game selection
  
  if (!isAuthenticated.value) {
    alert('Please log in to vote')
    return
  }
  
  try {
    const result = await toggleVote(gameId, categoryId, currentlyVoted)
    
    // Update gameCategoryDetails for the game
    const gameDetails = gameCategoryDetails.value.get(gameId) || []
    const detailCategory = gameDetails.find(c => c.id === categoryId)
    if (detailCategory) {
      detailCategory.voteCount = result.voteCount
      detailCategory.userVoted = result.userVoted
    }
    
    // If vote count dropped to 0 or below, remove the category from the game
    if (result.voteCount <= 0) {
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
      if (categoryIds && result.voteCount > 0) {
        categoryIds.add(categoryId)
      }
    }
  } catch (err) {
    console.error('Failed to toggle vote:', err)
    alert('Failed to vote. Please try again.')
  }
}

const handleVoteToggle = async (categoryId: number, currentlyVoted: boolean) => {
  if (!selectedGameForCategories.value) return
  if (!isAuthenticated.value) {
    alert('Please log in to vote')
    return
  }
  
  try {
    const result = await toggleVote(selectedGameForCategories.value, categoryId, currentlyVoted)
    
    // Update the local state
    const category = gameCategories.value.find(c => c.id === categoryId)
    if (category) {
      category.voteCount = result.voteCount
      category.userVoted = result.userVoted
    }
    
    // Update gameCategoryDetails for the game
    const gameDetails = gameCategoryDetails.value.get(selectedGameForCategories.value) || []
    const detailCategory = gameDetails.find(c => c.id === categoryId)
    if (detailCategory) {
      detailCategory.voteCount = result.voteCount
      detailCategory.userVoted = result.userVoted
    }
  } catch (err: any) {
    if (err.data?.error?.code === 'ALREADY_VOTED') {
      alert('You have already voted for this category')
    } else {
      alert('Failed to update vote. Please try again.')
    }
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
        <div v-if="categories.length > 0" class="mb-6 bg-gray-800/60 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
          <div class="flex items-center justify-between mb-4">
            <!-- Header with Clear Button -->
            <div class="flex items-center gap-3">
              <h3 class="text-white text-lg font-semibold">Filter by Tags</h3>
              <button
                v-if="selectedCategories.size > 0 || showRandomGames"
                @click="clearFilters"
                class="text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all rounded-full p-1"
                title="Clear all filters"
              >
                <Icon name="heroicons:x-mark" class="w-5 h-5" />
              </button>
            </div>
            
            <!-- Filter Mode Toggle -->
            <div v-if="selectedCategories.size > 1" class="flex items-center gap-2">
              <span class="text-xs text-gray-400">Match:</span>
              <button
                @click="toggleFilterMode"
                :class="[
                  'relative inline-flex h-6 w-24 items-center rounded-full transition-colors',
                  filterMode === 'OR' ? 'bg-cyan' : 'bg-magenta'
                ]"
              >
                <span
                  :class="[
                    'flex h-4 w-10 transform rounded-full bg-white transition-transform text-xs font-bold items-center justify-center',
                    filterMode === 'OR' ? 'translate-x-1' : 'translate-x-[52px]'
                  ]"
                >
                  {{ filterMode }}
                </span>
              </button>
              <span class="text-xs text-gray-400">
                {{ filterMode === 'OR' ? '(Any tag)' : '(All tags)' }}
              </span>
            </div>
          </div>

          <!-- Category Chips -->
          <div class="flex flex-wrap gap-2 mb-4">
            <!-- Random Filter Indicator (shown first if active) -->
            <button
              v-if="showRandomGames"
              @click="randomSeed++"
              class="px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg shadow-purple/50 border-2 border-transparent flex items-center gap-2 hover:from-purple-700 hover:to-pink-700 transition-all transform hover:scale-105 cursor-pointer"
              title="Click to re-roll 5 random games"
            >
              <Icon name="heroicons:sparkles" class="w-4 h-4" />
              <span>Random (5 games)</span>
              <Icon name="heroicons:arrow-path" class="w-3 h-3 opacity-75" />
            </button>
            
            <!-- Category Filter Buttons (selected ones appear first) -->
            <button
              v-for="category in sortedCategories"
              :key="category.id"
              @click="toggleCategoryFilter(category.id)"
              :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-all transform hover:scale-105 border-2',
                selectedCategories.has(category.id)
                  ? 'bg-gradient-to-r from-cyan to-magenta text-white shadow-lg shadow-cyan/50 border-transparent'
                  : 'bg-gray-700 text-gray-300 hover:bg-gray-600 border-gray-600'
              ]"
            >
              {{ category.name }}
              <span class="ml-2 text-xs opacity-75">({{ categoryGameCounts.get(category.id) || 0 }})</span>
            </button>
          </div>
        </div>

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
        <button
          v-for="game in filteredGames"
          :key="game.id"
          @click="selectGame(game.id)"
          class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-cyan hover:shadow-xl hover:shadow-cyan/20 transition-all transform hover:-translate-y-1"
        >
          <div v-if="game.image" class="mb-4 h-32 flex items-center justify-center bg-gray-900 rounded">
            <img :src="game.image" :alt="game.name" class="max-h-full max-w-full object-contain" />
          </div>
          <div v-else class="mb-4 h-32 flex items-center justify-center bg-gray-900 rounded">
            <span class="text-4xl">🎮</span>
          </div>
          <h3 class="text-xl font-bold text-white mb-2">{{ game.name }}</h3>
          
          <!-- Show community-voted categories with voting -->
          <div v-if="gameCategoryDetails.get(game.id)?.length" class="flex flex-wrap gap-2 mb-3">
            <div
              v-for="cat in gameCategoryDetails.get(game.id)"
              :key="cat.id"
              :class="[
                'flex items-center gap-1 px-2 py-1 rounded text-xs font-semibold border transition-all',
                selectedCategories.has(cat.id)
                  ? 'bg-gradient-to-r from-cyan/20 to-magenta/20 border-cyan text-white shadow-lg'
                  : 'bg-gray-700/80 border-gray-600 text-gray-300'
              ]"
            >
              <!-- Vote Down Button -->
              <button
                @click="handleQuickVote($event, game.id, cat.id, cat.userVoted)"
                :class="[
                  'hover:text-red-400 transition-colors px-1',
                  cat.userVoted ? 'text-red-400' : 'text-gray-500'
                ]"
                title="Vote down / Remove vote"
              >
                −
              </button>
              
              <!-- Category Name and Vote Count -->
              <span class="px-1">
                {{ categories.find(c => c.id === cat.id)?.name }}
                <span :class="cat.voteCount >= 0 ? 'text-green-400' : 'text-red-400'">
                  ({{ cat.voteCount >= 0 ? '+' : '' }}{{ cat.voteCount }})
                </span>
              </span>
              
              <!-- Vote Up Button -->
              <button
                @click="handleQuickVote($event, game.id, cat.id, cat.userVoted)"
                :class="[
                  'hover:text-green-400 transition-colors px-1',
                  cat.userVoted ? 'text-green-400' : 'text-gray-500'
                ]"
                title="Vote up"
              >
                +
              </button>
            </div>
          </div>
          
          <p v-if="game.description" class="text-gray-400 text-sm mb-3">{{ game.description }}</p>
          <div class="flex items-center justify-between gap-2">
            <button
              v-if="!game.isCategoryRepresentative"
              @click.stop="openCategoryModal(game.id)"
              class="flex items-center gap-1 text-xs text-gray-400 hover:text-cyan transition px-2 py-1 rounded hover:bg-gray-700"
            >
              <Icon name="heroicons:tag" class="w-4 h-4" />
              <span>Tags</span>
            </button>
            <div v-else class="text-xs text-gray-500 italic px-2 py-1">
              Category Game
            </div>
            <div class="flex items-center text-cyan text-sm font-medium">
              <span>{{ game.rulesetCount }} ruleset{{ game.rulesetCount !== 1 ? 's' : '' }}</span>
            </div>
          </div>
        </button>
        </div>

        <!-- Category Voting Modal -->
        <div
          v-if="showCategoryModal"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
          @click.self="closeCategoryModal"
        >
          <div class="bg-gray-800 rounded-xl max-w-2xl w-full p-6 border border-gray-700 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-bold text-white">Community Tags</h2>
              <button @click="closeCategoryModal" class="text-gray-400 hover:text-white transition">
                <Icon name="heroicons:x-mark" class="w-6 h-6" />
              </button>
            </div>

            <p class="text-gray-400 text-sm mb-6">
              Vote for tags that best describe this game. Your votes help the community find games they'll enjoy!
            </p>

            <div v-if="loadingCategories" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-cyan"></div>
              <p class="text-gray-400 mt-4">Loading tags...</p>
            </div>

            <div v-else-if="gameCategories.length === 0" class="text-center py-8 text-gray-400">
              No tags yet. Be the first to tag this game!
            </div>

            <div v-else class="space-y-3">
              <button
                v-for="category in gameCategories"
                :key="category.id"
                @click="handleVoteToggle(category.id, category.userVoted)"
                :class="[
                  'w-full flex items-center justify-between p-4 rounded-lg transition-all border-2',
                  category.userVoted
                    ? 'bg-gradient-to-r from-cyan/20 to-magenta/20 border-cyan'
                    : 'bg-gray-700 border-gray-600 hover:border-gray-500'
                ]"
              >
                <div class="flex items-center gap-3">
                  <Icon
                    :name="category.userVoted ? 'heroicons:heart-solid' : 'heroicons:heart'"
                    class="w-5 h-5"
                    :class="category.userVoted ? 'text-magenta' : 'text-gray-400'"
                  />
                  <div class="text-left">
                    <h3 class="font-semibold text-white">{{ category.name }}</h3>
                    <p class="text-xs text-gray-400">{{ category.slug }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <span :class="[
                    'font-bold',
                    category.userVoted ? 'text-cyan' : 'text-gray-400'
                  ]">
                    {{ category.voteCount }}
                  </span>
                  <span class="text-xs text-gray-400">
                    {{ category.voteCount === 1 ? 'vote' : 'votes' }}
                  </span>
                </div>
              </button>
            </div>

            <div class="mt-6 p-4 bg-gray-900 rounded-lg">
              <h4 class="text-sm font-semibold text-white mb-2">All Available Categories</h4>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="category in categories"
                  :key="category.id"
                  @click="handleVoteToggle(category.id, gameCategories.find(c => c.id === category.id)?.userVoted || false)"
                  :disabled="!isAuthenticated"
                  class="px-3 py-1 rounded-full text-xs border transition"
                  :class="gameCategories.find(c => c.id === category.id)
                    ? 'bg-cyan/20 border-cyan text-cyan'
                    : 'bg-gray-800 border-gray-600 text-gray-400 hover:border-gray-500'"
                >
                  {{ category.name }}
                </button>
              </div>
              <p v-if="!isAuthenticated" class="text-xs text-gray-400 mt-3">
                Log in to vote for categories
              </p>
            </div>
          </div>
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


