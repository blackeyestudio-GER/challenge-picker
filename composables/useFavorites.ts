import { ref } from 'vue'
import { useAuth } from './useAuth'
import type { ToggleFavoriteGameResponse } from '~/generated/api-contracts'
import { extractErrorMessage } from '~/utils/errorHandler'

export const useFavorites = () => {
  const config = useRuntimeConfig()
  const { token } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const toggleFavorite = async (gameId: number): Promise<boolean> => {
    if (!token.value) {
      throw new Error('Must be authenticated to favorite games')
    }

    loading.value = true
    error.value = null

    try {
      const response = await $fetch<ToggleFavoriteGameResponse>(`${config.public.apiBase}/games/${gameId}/favorite`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
      })

      if (!response.success) {
        throw new Error('Failed to toggle favorite')
      }

      return response.data.isFavorited
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to toggle favorite')
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    toggleFavorite,
    loading,
    error
  }
}
