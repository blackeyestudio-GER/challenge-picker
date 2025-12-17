<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { fetchGames, fetchRulesets, createPlaythrough, fetchActivePlaythrough, games, rulesets, activePlaythrough, loading, error } = usePlaythrough()
const router = useRouter()

const step = ref<1 | 2>(1)
const selectedGame = ref<number | null>(null)
const selectedRuleset = ref<number | null>(null)
const maxConcurrentRules = ref(3)
const creating = ref(false)
const searchQuery = ref('')

// Check for active playthrough on mount
onMounted(async () => {
  await fetchActivePlaythrough()
  
  if (activePlaythrough.value) {
    // User already has an active session, redirect to setup
    router.push(`/playthrough/${activePlaythrough.value.uuid}/setup`)
  } else {
    // Load games
    await fetchGames()
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

// Filter games based on search query
const filteredGames = computed(() => {
  if (!searchQuery.value.trim()) {
    return games.value
  }
  
  const query = searchQuery.value.toLowerCase().trim()
  return games.value.filter(game => 
    game.name.toLowerCase().includes(query)
  )
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 py-8 px-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Create New Session</h1>
        <p class="text-indigo-100">
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
              step === 1 ? 'bg-white text-purple-600' : 'bg-purple-300 text-white'
            ]">
              1
            </div>
            <span class="ml-2 text-white font-medium">Select Game</span>
          </div>
          <div class="w-16 h-1 bg-purple-300"></div>
          <div class="flex items-center">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-bold',
              step === 2 ? 'bg-white text-purple-600' : 'bg-purple-300 text-white'
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
        <!-- Search Bar -->
        <div class="mb-6 max-w-md mx-auto">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search games..."
              class="w-full px-4 py-3 pl-12 rounded-lg bg-white/90 backdrop-blur-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white shadow-lg"
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
          <p v-if="searchQuery && filteredGames.length === 0" class="text-center text-white mt-4">
            No games found matching "{{ searchQuery }}"
          </p>
          <p v-else-if="searchQuery" class="text-center text-white/80 mt-2 text-sm">
            {{ filteredGames.length }} game{{ filteredGames.length !== 1 ? 's' : '' }} found
          </p>
        </div>

        <!-- Game Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <button
          v-for="game in filteredGames"
          :key="game.id"
          @click="selectGame(game.id)"
          class="bg-white rounded-lg p-6 hover:shadow-xl transition-all transform hover:-translate-y-1"
        >
          <div v-if="game.image" class="mb-4 h-32 flex items-center justify-center bg-gray-100 rounded">
            <img :src="game.image" :alt="game.name" class="max-h-full max-w-full object-contain" />
          </div>
          <div v-else class="mb-4 h-32 flex items-center justify-center bg-gray-100 rounded">
            <span class="text-4xl">🎮</span>
          </div>
          <h3 class="text-xl font-bold text-gray-800 mb-2">{{ game.name }}</h3>
          <p v-if="game.description" class="text-gray-600 text-sm mb-3">{{ game.description }}</p>
          <div class="flex items-center justify-center text-purple-600 text-sm font-medium">
            <span>{{ game.rulesetCount }} ruleset{{ game.rulesetCount !== 1 ? 's' : '' }}</span>
          </div>
        </button>
        </div>
      </div>

      <!-- Step 2: Select Ruleset -->
      <div v-else-if="step === 2">
        <button
          @click="back"
          class="mb-6 text-white hover:text-indigo-100 flex items-center"
        >
          <span class="mr-2">←</span> Back to games
        </button>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <button
            v-for="ruleset in rulesets"
            :key="ruleset.id"
            @click="createSession(ruleset.id)"
            :disabled="creating"
            class="bg-white rounded-lg p-6 hover:shadow-xl transition-all transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <div class="mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">{{ ruleset.name }}</h3>
              <p v-if="ruleset.description" class="text-gray-600 text-sm mb-3">{{ ruleset.description }}</p>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-purple-600 font-medium">{{ ruleset.ruleCount }} rules</span>
              <span v-if="creating" class="text-gray-500">Creating...</span>
              <span v-else class="text-gray-500">Select →</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

