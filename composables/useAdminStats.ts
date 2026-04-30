import { ref } from 'vue'
import { useAuth } from './useAuth'
import { extractErrorMessage } from '~/utils/errorHandler'

export interface AdminStats {
  categories: number
  games: number
  rulesets: number
  rules: number
}

export const useAdminStats = () => {
  const { token } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const getAuthHeader = () => ({
    'Authorization': `Bearer ${token.value}`,
    'Content-Type': 'application/json'
  })

  const fetchAdminStats = async (): Promise<AdminStats> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: AdminStats }>(
        '/api/admin/stats',
        { headers: getAuthHeader() }
      )
      return response.data
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch admin statistics')
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    fetchAdminStats
  }
}
