import { ref } from 'vue'
import { useAuth } from './useAuth'

export interface AdminGame {
  id: number
  name: string
  description: string | null
  image: string | null
  rulesetCount: number
  categories: Array<{ id: number; name: string; slug: string }>
  isCategoryRepresentative: boolean
  isActive: boolean
  steamLink: string | null
  epicLink: string | null
  gogLink: string | null
  twitchCategory: string | null
}

export interface AdminRuleset {
  id: number
  name: string
  description: string | null
  games: Array<{ id: number; name: string }>
  defaultRules: Array<{ id: number; name: string; ruleType: string }>
  ruleCount: number
}

export interface RuleDifficultyLevel {
  difficultyLevel: number
  durationMinutes: number | null  // null if not time-based
  amount: number | null  // null if not counter-based
  description: string | null
}

export interface AdminRule {
  id: number
  name: string
  description: string | null
  ruleType: 'basic' | 'court' | 'legendary'
  difficultyLevels: RuleDifficultyLevel[]
}

export interface CreateGameRequest {
  name: string
  description?: string
  image?: string
  isCategoryRepresentative?: boolean
  steamLink?: string
  epicLink?: string
  gogLink?: string
  twitchCategory?: string
}

export interface UpdateGameRequest {
  name?: string
  description?: string
  image?: string
  categoryIds?: number[]
  isCategoryRepresentative?: boolean
  steamLink?: string
  epicLink?: string
  gogLink?: string
  twitchCategory?: string
}

export interface RulesetRuleAssignment {
  ruleId: number
  tarotCardIdentifier: string
  position: number
  isDefault: boolean
}

export interface CreateRulesetRequest {
  name: string
  description?: string
  gameIds: number[]
  rules?: RulesetRuleAssignment[]  // Rules to assign with default flags
}

export interface UpdateRulesetRequest {
  name?: string
  description?: string
  gameIds?: number[]
  rules?: RulesetRuleAssignment[]  // Rules to assign with default flags
}

export interface CreateRuleRequest {
  name: string
  description?: string
  ruleType: 'basic' | 'court' | 'legendary'
  difficultyLevels: Array<{
    difficultyLevel: number
    durationMinutes: number
  }>
}

export interface UpdateRuleRequest {
  name?: string
  description?: string
  ruleType?: 'basic' | 'court' | 'legendary'
  difficultyLevels?: Array<{
    difficultyLevel: number
    durationMinutes: number
  }>
}

export interface GamePagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

export interface GameListResponse {
  games: AdminGame[]
  pagination: GamePagination
}

export interface RulePagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

export interface RuleListResponse {
  rules: AdminRule[]
  pagination: RulePagination
}

export interface GameName {
  id: number
  name: string
}

export interface AdminCategory {
  id: number
  name: string
  description: string | null
  slug: string
  gameCount: number
  games: Array<{ id: number; name: string; image: string | null; isActive: boolean; isCategoryRepresentative: boolean }>
}

export interface CreateCategoryRequest {
  name: string
  description?: string
}

export interface UpdateCategoryRequest {
  name?: string
  description?: string
  gameIds?: number[]
}

export const useAdmin = () => {
  const { token } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const getAuthHeader = () => ({
    'Authorization': `Bearer ${token.value}`,
    'Content-Type': 'application/json'
  })

  // ========== GAMES ==========
  const fetchAdminGames = async (page: number = 1, limit: number = 50, search: string = ''): Promise<GameListResponse> => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
        ...(search && { search })
      })
      
      const response = await $fetch<{ success: boolean; data: GameListResponse }>(
        `/api/admin/games?${params.toString()}`,
        { headers: getAuthHeader() }
      )
      return response.data
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch games'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchGameNames = async (): Promise<GameName[]> => {
    try {
      const response = await $fetch<{ success: boolean; data: { games: GameName[] } }>(
        '/api/admin/games/names',
        { headers: getAuthHeader() }
      )
      return response.data.games
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch game names'
      throw err
    }
  }

  const createGame = async (data: CreateGameRequest): Promise<AdminGame> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { game: AdminGame } }>(
        '/api/admin/games',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.game
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create game'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateGame = async (id: number, data: UpdateGameRequest): Promise<AdminGame> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { game: AdminGame } }>(
        `/api/admin/games/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.game
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update game'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deactivateGame = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`/api/admin/games/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to deactivate game'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== RULESETS ==========
  const fetchAdminRulesets = async (gameId?: number): Promise<AdminRuleset[]> => {
    loading.value = true
    error.value = null
    try {
      const url = gameId ? `/api/admin/rulesets?gameId=${gameId}` : '/api/admin/rulesets'
      const response = await $fetch<{ success: boolean; data: { rulesets: AdminRuleset[] } }>(
        url,
        { headers: getAuthHeader() }
      )
      return response.data.rulesets
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch rulesets'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createRuleset = async (data: CreateRulesetRequest): Promise<AdminRuleset> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { ruleset: AdminRuleset } }>(
        '/api/admin/rulesets',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.ruleset
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create ruleset'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateRuleset = async (id: number, data: UpdateRulesetRequest): Promise<AdminRuleset> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { ruleset: AdminRuleset } }>(
        `/api/admin/rulesets/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.ruleset
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update ruleset'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteRuleset = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`/api/admin/rulesets/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to delete ruleset'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== RULES ==========
  const fetchAdminRules = async (page: number = 1, limit: number = 20, search: string = ''): Promise<RuleListResponse> => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
        ...(search && { search })
      })
      
      const response = await $fetch<{ success: boolean; data: RuleListResponse }>(
        `/api/admin/rules?${params.toString()}`,
        { headers: getAuthHeader() }
      )
      return response.data
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch rules'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createRule = async (data: CreateRuleRequest): Promise<AdminRule> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { rule: AdminRule } }>(
        '/api/admin/rules',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.rule
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create rule'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateRule = async (id: number, data: UpdateRuleRequest): Promise<AdminRule> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { rule: AdminRule } }>(
        `/api/admin/rules/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.rule
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update rule'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteRule = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`/api/admin/rules/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to delete rule'
      throw err
    } finally {
      loading.value = false
    }
  }

  const addRuleToRuleset = async (ruleId: number, rulesetId: number): Promise<AdminRule> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { rule: AdminRule } }>(
        `/api/admin/rules/${ruleId}/rulesets/${rulesetId}`,
        {
          method: 'POST',
          headers: getAuthHeader()
        }
      )
      return response.data.rule
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to add rule to ruleset'
      throw err
    } finally {
      loading.value = false
    }
  }

  const removeRuleFromRuleset = async (ruleId: number, rulesetId: number): Promise<AdminRule> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { rule: AdminRule } }>(
        `/api/admin/rules/${ruleId}/rulesets/${rulesetId}`,
        {
          method: 'DELETE',
          headers: getAuthHeader()
        }
      )
      return response.data.rule
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to remove rule from ruleset'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== CATEGORIES ==========
  const fetchAdminCategories = async (): Promise<AdminCategory[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { categories: AdminCategory[] } }>(
        '/api/admin/categories',
        { headers: getAuthHeader() }
      )
      return response.data.categories
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch categories'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCategory = async (data: CreateCategoryRequest): Promise<AdminCategory> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { category: AdminCategory } }>(
        '/api/admin/categories',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.category
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create category'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCategory = async (id: number, data: UpdateCategoryRequest): Promise<AdminCategory> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { category: AdminCategory } }>(
        `/api/admin/categories/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.category
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update category'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCategory = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`/api/admin/categories/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to delete category'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    
    // Categories
    fetchAdminCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    
    // Games
    fetchAdminGames,
    fetchGameNames,
    createGame,
    updateGame,
    deactivateGame,
    
    // Rulesets
    fetchAdminRulesets,
    createRuleset,
    updateRuleset,
    deleteRuleset,
    
    // Rules
    fetchAdminRules,
    createRule,
    updateRule,
    deleteRule,
    addRuleToRuleset,
    removeRuleFromRuleset
  }
}

