import type { Ref } from 'vue'
import type {
  ActiveRuleData as GeneratedActiveRuleData,
  ActiveRulesResponse as GeneratedActiveRulesResponse,
  ActiveRulesResponseData as GeneratedActiveRulesResponseData,
  ActivePlaythroughResponse as GeneratedActivePlaythroughResponse,
  AddVideoUrlResponse as GeneratedAddVideoUrlResponse,
  AddVideoUrlResponseData as GeneratedAddVideoUrlResponseData,
  BrowseAvailabilityResponse as GeneratedBrowseAvailabilityResponse,
  BrowseRun as GeneratedBrowseRun,
  BrowseRunsResponse as GeneratedBrowseRunsResponse,
  CompletedRunHistoryEntry as GeneratedCompletedRunHistoryEntry,
  CompletedRunRule as GeneratedCompletedRunRule,
  CompletedPlaythroughsResponse as GeneratedCompletedPlaythroughsResponse,
  CounterMutationResponse as GeneratedCounterMutationResponse,
  CounterMutationResponseData as GeneratedCounterMutationResponseData,
  CreatePlaythroughResponse as GeneratedCreatePlaythroughResponse,
  DashboardActiveRule as GeneratedDashboardActiveRule,
  DashboardPickStatus as GeneratedDashboardPickStatus,
  DashboardQueuePendingRule as GeneratedDashboardQueuePendingRule,
  DashboardQueueStatus as GeneratedDashboardQueueStatus,
  DashboardResponse as GeneratedDashboardResponse,
  DashboardResponseData as GeneratedDashboardResponseData,
  DeletePlaythroughResponse,
  DeletePlaythroughResponseData,
  EndPlaythroughResponse as GeneratedEndPlaythroughResponse,
  PlayScreenResponse as GeneratedPlayScreenResponse,
  Playthrough as GeneratedPlaythrough,
  PlaythroughDetails as GeneratedPlaythroughDetails,
  PickRuleResponse as GeneratedPickRuleResponse,
  PickRuleResponseData as GeneratedPickRuleResponseData,
  PlayScreenData as GeneratedPlayScreenData,
  PlaythroughGame as GeneratedGame,
  PlaythroughDetailsResponse as GeneratedPlaythroughDetailsResponse,
  PlaythroughMutationResponse as GeneratedPlaythroughMutationResponse,
  PlaythroughRule as GeneratedPlaythroughRule,
  PlaythroughRuleset as GeneratedRuleset,
  PlaythroughGamesResponse as GeneratedGamesResponse,
  PlaythroughRulesetsResponse as GeneratedRulesetsResponse,
  PublicRunHistoryEntry as GeneratedPublicRunHistoryEntry,
  PublicRunPlaythrough as GeneratedPublicRunPlaythrough,
  PublicRunResponse as GeneratedPublicRunResponse,
  PublicRunRule as GeneratedPublicRunRule,
  ToggleRuleResponse as GeneratedToggleRuleResponse,
  ToggleRuleResponseData as GeneratedToggleRuleResponseData
} from '~/generated/api-contracts'
import type { ApiError } from '~/utils/errorHandler'
import { extractErrorMessage } from '~/utils/errorHandler'

export type Game = GeneratedGame

export type Ruleset = GeneratedRuleset

export type PlaythroughRule = GeneratedPlaythroughRule

export type PlaythroughDetails = GeneratedPlaythroughDetails

export type Playthrough = GeneratedPlaythrough

export type CompletedRunRule = GeneratedCompletedRunRule

export type CompletedRunHistoryEntry = GeneratedCompletedRunHistoryEntry

export type AddVideoUrlResponseData = GeneratedAddVideoUrlResponseData
export type AddVideoUrlResponse = GeneratedAddVideoUrlResponse
export type CreatePlaythroughResponse = GeneratedCreatePlaythroughResponse
export type PickRuleResponseData = GeneratedPickRuleResponseData
export type PickRuleResponse = GeneratedPickRuleResponse
export type DashboardActiveRule = GeneratedDashboardActiveRule
export type DashboardPickStatus = GeneratedDashboardPickStatus
export type DashboardQueuePendingRule = GeneratedDashboardQueuePendingRule
export type DashboardQueueStatus = GeneratedDashboardQueueStatus
export type DashboardResponseData = GeneratedDashboardResponseData
export type DashboardResponse = GeneratedDashboardResponse

export type PublicRunRule = GeneratedPublicRunRule
export type PublicRunHistoryEntry = GeneratedPublicRunHistoryEntry
export type PublicRunPlaythrough = GeneratedPublicRunPlaythrough

export type PublicRunResponse = GeneratedPublicRunResponse

export type BrowseRun = GeneratedBrowseRun

export type BrowseRunsResponse = GeneratedBrowseRunsResponse
export type CompletedPlaythroughsResponse = GeneratedCompletedPlaythroughsResponse
export type BrowseAvailabilityResponse = GeneratedBrowseAvailabilityResponse
export type ActivePlaythroughResponse = GeneratedActivePlaythroughResponse
export type ActiveRulesResponseData = GeneratedActiveRulesResponseData
export type ActiveRulesResponse = GeneratedActiveRulesResponse
export type PlaythroughDetailsResponse = GeneratedPlaythroughDetailsResponse
export type ToggleRuleResponseData = GeneratedToggleRuleResponseData
export type ToggleRuleResponse = GeneratedToggleRuleResponse
export type CounterMutationResponseData = GeneratedCounterMutationResponseData
export type CounterMutationResponse = GeneratedCounterMutationResponse
export type EndPlaythroughResponse = GeneratedEndPlaythroughResponse
export type GamesResponse = GeneratedGamesResponse
export type RulesetsResponse = GeneratedRulesetsResponse
export type PlaythroughMutationResponse = GeneratedPlaythroughMutationResponse
export type PlayScreenResponse = GeneratedPlayScreenResponse

export interface EndPlaythroughResult {
  playthrough: Playthrough | null
  deleted: boolean
  message: string | null
  uuid: string | null
}

export type PlayScreenData = GeneratedPlayScreenData
export type ActiveRuleData = GeneratedActiveRuleData

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
      const response = await $fetch<GamesResponse>(
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
      const response = await $fetch<RulesetsResponse>(
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
      const response = await $fetch<PlaythroughMutationResponse>(
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
        activePlaythrough.value = null
        return
      }
      
      error.value = extractErrorMessage(err, 'Failed to check active playthrough')
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
      const response = await $fetch<PlayScreenResponse>(
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
      const response = await $fetch<PlayScreenResponse>(
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
      const response = await $fetch<PlayScreenResponse>(
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
      } catch {
        // Silently fail on polling errors (user already sees the screen)
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
      } catch {
        // Silently fail on polling errors (user already sees the screen)
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
      const response = await $fetch<PlaythroughMutationResponse>(
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
      const response = await $fetch<PlaythroughMutationResponse>(
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
      const response = await $fetch<PlaythroughMutationResponse>(
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
  const endPlaythrough = async (uuid: string): Promise<EndPlaythroughResult> => {
    try {
      const response = await $fetch<EndPlaythroughResponse>(
        `${config.public.apiBase}/playthroughs/${uuid}/end`,
        {
          method: 'PUT',
          headers: getAuthHeader()
        }
      )

      if (response.success) {
        return {
          playthrough: response.data,
          deleted: response.deleted === true,
          message: response.message ?? null,
          uuid: response.uuid ?? null
        }
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

      const response = await $fetch<PlaythroughMutationResponse>(
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
