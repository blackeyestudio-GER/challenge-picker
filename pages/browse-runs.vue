<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'

// Define types locally
interface Game {
  id: number
  name: string
}

interface Ruleset {
  id: number
  name: string
  gameId: number
}

interface Playthrough {
  id: number
  uuid: string
  username: string
  gameName: string
  gameId: number
  rulesetName: string
  endedAt: string | null
  totalDuration: number | null
  videoUrl: string | null
}

const { token, isAuthenticated } = useAuth()

// For game/ruleset filters - we'll create these composables
const games = ref<Game[]>([])
const rulesets = ref<Ruleset[]>([])

const fetchGames = async () => {
  try {
    const response = await $fetch<{ success: boolean; data: { games: Game[] } }>(
      'http://localhost:8090/api/games'
    )
    games.value = response.data.games
  } catch (err) {
    console.error('Failed to fetch games:', err)
  }
}

const fetchRulesets = async (gameId: number) => {
  try {
    const response = await $fetch<{ success: boolean; data: { rulesets: Ruleset[] } }>(
      `http://localhost:8090/api/games/${gameId}/rulesets`
    )
    rulesets.value = response.data.rulesets
  } catch (err) {
    console.error('Failed to fetch rulesets:', err)
  }
}

interface PlaythroughWithFlag extends Playthrough {
  isOwnRun: boolean
  hasPlayedGame: boolean
}

const runs = ref<PlaythroughWithFlag[]>([])
const loading = ref(true)
const selectedGameId = ref<number | null>(null)
const selectedRulesetId = ref<number | null>(null)

// Computed filtered rulesets based on selected game
const filteredRulesets = computed(() => {
  if (!selectedGameId.value) return []
  return rulesets.value.filter(r => r.gameId === selectedGameId.value)
})

onMounted(async () => {
  // Load games for filter
  await fetchGames()
  
  // Load initial runs
  await loadRuns()
})

const loadRuns = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (selectedGameId.value) {
      params.append('gameId', selectedGameId.value.toString())
    }
    if (selectedRulesetId.value) {
      params.append('rulesetId', selectedRulesetId.value.toString())
    }

    const queryString = params.toString()
    const url = `http://localhost:8090/api/playthrough/browse${queryString ? '?' + queryString : ''}`
    
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
    }
    
    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }

    const response = await $fetch<{ success: boolean; data: { playthroughs: PlaythroughWithFlag[] } }>(
      url,
      { headers }
    )
    runs.value = response.data.playthroughs
  } catch (err) {
    console.error('Failed to load runs:', err)
  } finally {
    loading.value = false
  }
}

const onGameChange = async () => {
  selectedRulesetId.value = null
  
  // Load rulesets for selected game
  if (selectedGameId.value) {
    await fetchRulesets(selectedGameId.value)
  }
  
  await loadRuns()
}

const onRulesetChange = async () => {
  await loadRuns()
}

const clearFilters = async () => {
  selectedGameId.value = null
  selectedRulesetId.value = null
  await loadRuns()
}

const formatDuration = (seconds: number | null) => {
  if (!seconds) return 'N/A'
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  if (hours > 0) {
    return `${hours}h ${mins}m ${secs}s`
  }
  return `${mins}m ${secs}s`
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const extractVideoId = (url: string | null): { platform: 'youtube' | 'twitch' | null; id: string | null } => {
  if (!url) return { platform: null, id: null }
  
  // YouTube patterns
  const youtubePatterns = [
    /youtube\.com\/watch\?v=([\w-]+)/,
    /youtu\.be\/([\w-]+)/,
    /youtube\.com\/embed\/([\w-]+)/,
    /youtube\.com\/v\/([\w-]+)/
  ]
  
  for (const pattern of youtubePatterns) {
    const match = url.match(pattern)
    if (match) {
      return { platform: 'youtube', id: match[1] }
    }
  }
  
  // Twitch patterns
  const twitchPatterns = [
    /twitch\.tv\/videos\/(\d+)/,
    /twitch\.tv\/[\w-]+\/clip\/([\w-]+)/,
    /clips\.twitch\.tv\/([\w-]+)/
  ]
  
  for (const pattern of twitchPatterns) {
    const match = url.match(pattern)
    if (match) {
      return { platform: 'twitch', id: match[1] }
    }
  }
  
  return { platform: null, id: null }
}

// No need to separate runs anymore - we'll show all in one list with visual indicators
</script>

<template>
  <div class="runs-page">
    <!-- Header -->
    <div class="runs-page__header">
      <h1 class="runs-page__title">
        Browse Challenge Runs
      </h1>
      <p class="runs-page__description">Discover the latest 25 completed challenge runs with videos</p>
      <p class="runs-page__legend">
        <span class="runs-page__legend-item">
          <span class="runs-page__legend-dot runs-page__legend-dot--yellow"></span>
          Yellow border = Your run
        </span>
        <span class="runs-page__legend-item">
          <span class="runs-page__legend-dot runs-page__legend-dot--cyan"></span>
          Cyan border = Games you've played
        </span>
      </p>
    </div>

    <!-- Filters -->
    <div class="runs-page__filters">
      <div class="runs-page__filters-header">
        <Icon name="heroicons:funnel" class="runs-page__filters-icon" />
        <h2 class="runs-page__filters-title">Filters</h2>
      </div>
      
      <div class="runs-page__filters-grid">
        <!-- Game Filter -->
        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">Game</label>
          <select
            v-model="selectedGameId"
            @change="onGameChange"
            class="runs-page__filter-select"
          >
            <option :value="null">All Games</option>
            <option v-for="game in games" :key="game.id" :value="game.id">
              {{ game.name }}
            </option>
          </select>
        </div>

        <!-- Ruleset Filter -->
        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">Ruleset</label>
          <select
            v-model="selectedRulesetId"
            @change="onRulesetChange"
            :disabled="!selectedGameId"
            class="runs-page__filter-select"
          >
            <option :value="null">All Rulesets</option>
            <option v-for="ruleset in filteredRulesets" :key="ruleset.id" :value="ruleset.id">
              {{ ruleset.name }}
            </option>
          </select>
        </div>

        <!-- Clear Filters -->
        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">&nbsp;</label>
          <button
            @click="clearFilters"
            :disabled="!selectedGameId && !selectedRulesetId"
            class="runs-page__filter-clear"
          >
            <Icon name="heroicons:x-mark" class="runs-page__filter-clear-icon" />
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="runs-page__loading">
      <div class="runs-page__loading-spinner"></div>
      <p class="runs-page__loading-text">Loading runs...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="runs.length === 0" class="runs-page__empty">
      <Icon name="heroicons:film" class="runs-page__empty-icon" />
      <p class="runs-page__empty-message">No runs found</p>
      <p class="runs-page__hint">Try adjusting your filters or check back later</p>
    </div>

    <!-- Runs List -->
    <div v-else class="runs-page__list">
      <div
        v-for="run in runs"
        :key="run.id"
        class="runs-page__run-card"
        :class="{
          'runs-page__run-card--highlight-yellow': run.isOwnRun,
          'runs-page__run-card--highlight-cyan': run.hasPlayedGame && !run.isOwnRun
        }"
      >
        <div class="runs-page__run-header">
          <div class="runs-page__run-content">
            <div class="runs-page__run-title-row">
              <Icon
                name="heroicons:trophy"
                class="runs-page__run-icon"
                :class="{
                  'text-yellow-500': run.isOwnRun,
                  'text-cyan': run.hasPlayedGame && !run.isOwnRun,
                  'text-gray-400': !run.isOwnRun && !run.hasPlayedGame
                }"
              />
              <h3 class="runs-page__run-game">{{ run.gameName }}</h3>
              
              <!-- Your Run Badge -->
              <span
                v-if="run.isOwnRun"
                class="px-3 py-1 bg-yellow-500/20 text-yellow-500 text-xs font-semibold rounded-full"
              >
                YOUR RUN
              </span>
              
              <!-- You've Played This Game Badge -->
              <span
                v-else-if="run.hasPlayedGame"
                class="px-3 py-1 bg-cyan/20 text-cyan text-xs font-semibold rounded-full flex items-center gap-1"
              >
                <Icon name="heroicons:check-circle" class="w-3 h-3" />
                You've played this
              </span>
            </div>
            
            <p class="text-gray-300 mb-2">{{ run.rulesetName }}</p>
            
            <div class="flex items-center gap-4 text-sm text-gray-400 mb-3">
              <span class="flex items-center gap-1">
                <Icon name="heroicons:user" class="w-4 h-4" />
                {{ run.username }}
              </span>
              <span class="flex items-center gap-1">
                <Icon name="heroicons:calendar" class="w-4 h-4" />
                {{ formatDate(run.endedAt!) }}
              </span>
              <span class="flex items-center gap-1">
                <Icon name="heroicons:clock" class="w-4 h-4" />
                {{ formatDuration(run.totalDuration) }}
              </span>
            </div>
            
            <a
              :href="run.videoUrl!"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 text-cyan hover:text-cyan-light transition-colors"
            >
              <Icon
                :name="extractVideoId(run.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:video-camera'"
                class="w-5 h-5"
                :class="extractVideoId(run.videoUrl).platform === 'youtube' ? 'text-red-500' : 'text-purple-500'"
              />
              <span class="underline">Watch on {{ extractVideoId(run.videoUrl).platform === 'youtube' ? 'YouTube' : 'Twitch' }}</span>
              <Icon name="heroicons:arrow-top-right-on-square" class="w-4 h-4" />
            </a>
          </div>
          
          <div class="flex items-center gap-2 ml-4">
            <NuxtLink
              :to="`/runs/${run.uuid}`"
              :class="[
                'px-4 py-2 rounded-lg transition-all flex items-center gap-2',
                run.isOwnRun
                  ? 'bg-yellow-600 hover:bg-yellow-700 text-white'
                  : 'bg-cyan hover:bg-cyan-dark text-white'
              ]"
            >
              <Icon name="heroicons:share" class="w-5 h-5" />
              Share
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

