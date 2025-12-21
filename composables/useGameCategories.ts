export interface GameCategory {
  id: number
  name: string
  slug: string
  voteCount: number
  userVoted: boolean
}

export const useGameCategories = () => {
  const { token } = useAuth()

  const voteForCategory = async (gameId: number, categoryId: number): Promise<{ voteCount: number; userVoted: boolean }> => {
    try {
      const response = await $fetch<{
        success: boolean
        data: { voteCount: number; userVoted: boolean }
      }>(`/api/games/${gameId}/categories/${categoryId}/vote`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
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

  const unvoteCategory = async (gameId: number, categoryId: number): Promise<{ voteCount: number; userVoted: boolean }> => {
    try {
      const response = await $fetch<{
        success: boolean
        data: { voteCount: number; userVoted: boolean }
      }>(`/api/games/${gameId}/categories/${categoryId}/vote`, {
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
      }>(`/api/games/${gameId}/categories`, {
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
          }>(`/api/games/${gameId}/categories`)
          return response.success ? response.data : []
        } catch {
          return []
        }
      }
      console.error('Failed to fetch game categories:', err)
      return []
    }
  }

  const toggleVote = async (gameId: number, categoryId: number, currentlyVoted: boolean): Promise<{ voteCount: number; userVoted: boolean }> => {
    if (currentlyVoted) {
      return await unvoteCategory(gameId, categoryId)
    } else {
      return await voteForCategory(gameId, categoryId)
    }
  }

  return {
    voteForCategory,
    unvoteCategory,
    getGameCategories,
    toggleVote
  }
}

