import type { Ref } from 'vue'
import { extractErrorMessage } from '~/utils/errorHandler'

export interface ChallengeUser {
  uuid: string
  username: string
  displayName: string
}

export interface ChallengePlaythrough {
  uuid: string
  ruleset: {
    id: number
    name: string
    difficulty?: string
    game: {
      id: number | null
      name: string | null
      imageBase64: string | null
    } | null
  }
  maxConcurrentRules: number
}

export interface Challenge {
  uuid: string
  challenger: ChallengeUser
  playthrough: ChallengePlaythrough
  createdAt: string
  expiresAt: string
}

export interface ChallengesData {
  challenges: Challenge[]
  count: number
}

export interface FetchMyChallengesResponse {
  success: boolean
  data: ChallengesData
  error?: { code: string; message: string }
}

export interface SendChallengeResponseData {
  challengeUuid: string
  message: string
}

export interface SendChallengeResponse {
  success: boolean
  data?: SendChallengeResponseData
  error?: { code: string; message: string }
}

export interface RespondToChallengeResponseData {
  message: string
  playthroughUuid?: string | null
}

export interface RespondToChallengeResponse {
  success: boolean
  data?: RespondToChallengeResponseData
  error?: { code: string; message: string }
}

export interface SentChallengeItem {
  uuid: string
  challengedUser: {
    uuid: string
    username: string
  }
  status: string
  createdAt: string
  respondedAt: string | null
  expiresAt: string
  resultingPlaythroughUuid: string | null
}

export interface SentChallengeGroup {
  playthroughUuid: string
  game: {
    id: number | null
    name: string
    imageBase64: string | null
  }
  ruleset: {
    id: number | null
    name: string
  }
  createdAt: string
  challenges: SentChallengeItem[]
}

export interface SentChallengesResponse {
  success: boolean
  data: SentChallengeGroup[]
  error?: { code: string; message: string }
}

export interface ChallengeDetails {
  playthroughUuid: string
  hostUsername: string
  game: {
    id: number
    name: string
    imageBase64: string | null
  }
  ruleset: {
    id: number
    name: string
    description: string | null
    difficulty: string | null
  }
  maxConcurrentRules: number
  requireAuth: boolean
  allowViewerPicks: boolean
}

export interface ChallengeDetailsResponse {
  success: boolean
  data?: ChallengeDetails
  error?: { code: string; message: string }
}

export interface ChallengeComparisonRule {
  ruleId: number | null
  ruleName: string | null
  ruleType: string | null
  difficultyLevel?: number | null
  isActive: boolean | null
  completed: boolean
  currentAmount: number | null
  startedAt: string | null
  completedAt: string | null
}

export interface ChallengeComparisonParticipant {
  username: string
  playthroughUuid: string
  duration: number | null
  activeRules: ChallengeComparisonRule[]
  status: string
}

export interface ChallengeComparisonData {
  sourcePlaythroughUuid: string
  sourceUsername: string
  gameName: string
  rulesetName: string
  sourceDuration: number | null
  sourceActiveRules: ChallengeComparisonRule[]
  participants: ChallengeComparisonParticipant[]
}

export interface ChallengeComparisonResponse {
  success: boolean
  data?: ChallengeComparisonData
  error?: { code: string; message: string }
}

export function useChallenges() {
  const config = useRuntimeConfig()
  const { getAuthHeader } = useAuth()

  const challenges: Ref<Challenge[]> = ref([])
  const loading = ref(false)
  const error: Ref<string | null> = ref(null)

  /**
   * Fetch pending challenges for the current user
   */
  const fetchMyChallenges = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<FetchMyChallengesResponse>(`${config.public.apiBase}/challenges/mine`, {
        headers: getAuthHeader(),
      })

      if (response.success && response.data) {
        challenges.value = response.data.challenges
        return response.data
      } else {
        error.value = response.error?.message || 'Failed to fetch challenges'
        throw new Error(error.value)
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch challenges')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Send a challenge to another user
   */
  const sendChallenge = async (
    playthroughUuid: string,
    challengedUserIdentifier: string
  ) => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<SendChallengeResponse>(`${config.public.apiBase}/challenges/send`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: {
          playthroughUuid,
          challengedUserIdentifier,
        },
      })

      if (response.success && response.data) {
        return response.data
      } else {
        error.value = response.error?.message || 'Failed to send challenge'
        throw new Error(error.value)
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to send challenge')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Accept or decline a challenge
   */
  const respondToChallenge = async (challengeUuid: string, action: 'accept' | 'decline') => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<RespondToChallengeResponse>(`${config.public.apiBase}/challenges/${challengeUuid}/respond`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: { action },
      })

      if (response.success && response.data) {
        // Refresh challenges list
        await fetchMyChallenges()
        return response.data
      } else {
        error.value = response.error?.message || 'Failed to respond to challenge'
        throw new Error(error.value)
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to respond to challenge')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch challenges sent by the current user
   */
  const fetchSentChallenges = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<SentChallengesResponse>(`${config.public.apiBase}/challenges/sent`, {
        headers: getAuthHeader(),
      })

      if (response.success && response.data) {
        return response.data
      } else {
        error.value = response.error?.message || 'Failed to fetch sent challenges'
        throw new Error(error.value)
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch sent challenges')
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    challenges,
    loading,
    error,
    fetchMyChallenges,
    fetchSentChallenges,
    sendChallenge,
    respondToChallenge,
  }
}
