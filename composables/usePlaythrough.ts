import type { Ref } from 'vue'

export interface Game {
  id: number
  name: string
  description: string | null
  image: string | null
  rulesetCount: number
  gameSpecificRulesetCount?: number
  categoryBasedRulesetCount?: number
  categoryId: number | null
  categoryName: string | null
  categorySlug: string | null
  isCategoryRepresentative: boolean
  isFavorited: boolean
  steamLink: string | null
  epicLink: string | null
  gogLink: string | null
  twitchCategory: string | null
}

export interface Ruleset {
  id: number
  name: string
  description: string | null
  gameId?: number
  gameName?: string
  ruleCount: number
  isFavorited?: boolean
  voteCount?: number
  userVoteType?: number | null
  isInherited?: boolean
  inheritedFromCategory?: string | null
  isGameSpecific?: boolean
  categoryName?: string | null
  categoryId?: number | null
}

export interface PlaythroughRule {
  id: number
  ruleId: number
  text: string
  durationMinutes: number
  isActive: boolean
  completed: boolean
}

export interface PlaythroughDetails {
  id: number
  uuid: string
  gameId: number
  gameName: string
  rulesetId: number
  rulesetName: string
  maxConcurrentRules: number
  status: 'setup' | 'active' | 'paused' | 'completed'
  rules: PlaythroughRule[]
}

export interface Playthrough {
  id: number
  uuid: string
  userId: number
  username: string
  gameId: number
  gameName: string
  rulesetId: number
  rulesetName: string
  maxConcurrentRules: number
  status: 'setup' | 'active' | 'paused' | 'completed'
  startedAt: string | null
  endedAt: string | null
  pausedAt: string | null
  totalPausedDuration: number | null // Total seconds spent paused (accumulated)
  totalDuration: number | null // Total active play time (excluding paused time)
  videoUrl: string | null
  finishedRun: boolean | null
  recommended: number | null // -1 = no, 0 = neutral, 1 = yes
  configuration: Record<string, any> // JSON configuration snapshot (revision-safe, always present)
  createdAt: string
}

export interface PlayScreenData {
  id: number
  uuid: string
  userUuid: string
  gameName: string
  gameImage: string | null
  rulesetName: string
  gamehostUsername: string
  status: 'setup' | 'active' | 'paused' | 'completed'
  maxConcurrentRules: number
  requireAuth: boolean
  allowViewerPicks: boolean
  startedAt: string | null
  totalDuration: number | null
  activeRules: ActiveRuleData[]
  totalRulesCount: number
  activeRulesCount: number
  completedRulesCount: number
  configuration: Record<string, any>
}

export interface ActiveRuleData {
  id: number
  text: string
  durationMinutes: number
  startedAt: string | null
}

export const usePlaythrough = () => {
  const config = useRuntimeConfig()
  const { getAuthHeader } = useAuth()

  const games: Ref<Game[]> = ref([])
  const rulesets: Ref<Ruleset[]> = ref([])
  const currentPlaythrough: Ref<PlaythroughDetails | null> = ref(null)
  const activePlaythrough: Ref<Playthrough | null> = ref(null)
  const playScreenData: Ref<PlayScreenData | null> = ref(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch all available games
   */
  const fetchGames = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: Game[] }>(
        `${config.public.apiBase}/games`,
        {
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        games.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to load games'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch rulesets for a specific game
   */
  const fetchRulesets = async (gameId: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: Ruleset[] }>(
        `${config.public.apiBase}/games/${gameId}/rulesets`,
        {
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        rulesets.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to load rulesets'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new playthrough session
   * @param configuration Optional JSON configuration snapshot (revision-safe)
   */
  const createPlaythrough = async (
    gameId: number,
    rulesetId: number,
    maxConcurrentRules: number = 3,
    requireAuth: boolean = false,
    allowViewerPicks: boolean = false,
    configuration?: Record<string, any> | null
  ): Promise<Playthrough> => {
    loading.value = true
    error.value = null

    try {
      const body: {
        gameId: number
        rulesetId: number
        maxConcurrentRules: number
        requireAuth: boolean
        allowViewerPicks: boolean
        configuration?: Record<string, any>
      } = {
        gameId,
        rulesetId,
        maxConcurrentRules,
        requireAuth,
        allowViewerPicks
      }
      
      if (configuration) {
        body.configuration = configuration
      }
      
      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs`,
        {
          method: 'POST',
          headers: getAuthHeader(),
          body
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to create playthrough')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create playthrough'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch playthrough details
   */
  const fetchPlaythrough = async (uuid: string) => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: PlaythroughDetails }>(
        `${config.public.apiBase}/playthroughs/${uuid}`,
        {
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        currentPlaythrough.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to load playthrough'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Toggle a rule's active status
   */
  const toggleRule = async (playthroughUuid: string, ruleId: number) => {
    try {
      const response = await $fetch<{ success: boolean; data: { id: number; ruleId: number; isActive: boolean } }>(
        `${config.public.apiBase}/playthroughs/${playthroughUuid}/rules/${ruleId}/toggle`,
        {
          method: 'PUT',
          headers: getAuthHeader()
        }
      )

      if (response.success && currentPlaythrough.value) {
        // Update local state
        const rule = currentPlaythrough.value.rules.find(r => r.ruleId === ruleId)
        if (rule) {
          rule.isActive = response.data.isActive
        }
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to toggle rule'
      throw err
    }
  }

  /**
   * Update max concurrent rules
   */
  const updateMaxConcurrent = async (playthroughUuid: string, maxConcurrentRules: number) => {
    try {
      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs/${playthroughUuid}/concurrent`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: {
            maxConcurrentRules
          }
        }
      )

      if (response.success && currentPlaythrough.value) {
        currentPlaythrough.value.maxConcurrentRules = response.data.maxConcurrentRules
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update max concurrent rules'
      throw err
    }
  }

  /**
   * Check if user has an active playthrough
   */
  const fetchActivePlaythrough = async () => {
    try {
      const authHeader = getAuthHeader()
      
      // Only proceed if we have an auth token
      if (!authHeader || !authHeader.Authorization) {
        console.warn('No auth token available for fetching active playthrough')
        activePlaythrough.value = null
        return
      }

      const response = await $fetch<{ success: boolean; data: Playthrough | null }>(
        `${config.public.apiBase}/users/me/playthrough/active`,
        {
          headers: authHeader
        }
      )

      if (response.success) {
        activePlaythrough.value = response.data
      }
    } catch (err: any) {
      // Handle 401 gracefully (no active session or expired token)
      if (err.status === 401 || err.statusCode === 401) {
        console.warn('Authentication failed for active playthrough check')
        activePlaythrough.value = null
        return
      }
      
      error.value = err.data?.error?.message || 'Failed to check active playthrough'
      console.error('Failed to fetch active playthrough:', err)
      activePlaythrough.value = null
    }
  }

  /**
   * Fetch public play screen data by UUID (no auth required)
   */
  const fetchPlayScreen = async (uuid: string, silent: boolean = false) => {
    if (!silent) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: PlayScreenData }>(
        `${config.public.apiBase}/play/${uuid}`
      )

      if (response.success) {
        playScreenData.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to load play screen'
      throw err
    } finally {
      if (!silent) {
        loading.value = false
      }
    }
  }

  /**
   * Fetch play screen data by user UUID (finds their active playthrough)
   */
  const fetchPlayScreenByUserUuid = async (userUuid: string, silent: boolean = false) => {
    if (!silent) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: PlayScreenData }>(
        `${config.public.apiBase}/user/${userUuid}/play-screen`
      )

      if (response.success) {
        playScreenData.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'No active game session'
      playScreenData.value = null
      throw err
    } finally {
      if (!silent) {
        loading.value = false
      }
    }
  }

  /**
   * Fetch play screen data for authenticated user (finds their active playthrough)
   */
  const fetchMyPlayScreen = async (silent: boolean = false) => {
    if (!silent) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: PlayScreenData }>(
        `${config.public.apiBase}/users/me/play-screen`,
        {
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        playScreenData.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'No active game session'
      playScreenData.value = null
      throw err
    } finally {
      if (!silent) {
        loading.value = false
      }
    }
  }

  /**
   * Start polling for play screen updates
   * Returns a cleanup function to stop polling
   */
  const startPlayScreenPolling = (uuid: string, intervalMs: number = 2000): (() => void) => {
    const pollInterval = setInterval(async () => {
      try {
        // Silent fetch (don't show loading state)
        await fetchPlayScreen(uuid, true)
      } catch (err) {
        // Silently fail on polling errors (user already sees the screen)
        console.error('Polling error:', err)
      }
    }, intervalMs)

    // Return cleanup function
    return () => {
      clearInterval(pollInterval)
    }
  }

  /**
   * Start polling for play screen updates by user UUID
   */
  const startPlayScreenPollingByUserUuid = (userUuid: string, intervalMs: number = 2000): (() => void) => {
    const pollInterval = setInterval(async () => {
      try {
        // Silent fetch (don't show loading state)
        await fetchPlayScreenByUserUuid(userUuid, true)
      } catch (err) {
        // Silently fail on polling errors (user already sees the screen)
        console.error('Polling error:', err)
      }
    }, intervalMs)

    // Return cleanup function
    return () => {
      clearInterval(pollInterval)
    }
  }

  /**
   * Start a playthrough session
   */
  const startPlaythrough = async (uuid: string): Promise<Playthrough> => {
    try {
      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs/${uuid}/start`,
        {
          method: 'PUT',
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to start playthrough')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to start playthrough'
      throw err
    }
  }

  /**
   * Pause a playthrough session
   */
  const pausePlaythrough = async (uuid: string): Promise<Playthrough> => {
    try {
      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs/${uuid}/pause`,
        {
          method: 'PUT',
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to pause playthrough')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to pause playthrough'
      throw err
    }
  }

  /**
   * Resume a playthrough session
   */
  const resumePlaythrough = async (uuid: string): Promise<Playthrough> => {
    try {
      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs/${uuid}/resume`,
        {
          method: 'PUT',
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to resume playthrough')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to resume playthrough'
      throw err
    }
  }

  /**
   * End a playthrough session
   */
  const endPlaythrough = async (uuid: string): Promise<Playthrough> => {
    try {
      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs/${uuid}/end`,
        {
          method: 'PUT',
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to end playthrough')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to end playthrough'
      throw err
    }
  }

  const addVideoUrl = async (uuid: string, videoUrl: string): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`${config.public.apiBaseUrl}/api/playthrough/${uuid}/video-url`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify({ videoUrl })
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to add video URL'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update playthrough feedback (finishedRun and recommended)
   */
  const updatePlaythroughFeedback = async (
    uuid: string,
    finishedRun: boolean | null = null,
    recommended: number | null = null
  ): Promise<Playthrough> => {
    loading.value = true
    error.value = null
    try {
      const body: { finishedRun?: boolean | null; recommended?: number | null } = {}
      if (finishedRun !== null) {
        body.finishedRun = finishedRun
      }
      if (recommended !== null) {
        body.recommended = recommended
      }

      const response = await $fetch<{ success: boolean; data: Playthrough }>(
        `${config.public.apiBase}/playthroughs/${uuid}/feedback`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to update feedback')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update feedback'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    games,
    rulesets,
    currentPlaythrough,
    activePlaythrough,
    playScreenData,
    loading,
    error,
    fetchGames,
    fetchRulesets,
    createPlaythrough,
    fetchPlaythrough,
    toggleRule,
    updateMaxConcurrent,
    fetchActivePlaythrough,
    fetchPlayScreen,
    fetchPlayScreenByUserUuid,
    fetchMyPlayScreen,
    startPlayScreenPolling,
    startPlayScreenPollingByUserUuid,
    startPlaythrough,
    pausePlaythrough,
    resumePlaythrough,
    endPlaythrough,
    addVideoUrl,
    updatePlaythroughFeedback
  }
}

