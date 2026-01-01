export interface GameCategory {
  id: number
  name: string
  slug: string
  voteCount: number
  userVoted: boolean
  userVoteType: number | null // 1 for upvote, -1 for downvote, null if not voted
}

export const useGameCategories = () => {
  const { token } = useAuth()
  const config = useRuntimeConfig()

  const voteForCategory = async (gameId: number, categoryId: number, voteType: 1 | -1 = 1): Promise<{ voteCount: number; userVoted: boolean; userVoteType: number | null }> => {
    try {
      const response = await $fetch<{
        success: boolean
        data: { voteCount: number; userVoted: boolean; userVoteType: number | null }
      }>(`${config.public.apiBase}/games/${gameId}/categories/${categoryId}/vote`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ voteType })
      })

      if (!response.success) {
        throw new Error('Failed to vote for category')
      }

      return response.data
    } catch (err: any) {
      console.error('Failed to vote for category:', err)
      throw err
    }
  }

  const unvoteCategory = async (gameId: number, categoryId: number): Promise<{ voteCount: number; userVoted: boolean; userVoteType: number | null }> => {
    try {
      const response = await $fetch<{
        success: boolean
        data: { voteCount: number; userVoted: boolean; userVoteType: number | null }
      }>(`${config.public.apiBase}/games/${gameId}/categories/${categoryId}/vote`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
      })

      if (!response.success) {
        throw new Error('Failed to remove vote')
      }

      return response.data
    } catch (err: any) {
      console.error('Failed to remove vote:', err)
      throw err
    }
  }

  const getGameCategories = async (gameId: number): Promise<GameCategory[]> => {
    try {
      const headers: Record<string, string> = {}
      
      // Only include auth header if we have a valid token
      if (token.value) {
        headers['Authorization'] = `Bearer ${token.value}`
      }
      
      const response = await $fetch<{
        success: boolean
        data: GameCategory[]
      }>(`${config.public.apiBase}/games/${gameId}/categories`, {
        headers: Object.keys(headers).length > 0 ? headers : undefined
      })

      if (!response.success) {
        throw new Error('Failed to fetch game categories')
      }

      return response.data
    } catch (err: any) {
      // Silently handle 401 errors for public endpoints
      if (err.status === 401 || err.statusCode === 401) {
        console.warn(`Auth failed for game ${gameId} categories, but this is a public endpoint`)
        // Try without auth
        try {
          const response = await $fetch<{
            success: boolean
            data: GameCategory[]
          }>(`${config.public.apiBase}/games/${gameId}/categories`)
          return response.success ? response.data : []
        } catch {
          return []
        }
      }
      console.error('Failed to fetch game categories:', err)
      return []
    }
  }

  const toggleVote = async (gameId: number, categoryId: number, voteType: 1 | -1, currentVoteType: number | null): Promise<{ voteCount: number; userVoted: boolean; userVoteType: number | null }> => {
    // If the user is clicking the same vote type they already have, it will be removed by the backend
    // Otherwise, it will be added/updated
    return await voteForCategory(gameId, categoryId, voteType)
  }

  /**
   * Get all categories for all games in a single batch request (much faster than individual calls).
   * Returns a map: { gameId: [categories...] }
   */
  const getAllGamesCategories = async (): Promise<Record<number, GameCategory[]>> => {
    try {
      const headers: Record<string, string> = {}
      
      // Only include auth header if we have a valid token
      if (token.value) {
        headers['Authorization'] = `Bearer ${token.value}`
      }
      
      const response = await $fetch<{
        success: boolean
        data: Record<number, GameCategory[]>
      }>(`${config.public.apiBase}/games/categories`, {
        headers: Object.keys(headers).length > 0 ? headers : undefined
      })

      if (!response.success) {
        throw new Error('Failed to fetch all games categories')
      }

      return response.data
    } catch (err: any) {
      // Silently handle 401 errors for public endpoints
      if (err.status === 401 || err.statusCode === 401) {
        console.warn('Auth failed for batch games categories, but this is a public endpoint')
        // Try without auth
        try {
          const response = await $fetch<{
            success: boolean
            data: Record<number, GameCategory[]>
          }>(`${config.public.apiBase}/games/categories`)
          return response.success ? response.data : {}
        } catch {
          return {}
        }
      }
      console.error('Failed to fetch all games categories:', err)
      return {}
    }
  }

  return {
    voteForCategory,
    unvoteCategory,
    getGameCategories,
    getAllGamesCategories,
    toggleVote
  }
}

