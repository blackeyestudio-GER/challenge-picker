import type { Ref } from 'vue'
import type {
  ChallengeItem,
  ChallengeListResponse,
  RespondToChallengeResponse,
  SendChallengeResponse,
  SentChallengesResponse
} from '~/generated/api-contracts'
import { extractErrorMessage } from '~/utils/errorHandler'

export type Challenge = ChallengeItem
export type ChallengesData = ChallengeListResponse['data']

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
      const response = await $fetch<ChallengeListResponse>(`${config.public.apiBase}/challenges/mine`, {
        headers: getAuthHeader(),
      })

      if (response.success) {
        challenges.value = response.data.challenges
        return response.data
      }

      throw new Error('Failed to fetch challenges')
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

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to send challenge')
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

      if (response.success) {
        // Refresh challenges list
        await fetchMyChallenges()
        return response.data
      }

      throw new Error('Failed to respond to challenge')
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

      if (response.success) {
        return response.data
      }

      throw new Error('Failed to fetch sent challenges')
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
