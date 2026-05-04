import type { Ref } from 'vue'
import type { ApiError } from '~/utils/errorHandler'
import { extractErrorMessage } from '~/utils/errorHandler'

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
  configuration: Record<string, unknown> // JSON configuration snapshot (revision-safe, always present)
  usedRules: CompletedRunRule[]
  ruleHistory: CompletedRunHistoryEntry[]
  createdAt: string
}

export interface CompletedRunRule {
  id: number
  name: string
  description: string | null
  type: string | null
  isDefault: boolean
  isEnabled: boolean
}

export interface CompletedRunHistoryEntry {
  id: number | null
  ruleId: number
  name: string
  description: string | null
  type: string | null
  isActive: boolean
  completed: boolean
  currentAmount: number | null
  startedAt: string | null
  completedAt: string | null
  createdAt: string | null
}

export interface AddVideoUrlResponseData {
  message: string
  videoUrl: string | null
}

export interface AddVideoUrlResponse {
  success: boolean
  data: AddVideoUrlResponseData
}

export interface CreatePlaythroughResponse {
  success: boolean
  data: Playthrough
}

export interface PickRuleResponseData {
  ruleId: number
  ruleName: string
  activated: boolean
  position: number | null
  eta: number | null
  message: string
}

export interface PickRuleResponse {
  success: boolean
  data: PickRuleResponseData
}

export interface DashboardActiveRule {
  id: number
  ruleId: number | null
  ruleName: string | null
  ruleType: string | null
  type: 'permanent' | 'time' | 'counter' | 'hybrid'
  currentAmount: number | null
  initialAmount: number | null
  durationSeconds: number | null
  expiresAt: string | null
  timeRemaining: number | null
  startedAt: string | null
}

export interface DashboardPickStatus {
  canPick: boolean
  rateLimitSeconds: number | null
  cooldownRuleIds: number[]
  availableRulesCount: number
  message: string
}

export interface DashboardQueuePendingRule {
  ruleId: number
  ruleName: string
  ruleType: string | null
  position: number
  eta: number
}

export interface DashboardQueueStatus {
  queueLength: number
  pendingRules: DashboardQueuePendingRule[]
}

export interface DashboardResponseData {
  playthrough: PlayScreenData
  activeRules: DashboardActiveRule[]
  pickStatus: DashboardPickStatus | null
  queueStatus: DashboardQueueStatus
  isHost: boolean
}

export interface DashboardResponse {
  success: boolean
  data: DashboardResponseData
}

export interface PublicRunRule {
  id: number
  name: string
  description: string | null
  type: string | null
}

export interface PublicRunHistoryEntry {
  ruleId: number
  name: string
  description: string | null
  type: string | null
  isActive: boolean
  completed: boolean
  currentAmount: number | null
  startedAt: string | null
  completedAt: string | null
  createdAt: string | null
}

export interface PublicRunPlaythrough {
  uuid: string
  status: string
  startedAt: string | null
  endedAt: string | null
  totalDuration: number | null
  videoUrl: string | null
  finishedRun: boolean | null
  recommended: number | null
  game: {
    id: number | null
    name: string | null
    imageUrl: string | null
  }
  ruleset: {
    id: number | null
    name: string | null
    description: string | null
  }
  user: {
    username: string | null
    avatarUrl: string | null
  }
  usedRules: PublicRunRule[]
  ruleHistory: PublicRunHistoryEntry[]
}

export interface PublicRunResponse {
  success: boolean
  data: {
    playthrough: PublicRunPlaythrough
  }
}

export interface BrowseRun extends Playthrough {
  isOwnRun: boolean
  hasPlayedGame: boolean
}

export interface BrowseRunsResponse {
  success: boolean
  data: {
    playthroughs: BrowseRun[]
  }
}

export interface CompletedPlaythroughsResponse {
  success: boolean
  data: {
    playthroughs: Playthrough[]
  }
}

export interface BrowseAvailabilityResponse {
  success: boolean
  data: {
    available: boolean
    count: number
  }
}

export interface ActivePlaythroughResponse {
  success: boolean
  data: Playthrough | null
}

export interface ActiveRulesResponseData {
  playthroughId: number
  status: string
  activeRules: DashboardActiveRule[]
}

export interface ActiveRulesResponse {
  success: boolean
  data: ActiveRulesResponseData
}

export interface PlaythroughDetailsResponse {
  success: boolean
  data: PlaythroughDetails
}

export interface ToggleRuleResponseData {
  id: number | null
  ruleId: number | null
  isActive: boolean
}

export interface ToggleRuleResponse {
  success: boolean
  data: ToggleRuleResponseData
}

export interface CounterMutationResponseData {
  id: number | null
  currentAmount: number | null
  isActive: boolean
  message: string
}

export interface CounterMutationResponse {
  success: boolean
  data: CounterMutationResponseData
}

export interface DeletePlaythroughResponseData {
  uuid: string
  message: string
}

export interface DeletePlaythroughResponse {
  success: boolean
  data: DeletePlaythroughResponseData
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
  pausedAt: string | null
  totalPausedDuration: number | null
  totalDuration: number | null
  activeRules: ActiveRuleData[]
  totalRulesCount: number
  activeRulesCount: number
  completedRulesCount: number
  configuration: Record<string, unknown>
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to load games')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to load rulesets')
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
    configuration?: Record<string, unknown> | null
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
        configuration?: Record<string, unknown>
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
      
      const response = await $fetch<CreatePlaythroughResponse>(
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to create playthrough')
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
      const response = await $fetch<PlaythroughDetailsResponse>(
        `${config.public.apiBase}/playthroughs/${uuid}`,
        {
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        currentPlaythrough.value = response.data
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to load playthrough')
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
      const response = await $fetch<ToggleRuleResponse>(
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to toggle rule')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to update max concurrent rules')
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

      const response = await $fetch<ActivePlaythroughResponse>(
        `${config.public.apiBase}/users/me/playthrough/active`,
        {
          headers: authHeader
        }
      )

      if (response.success) {
        activePlaythrough.value = response.data
      }
    } catch (err: unknown) {
      const apiError = err as ApiError
      // Handle 401 gracefully (no active session or expired token)
      if (apiError.statusCode === 401) {
        console.warn('Authentication failed for active playthrough check')
        activePlaythrough.value = null
        return
      }
      
      error.value = extractErrorMessage(err, 'Failed to check active playthrough')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to load play screen')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'No active game session')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'No active game session')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to start playthrough')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to pause playthrough')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to resume playthrough')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to end playthrough')
      throw err
    }
  }

  const deletePlaythrough = async (uuid: string): Promise<DeletePlaythroughResponseData> => {
    try {
      const response = await $fetch<DeletePlaythroughResponse>(
        `${config.public.apiBase}/playthroughs/${uuid}`,
        {
          method: 'DELETE',
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to delete playthrough')
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to delete playthrough')
      throw err
    }
  }

  const pickRule = async (
    uuid: string,
    ruleId: number,
    difficultyLevel: number,
    includeAuthHeader: boolean = true
  ): Promise<PickRuleResponseData> => {
    error.value = null

    try {
      const response = await $fetch<PickRuleResponse>(`${config.public.apiBase}/playthroughs/${uuid}/pick-rule`, {
        method: 'POST',
        headers: includeAuthHeader ? getAuthHeader() : {},
        body: {
          ruleId,
          difficultyLevel
        }
      })

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to pick rule')
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to pick rule')
      throw err
    }
  }

  const addVideoUrl = async (uuid: string, videoUrl: string): Promise<AddVideoUrlResponseData> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<AddVideoUrlResponse>(`${config.public.apiBase}/playthrough/${uuid}/video-url`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: { videoUrl }
      })

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to add video URL')
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to add video URL')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to update feedback')
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
    deletePlaythrough,
    pickRule,
    addVideoUrl,
    updatePlaythroughFeedback
  }
}
