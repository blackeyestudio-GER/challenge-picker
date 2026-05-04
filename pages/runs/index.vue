<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '~/composables/useAuth'
import type { BrowseRun, BrowseRunsResponse, Game } from '~/composables/usePlaythrough'
import { Icon } from '#components'
import type { Category } from '~/composables/useCategories'
import { useGameCategories } from '~/composables/useGameCategories'

const { token, loadAuth } = useAuth()
const config = useRuntimeConfig()
const { getAllGamesCategories } = useGameCategories()

const games = ref<Game[]>([])
const categories = ref<Category[]>([])
const gameCategoryMap = ref<Map<number, Set<number>>>(new Map())

const fetchGames = async () => {
  try {
    const response = await $fetch<{ success: boolean; data: Game[] }>(
      `${config.public.apiBase}/games`
    )
    games.value = response.data
  } catch (err) {
    console.error('Failed to fetch games:', err)
  }
}

const fetchCategories = async () => {
  try {
    const response = await $fetch<{ success: boolean; data: Category[] }>(
      `${config.public.apiBase}/categories`
    )
    categories.value = response.data
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  }
}

const loadGameCategories = async () => {
  try {
    const allGamesCategories = await getAllGamesCategories()
    const map = new Map<number, Set<number>>()

    Object.entries(allGamesCategories).forEach(([gameId, gameCategories]) => {
      map.set(Number(gameId), new Set(gameCategories.map(category => category.id)))
    })

    gameCategoryMap.value = map
  } catch (err) {
    console.error('Failed to load game categories:', err)
  }
}

const runs = ref<BrowseRun[]>([])
const loading = ref(true)
const selectedGameId = ref<number | null>(null)
const selectedCategoryId = ref<number | null>(null)

const filteredRuns = computed(() => {
  return runs.value.filter((run) => {
    if (selectedGameId.value !== null && run.gameId !== selectedGameId.value) {
      return false
    }

    if (selectedCategoryId.value !== null) {
      const gameCategories = gameCategoryMap.value.get(run.gameId)
      if (!gameCategories?.has(selectedCategoryId.value)) {
        return false
      }
    }

    return true
  })
})

onMounted(async () => {
  loadAuth()

  await Promise.all([
    fetchGames(),
    fetchCategories(),
    loadGameCategories(),
    loadRuns(),
  ])
})

const loadRuns = async () => {
  loading.value = true
  try {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
    }

    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }

    const response = await $fetch<BrowseRunsResponse>(
      `${config.public.apiBase}/playthrough/browse`,
      { headers }
    )
    runs.value = response.data.playthroughs
  } catch (err) {
    console.error('Failed to load runs:', err)
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  selectedGameId.value = null
  selectedCategoryId.value = null
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
</script>

<template>
  <div class="runs-page">
    <div class="runs-page__header">
      <h1 class="runs-page__title">
        Browse Challenge Runs
      </h1>
      <p class="runs-page__description">Explore the latest 50 completed challenge runs, with or without videos.</p>
      <p class="runs-page__legend">
        <span class="runs-page__legend-item">
          <span class="runs-page__legend-dot runs-page__legend-dot--yellow"/>
          Yellow border = Your run
        </span>
        <span class="runs-page__legend-item">
          <span class="runs-page__legend-dot runs-page__legend-dot--cyan"/>
          Cyan border = Games you've played
        </span>
      </p>
    </div>

    <div class="runs-page__filters">
      <div class="runs-page__filters-header">
        <Icon name="heroicons:funnel" class="runs-page__filters-icon" />
        <h2 class="runs-page__filters-title">Filters</h2>
      </div>

      <div class="runs-page__filters-grid">
        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">Game</label>
          <select
            v-model="selectedGameId"
            class="runs-page__filter-select"
          >
            <option :value="null">All Games</option>
            <option v-for="game in games" :key="game.id" :value="game.id">
              {{ game.name }}
            </option>
          </select>
        </div>

        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">Category</label>
          <select
            v-model="selectedCategoryId"
            class="runs-page__filter-select"
          >
            <option :value="null">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">&nbsp;</label>
          <button
            :disabled="!selectedGameId && !selectedCategoryId"
            class="runs-page__filter-clear"
            @click="clearFilters"
          >
            <Icon name="heroicons:x-mark" class="runs-page__filter-clear-icon" />
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="runs-page__loading">
      <div class="runs-page__loading-spinner"/>
      <p class="runs-page__loading-text">Loading runs...</p>
    </div>

    <div v-else-if="filteredRuns.length === 0" class="runs-page__empty">
      <Icon name="heroicons:film" class="runs-page__empty-icon" />
      <p class="runs-page__empty-message">No runs found</p>
      <p class="runs-page__hint">Try adjusting your filters or check back later</p>
    </div>

    <div v-else class="runs-page__list">
      <div
        v-for="run in filteredRuns"
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
                  'runs-page__run-icon--warning': run.isOwnRun,
                  'runs-page__run-icon--cyan': run.hasPlayedGame && !run.isOwnRun,
                  'runs-page__run-icon--muted': !run.isOwnRun && !run.hasPlayedGame
                }"
              />
              <h3 class="runs-page__run-game">{{ run.gameName }}</h3>

              <span
                v-if="run.isOwnRun"
                class="admin-badge admin-badge--warning"
              >
                YOUR RUN
              </span>

              <span
                v-else-if="run.hasPlayedGame"
                class="runs-page__played-badge"
              >
                <Icon name="heroicons:check-circle" class="runs-page__played-badge-icon" />
                You've played this
              </span>
            </div>

            <p class="runs-page__run-ruleset">{{ run.rulesetName }}</p>

            <div class="runs-page__run-meta">
              <span class="runs-page__run-meta-item">
                <Icon name="heroicons:user" class="runs-page__run-meta-icon" />
                {{ run.username }}
              </span>
              <span class="runs-page__run-meta-item">
                <Icon name="heroicons:calendar" class="runs-page__run-meta-icon" />
                {{ formatDate(run.endedAt!) }}
              </span>
              <span class="runs-page__run-meta-item">
                <Icon name="heroicons:clock" class="runs-page__run-meta-icon" />
                {{ formatDuration(run.totalDuration) }}
              </span>
            </div>

            <a
              v-if="run.videoUrl"
              :href="run.videoUrl!"
              target="_blank"
              rel="noopener noreferrer"
              class="runs-page__video-link-inline"
            >
              <Icon
                :name="extractVideoId(run.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:video-camera'"
                class="runs-page__video-link-icon"
                :class="extractVideoId(run.videoUrl).platform === 'youtube' ? 'runs-page__video-icon--youtube' : 'runs-page__video-icon--twitch'"
              />
              <span class="underline">Watch on {{ extractVideoId(run.videoUrl).platform === 'youtube' ? 'YouTube' : 'Twitch' }}</span>
              <Icon name="heroicons:arrow-top-right-on-square" class="runs-page__video-link-external-icon" />
            </a>
            <div v-else class="runs-page__video-link-empty">
              <Icon name="heroicons:film" class="runs-page__video-link-icon" />
              <span>No video attached yet</span>
            </div>
          </div>

          <div class="runs-page__run-actions">
            <NuxtLink
              :to="`/runs/${run.uuid}`"
              :class="[
                'runs-page__run-button',
                run.isOwnRun
                  ? 'runs-page__run-button--warning'
                  : 'runs-page__run-button--share'
              ]"
            >
              <Icon name="heroicons:share" class="runs-page__run-button-icon" />
              View Run
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
