import { ref } from 'vue'
import { useAuth } from './useAuth'
import type { UserStatsResponse } from '~/generated/api-contracts'
import type { ApiError } from '~/utils/errorHandler'
import { extractErrorMessage } from '~/utils/errorHandler'

export type UserStats = UserStatsResponse['data']

export const useUserStats = () => {
  const config = useRuntimeConfig()
  const { token, getAuthHeader, isAuthenticated, loadAuth, logout } = useAuth()
  const stats = ref<UserStats>({
    totalVotes: 0,
    completedPlaythroughs: 0,
    rulesPlayed: 0,
    totalActiveRules: 0
  })
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchUserStats = async () => {
    // Ensure auth is loaded
    loadAuth()
    
    if (!isAuthenticated.value || !token.value) {
      stats.value = {
        totalVotes: 0,
        completedPlaythroughs: 0,
        rulesPlayed: 0,
        totalActiveRules: 0
      }
      return
    }

    loading.value = true
    error.value = null

    try {
      const response = await $fetch<UserStatsResponse>(`${config.public.apiBase}/users/me/stats`, {
        headers: getAuthHeader()
      })

      if (response.success) {
        stats.value = response.data
      }
    } catch (err: unknown) {
      const apiError = err as ApiError & { response?: { status?: number } }
      const status = apiError.statusCode || apiError.response?.status
      
      // If token is invalid/expired, clear auth state
      if (status === 401) {
        logout()
        stats.value = {
          totalVotes: 0,
          completedPlaythroughs: 0,
          rulesPlayed: 0,
          totalActiveRules: 0
        }
        return
      }
      
      error.value = extractErrorMessage(err, 'Failed to fetch user stats')
      stats.value = {
        totalVotes: 0,
        completedPlaythroughs: 0,
        rulesPlayed: 0,
        totalActiveRules: 0
      }
    } finally {
      loading.value = false
    }
  }

  return {
    stats,
    fetchUserStats,
    loading,
    error
  }
}
