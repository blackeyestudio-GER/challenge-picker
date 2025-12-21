import type { Ref } from 'vue'

export interface Game {
  id: number
  name: string
  description?: string
  image?: string
  rulesetCount: number
  categoryId?: number | null
  categoryName?: string | null
  categorySlug?: string | null
  isCategoryRepresentative?: boolean
}

export const useGames = () => {
  const config = useRuntimeConfig()
  const { token } = useAuth()
  const games: Ref<Game[]> = useState('games', () => [])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchGames = async () => {
    loading.value = true
    error.value = null

    try {
      const headers: Record<string, string> = {}
      if (token.value) {
        headers['Authorization'] = `Bearer ${token.value}`
      }

      const response = await $fetch<{ success: boolean; data: { games: Game[] } }>(
        `${config.public.apiBase}/games`,
        {
          headers: Object.keys(headers).length > 0 ? headers : undefined
        }
      )

      if (response.success) {
        games.value = response.data.games
      }
    } catch (err: any) {
      console.error('Failed to fetch games:', err)
      error.value = err.data?.error?.message || 'Failed to load games'
    } finally {
      loading.value = false
    }
  }

  const createGame = async (gameData: { name: string; description?: string; image?: string }) => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: Game }>(
        `${config.public.apiBase}/games`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token.value}`
          },
          body: JSON.stringify(gameData)
        }
      )

      if (response.success) {
        await fetchGames() // Refresh the list
        return response.data
      }
    } catch (err: any) {
      console.error('Failed to create game:', err)
      error.value = err.data?.error?.message || 'Failed to create game'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateGame = async (id: number, gameData: { name?: string; description?: string; image?: string }) => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: Game }>(
        `${config.public.apiBase}/games/${id}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token.value}`
          },
          body: JSON.stringify(gameData)
        }
      )

      if (response.success) {
        await fetchGames() // Refresh the list
        return response.data
      }
    } catch (err: any) {
      console.error('Failed to update game:', err)
      error.value = err.data?.error?.message || 'Failed to update game'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    games,
    loading,
    error,
    fetchGames,
    createGame,
    updateGame
  }
}

