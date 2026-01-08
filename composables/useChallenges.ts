import type { Ref } from 'vue'

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
    difficulty: string
    game: {
      id: number
      name: string
      imageBase64: string | null
    }
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
      const response = await $fetch<{
        success: boolean
        data: ChallengesData
        error?: { code: string; message: string }
      }>(`${config.public.apiBase}/challenges/mine`, {
        headers: getAuthHeader(),
      })

      if (response.success && response.data) {
        challenges.value = response.data.challenges
        return response.data
      } else {
        error.value = response.error?.message || 'Failed to fetch challenges'
        throw new Error(error.value)
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch challenges'
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
      const response = await $fetch<{
        success: boolean
        data?: { challengeUuid: string; message: string }
        error?: { code: string; message: string }
      }>(`${config.public.apiBase}/challenges/send`, {
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
    } catch (err: any) {
      error.value = err.data?.error?.message || err.message || 'Failed to send challenge'
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
      const response = await $fetch<{
        success: boolean
        data?: { message: string; playthroughUuid?: string }
        error?: { code: string; message: string }
      }>(`${config.public.apiBase}/challenges/${challengeUuid}/respond`, {
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
    } catch (err: any) {
      error.value = err.data?.error?.message || err.message || 'Failed to respond to challenge'
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
      const response = await $fetch<{
        success: boolean
        data: {
          challenges: Array<{
            playthroughUuid: string
            game: {
              id: number
              name: string
              imageBase64: string | null
            }
            ruleset: {
              id: number
              name: string
            }
            createdAt: string
            challenges: Array<{
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
            }>
          }>
          totalCount: number
          playthroughCount: number
        }
        error?: { code: string; message: string }
      }>(`${config.public.apiBase}/challenges/sent`, {
        headers: getAuthHeader(),
      })

      if (response.success && response.data) {
        return response.data
      } else {
        error.value = response.error?.message || 'Failed to fetch sent challenges'
        throw new Error(error.value)
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || err.message || 'Failed to fetch sent challenges'
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

