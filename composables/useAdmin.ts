import { ref } from 'vue'
import { useAuth } from './useAuth'
import type {
  FeatureSettingsResponse,
  PayoutDecisionResponse,
  PayoutRequestsResponse,
  ShopSettingsResponse,
  UpdateFeatureSettingsResponse,
  UpdateShopSettingsResponse
} from '~/generated/api-contracts'
import { extractErrorMessage } from '~/utils/errorHandler'

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
  durationSeconds: number | null  // null if not time-based (stored in seconds)
  amount: number | null  // null if not counter-based
  description: string | null
}

export interface AdminRule {
  id: number
  name: string
  description: string | null
  ruleType: 'basic' | 'court' | 'legendary'
  iconIdentifier?: string | null
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
  iconIdentifier?: string | null
  difficultyLevels: Array<{
    difficultyLevel: number
    durationSeconds: number | null
    amount?: number | null
  }>
}

export interface UpdateRuleRequest {
  name?: string
  description?: string
  ruleType?: 'basic' | 'court' | 'legendary'
  iconIdentifier?: string | null
  difficultyLevels?: Array<{
    difficultyLevel: number
    durationSeconds: number | null
    amount: number | null
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

export interface GameNamesResponse {
  success: boolean
  data: {
    games: GameName[]
  }
}

export interface AdminCategory {
  id: number
  name: string
  description: string | null
  slug: string
  gameCount: number
  games: Array<{ id: number; name: string; image: string | null; isActive: boolean; isCategoryRepresentative: boolean }>
}

export interface GameMutationResponse {
  success: boolean
  data: {
    message: string
    game: AdminGame
  }
}

export interface RulesetMutationResponse {
  success: boolean
  data: {
    message: string
    ruleset: AdminRuleset
  }
}

export interface RuleMutationResponse {
  success: boolean
  data: {
    message: string
    rule: AdminRule
  }
}

export interface CategoryMutationResponse {
  success: boolean
  data: {
    message: string
    category: AdminCategory
  }
}

export interface ShopSettingsResponse {
  success: boolean
  data: {
    shopEnabled: boolean
  }
}

export interface UpdateShopSettingsResponse {
  success: boolean
  data: {
    message: string
    shopEnabled: boolean
  }
}

export type AdminPayoutRequest = PayoutRequestsResponse['data']['payoutRequests'][number]

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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch games')
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchGameNames = async (): Promise<GameName[]> => {
    try {
      const response = await $fetch<GameNamesResponse>(
        '/api/admin/games/names',
        { headers: getAuthHeader() }
      )
      return response.data.games
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch game names')
      throw err
    }
  }

  const createGame = async (data: CreateGameRequest): Promise<AdminGame> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<GameMutationResponse>(
        '/api/admin/games',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.game
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to create game')
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateGame = async (id: number, data: UpdateGameRequest): Promise<AdminGame> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<GameMutationResponse>(
        `/api/admin/games/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.game
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to update game')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to deactivate game')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch rulesets')
      throw err
    } finally {
      loading.value = false
    }
  }

  const createRuleset = async (data: CreateRulesetRequest): Promise<AdminRuleset> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<RulesetMutationResponse>(
        '/api/admin/rulesets',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.ruleset
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to create ruleset')
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateRuleset = async (id: number, data: UpdateRulesetRequest): Promise<AdminRuleset> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<RulesetMutationResponse>(
        `/api/admin/rulesets/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.ruleset
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to update ruleset')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to delete ruleset')
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== RULES ==========
  const fetchAdminRules = async (page: number = 1, limit: number = 20, search: string = '', withoutIcon: boolean = false): Promise<RuleListResponse> => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
        ...(search && { search }),
        ...(withoutIcon && { withoutIcon: 'true' })
      })
      
      const response = await $fetch<{ success: boolean; data: RuleListResponse }>(
        `/api/admin/rules?${params.toString()}`,
        { headers: getAuthHeader() }
      )
      return response.data
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch rules')
      throw err
    } finally {
      loading.value = false
    }
  }

  const createRule = async (data: CreateRuleRequest): Promise<AdminRule> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<RuleMutationResponse>(
        '/api/admin/rules',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.rule
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to create rule')
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateRule = async (id: number, data: UpdateRuleRequest): Promise<AdminRule> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<RuleMutationResponse>(
        `/api/admin/rules/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.rule
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to update rule')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to delete rule')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to add rule to ruleset')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to remove rule from ruleset')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch categories')
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCategory = async (data: CreateCategoryRequest): Promise<AdminCategory> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<CategoryMutationResponse>(
        '/api/admin/categories',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.category
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to create category')
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCategory = async (id: number, data: UpdateCategoryRequest): Promise<AdminCategory> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<CategoryMutationResponse>(
        `/api/admin/categories/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.category
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to update category')
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
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to delete category')
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
    removeRuleFromRuleset,
    
    // Shop Settings
    fetchShopSettings: async () => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<ShopSettingsResponse>(
          '/api/admin/shop/settings',
          { headers: getAuthHeader() }
        )
        return response.data
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to fetch shop settings')
        throw err
      } finally {
        loading.value = false
      }
    },
    
    updateShopSettings: async (shopEnabled: boolean) => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<UpdateShopSettingsResponse>(
          '/api/admin/shop/settings',
          {
            method: 'PUT',
            headers: getAuthHeader(),
            body: JSON.stringify({ shopEnabled })
          }
        )
        return response.data
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to update shop settings')
        throw err
      } finally {
        loading.value = false
      }
    },

    fetchFeatureSettings: async () => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<FeatureSettingsResponse>(
          '/api/admin/features/settings',
          { headers: getAuthHeader() }
        )
        return response.data.features
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to fetch feature settings')
        throw err
      } finally {
        loading.value = false
      }
    },

    updateFeatureSetting: async (featureKey: string, enabled: boolean) => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<UpdateFeatureSettingsResponse>(
          '/api/admin/features/settings',
          {
            method: 'PUT',
            headers: getAuthHeader(),
            body: { featureKey, enabled }
          }
        )
        return response.data.feature
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to update feature settings')
        throw err
      } finally {
        loading.value = false
      }
    },

    fetchPayoutRequests: async () => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<PayoutRequestsResponse>(
          '/api/admin/payout-requests',
          { headers: getAuthHeader() }
        )
        return response.data.payoutRequests
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to fetch payout requests')
        throw err
      } finally {
        loading.value = false
      }
    },

    approvePayoutRequest: async (id: number, adminNotes: string | null) => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<PayoutDecisionResponse>(
          `/api/admin/payout-requests/${id}/approve`,
          {
            method: 'POST',
            headers: getAuthHeader(),
            body: { adminNotes }
          }
        )
        return response.data.payoutRequest
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to approve payout request')
        throw err
      } finally {
        loading.value = false
      }
    },

    rejectPayoutRequest: async (id: number, adminNotes: string) => {
      loading.value = true
      error.value = null
      try {
        const response = await $fetch<PayoutDecisionResponse>(
          `/api/admin/payout-requests/${id}/reject`,
          {
            method: 'POST',
            headers: getAuthHeader(),
            body: { adminNotes }
          }
        )
        return response.data.payoutRequest
      } catch (err: unknown) {
        error.value = extractErrorMessage(err, 'Failed to reject payout request')
        throw err
      } finally {
        loading.value = false
      }
    }
  }
}
