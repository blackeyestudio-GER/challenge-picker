<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePlaythrough } from '~/composables/usePlaythrough'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const { fetchGames, fetchRulesets, createPlaythrough, games, rulesets, loading, error } = usePlaythrough()

const gameId = computed(() => parseInt(route.params.gameId as string))
const selectedRuleset = ref<number | null>(null)
const maxConcurrentRules = ref(3)
const creating = ref(false)

const game = computed(() => games.value.find(g => g.id === gameId.value))

onMounted(async () => {
  // Fetch games to get game details
  if (games.value.length === 0) {
    await fetchGames()
  }
  
  // Verify game exists
  if (!game.value) {
    // Game not found, redirect back to game selection
    router.push('/playthrough/new')
    return
  }
  
  // Fetch rulesets for this game
  await fetchRulesets(gameId.value)
})

const back = () => {
  router.push('/playthrough/new')
}

const createSession = async (rulesetId: number) => {
  creating.value = true
  
  try {
    const playthrough = await createPlaythrough(
      gameId.value,
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
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
      <button
        @click="back"
        class="mb-4 text-gray-300 hover:text-white flex items-center transition"
      >
        <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
        <span>Back to games</span>
      </button>

      <div v-if="game" class="bg-gradient-to-r from-cyan-muted/20 to-magenta-muted/20 rounded-xl p-6 border border-cyan/20">
        <div class="flex items-center gap-4">
          <div v-if="game.image" class="w-20 h-20 rounded-lg overflow-hidden bg-gray-900 flex items-center justify-center flex-shrink-0">
            <img :src="game.image" :alt="game.name" class="max-h-full max-w-full object-contain" />
          </div>
          <div v-else class="w-20 h-20 rounded-lg bg-gray-900 flex items-center justify-center flex-shrink-0">
            <span class="text-4xl">🎮</span>
          </div>
          
          <div class="flex-1">
            <h1 class="text-3xl font-extrabold text-white mb-1">{{ game.name }}</h1>
            <p v-if="game.description" class="text-gray-300 text-sm">{{ game.description }}</p>
            <p class="text-cyan text-sm mt-2">Select a ruleset to start your playthrough</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-cyan border-t-transparent"></div>
      <p class="text-gray-400 mt-4">Loading rulesets...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/50 rounded-lg p-6 text-center">
      <p class="text-red-400">{{ error }}</p>
      <button @click="back" class="mt-4 px-6 py-2 bg-red-500/20 hover:bg-red-500/30 rounded-lg transition">
        Go Back
      </button>
    </div>

    <!-- Rulesets Grid -->
    <div v-else-if="rulesets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <button
        v-for="ruleset in rulesets"
        :key="ruleset.id"
        @click="selectedRuleset = ruleset.id"
        :class="[
          'bg-gray-800/80 backdrop-blur-sm border rounded-xl p-6 hover:border-cyan hover:shadow-xl hover:shadow-cyan/20 transition-all transform hover:-translate-y-1 text-left',
          selectedRuleset === ruleset.id ? 'border-cyan shadow-xl shadow-cyan/30 ring-2 ring-cyan/50' : 'border-gray-700'
        ]"
      >
        <div class="flex items-start justify-between mb-3">
          <h3 class="text-xl font-bold text-white flex-1">{{ ruleset.name }}</h3>
          <div 
            v-if="selectedRuleset === ruleset.id"
            class="flex-shrink-0 ml-3 bg-cyan/20 rounded-full p-2"
          >
            <Icon name="heroicons:check" class="w-5 h-5 text-cyan" />
          </div>
        </div>
        
        <p v-if="ruleset.description" class="text-gray-400 text-sm mb-4">{{ ruleset.description }}</p>
        
        <div class="flex items-center justify-between text-sm">
          <div class="flex items-center text-cyan font-medium">
            <Icon name="heroicons:list-bullet" class="w-4 h-4 mr-1" />
            <span>{{ ruleset.rules?.length || 0 }} rule{{ (ruleset.rules?.length || 0) !== 1 ? 's' : '' }}</span>
          </div>
          
          <div v-if="ruleset.isDefault" class="text-gray-500 italic text-xs">
            Default
          </div>
        </div>
      </button>
    </div>

    <!-- No Rulesets State -->
    <div v-else class="bg-gray-800/60 backdrop-blur-sm rounded-xl p-12 text-center border border-gray-700">
      <Icon name="heroicons:exclamation-triangle" class="w-16 h-16 mx-auto text-yellow-500 mb-4" />
      <h3 class="text-xl font-bold text-white mb-2">No Rulesets Available</h3>
      <p class="text-gray-400 mb-6">This game doesn't have any rulesets yet.</p>
      <button @click="back" class="px-6 py-3 bg-cyan hover:bg-cyan-dark rounded-lg transition text-white font-semibold">
        Choose Another Game
      </button>
    </div>

    <!-- Action Buttons -->
    <div v-if="rulesets.length > 0" class="mt-8 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <label class="text-gray-300 text-sm">Max concurrent rules:</label>
        <input
          v-model.number="maxConcurrentRules"
          type="number"
          min="1"
          max="10"
          class="w-20 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-cyan"
        />
      </div>

      <button
        @click="createSession(selectedRuleset!)"
        :disabled="!selectedRuleset || creating"
        class="px-8 py-4 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-cyan/50 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
      >
        <span v-if="creating">Creating Session...</span>
        <span v-else>Start Playthrough</span>
      </button>
    </div>
  </div>
</template>

