import { ref } from 'vue'
import { useAuth } from './useAuth'

export interface RuleIcon {
  id: number
  identifier: string
  category: string
  displayName: string
  svgContent: string
  tags: string[] | null
  color: string | null
  license: string | null
  source: string | null
  createdAt: string
  updatedAt: string
}

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
      const response = await $fetch<{ success: boolean; data: { icons: RuleIcon[] } }>(
        '/api/admin/rule-icons',
        { headers: getAuthHeader() }
      )
      return response.data.icons
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch icons'
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

