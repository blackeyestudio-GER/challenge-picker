import { ref } from 'vue'
import { useAuth } from './useAuth'
import type { RuleIconItem, RuleIconsResponse } from '~/generated/api-contracts'
import { extractErrorMessage } from '~/utils/errorHandler'

export type RuleIcon = RuleIconItem

export const useIcons = () => {
  const { token } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const getAuthHeader = () => ({
    'Authorization': `Bearer ${token.value}`,
    'Content-Type': 'application/json'
  })

  const fetchIcons = async (): Promise<RuleIcon[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<RuleIconsResponse>(
        '/api/admin/rule-icons',
        { headers: getAuthHeader() }
      )
      return response.data.icons
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch icons')
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchRuleIcons = async (): Promise<RuleIcon[]> => {
    // Alias for fetchIcons for compatibility
    return fetchIcons()
  }

  return {
    loading,
    error,
    fetchIcons,
    fetchRuleIcons
  }
}
