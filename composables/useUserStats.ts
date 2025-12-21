import { ref } from 'vue'
import { useAuth } from './useAuth'

export const useUserStats = () => {
  const config = useRuntimeConfig()
  const { token } = useAuth()
  const totalVotes = ref(0)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchUserStats = async () => {
    if (!token.value) {
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
        headers: {
          'Authorization': `Bearer ${token.value}`
        }
      })

      if (response.success) {
        totalVotes.value = response.data.totalVotes
      }
    } catch (err: any) {
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

