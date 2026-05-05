import { computed, ref } from 'vue'
import type { BrowseRun, BrowseRunsResponse, Game } from '~/composables/usePlaythrough'
import type { Category } from '~/composables/useCategories'
import { useCategories } from '~/composables/useCategories'
import { useGameCategories } from '~/composables/useGameCategories'
import { usePlaythrough } from '~/composables/usePlaythrough'
import { extractErrorMessage } from '~/utils/errorHandler'

export const useRunsPage = () => {
  const { token, loadAuth } = useAuth()
  const config = useRuntimeConfig()
  const { getAllGamesCategories } = useGameCategories()
  const { games, fetchGames } = usePlaythrough()
  const { categories, fetchCategories } = useCategories()
  const { notifyApiError } = useNotify()

  const gameCategoryMap = ref<Map<number, Set<number>>>(new Map())
  const errorMessage = ref('')
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

  const loadGameCategories = async () => {
    try {
      const allGamesCategories = await getAllGamesCategories()
      const map = new Map<number, Set<number>>()

      Object.entries(allGamesCategories).forEach(([gameId, gameCategories]) => {
        map.set(Number(gameId), new Set(gameCategories.map((category) => category.id)))
      })

      gameCategoryMap.value = map
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to load category mappings')
    }
  }

  const loadRuns = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
      }

      if (token.value) {
        headers.Authorization = `Bearer ${token.value}`
      }

      const response = await $fetch<BrowseRunsResponse>(
        `${config.public.apiBase}/playthrough/browse`,
        { headers }
      )
      runs.value = response.data.playthroughs
    } catch (err: unknown) {
      errorMessage.value = extractErrorMessage(err, 'Failed to load runs')
      notifyApiError(err, 'Failed to load runs')
    } finally {
      loading.value = false
    }
  }

  const bootstrap = async () => {
    loadAuth()
    await Promise.all([
      fetchGames(),
      fetchCategories(),
      loadGameCategories(),
      loadRuns(),
    ])
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

  const extractVideoId = (url: string | null): { platform: 'youtube' | 'external' | null; id: string | null } => {
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

    return { platform: 'external', id: null }
  }

  return {
    games: games as unknown as { value: Game[] },
    categories: categories as unknown as { value: Category[] },
    errorMessage,
    loading,
    selectedGameId,
    selectedCategoryId,
    filteredRuns,
    bootstrap,
    clearFilters,
    formatDuration,
    formatDate,
    extractVideoId
  }
}
