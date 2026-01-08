import { ref } from 'vue'
import { useAuth } from './useAuth'

export const useUserStats = () => {
  const config = useRuntimeConfig()
  const { token, getAuthHeader, isAuthenticated, loadAuth, logout } = useAuth()
  const totalVotes = ref(0)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchUserStats = async () => {
    // Ensure auth is loaded
    loadAuth()
    
    if (!isAuthenticated.value || !token.value) {
      totalVotes.value = 0
      return
    }

    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{
        success: boolean
        data: { totalVotes: number }
      }>(`${config.public.apiBase}/users/me/stats`, {
        headers: getAuthHeader()
      })

      if (response.success) {
        totalVotes.value = response.data.totalVotes
      }
    } catch (err: any) {
      const status = err.statusCode || err.response?.status
      
      // If token is invalid/expired, clear auth state
      if (status === 401) {
        console.warn('Token expired or invalid, clearing auth state')
        logout()
        totalVotes.value = 0
        return
      }
      
      console.error('Failed to fetch user stats:', err)
      error.value = err.data?.error?.message || 'Failed to fetch user stats'
      totalVotes.value = 0
    } finally {
      loading.value = false
    }
  }

  return {
    totalVotes,
    fetchUserStats,
    loading,
    error
  }
}

