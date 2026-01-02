import { ref } from 'vue'
import { useAuth } from './useAuth'

export interface DesignName {
  id: number
  name: string
  description: string | null
  createdAt: string
  designSetCount: number
}

export interface CardDesign {
  id: number
  cardIdentifier: string
  displayName: string
  imageBase64: string | null
  hasImage: boolean
  isTemplate: boolean
  templateType: 'basic' | 'court' | 'legendary' | null
  requiresIconComposition: boolean
  rarity: 'common' | 'rare' | 'legendary'
  updatedAt: string
}

export interface DesignSet {
  id: number
  designNameId: number
  designName: string
  type: 'full' | 'template'
  isFree: boolean
  isPremium: boolean
  price: string | null
  theme: string | null
  description: string | null
  cardCount: number
  expectedCardCount: number
  completedCards: number
  isComplete: boolean
  previewImage: string | null
  cards?: CardDesign[]
  createdAt: string
  updatedAt: string
}

export const useDesigns = () => {
  const { token } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const getAuthHeader = () => ({
    'Authorization': `Bearer ${token.value}`,
    'Content-Type': 'application/json'
  })

  // ========== DESIGN NAMES ==========
  const fetchDesignNames = async (): Promise<DesignName[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designNames: DesignName[] } }>(
        '/api/admin/design-names',
        { headers: getAuthHeader() }
      )
      return response.data.designNames
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch design names'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createDesignName = async (name: string, description?: string): Promise<DesignName> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designName: DesignName } }>(
        '/api/admin/design-names',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify({ name, description })
        }
      )
      return response.data.designName
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create design name'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteDesignName = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`/api/admin/design-names/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to delete design name'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== DESIGN SETS ==========
  const fetchDesignSets = async (): Promise<DesignSet[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designSets: DesignSet[] } }>(
        '/api/admin/design-sets',
        { headers: getAuthHeader() }
      )
      return response.data.designSets
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch design sets'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchDesignSet = async (id: number): Promise<DesignSet> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designSet: DesignSet } }>(
        `/api/admin/design-sets/${id}`,
        { headers: getAuthHeader() }
      )
      return response.data.designSet
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch design set'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createDesignSet = async (data: {
    designNameId: number
    type?: 'full' | 'template'
    isFree?: boolean
    price?: string | null
    theme?: string | null
    description?: string | null
  }): Promise<DesignSet> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designSet: DesignSet } }>(
        '/api/admin/design-sets',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.designSet
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to create design set'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateDesignSet = async (id: number, data: {
    name?: string
    isFree?: boolean
    price?: string | null
    theme?: string | null
    description?: string | null
  }): Promise<DesignSet> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designSet: DesignSet } }>(
        `/api/admin/design-sets/${id}`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: JSON.stringify(data)
        }
      )
      return response.data.designSet
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update design set'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== CARD DESIGNS ==========
  const updateCardDesign = async (id: number, imageBase64: string | null): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch(`/api/admin/card-designs/${id}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify({ imageBase64 })
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update card design'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== USER DESIGN PREFERENCES ==========
  const fetchAvailableDesignSets = async (): Promise<DesignSet[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { designSets: DesignSet[] } }>(
        '/api/users/me/available-design-sets',
        { headers: getAuthHeader() }
      )
      return response.data.designSets
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch available design sets'
      throw err
    } finally {
      loading.value = false
    }
  }

  const setActiveDesignSet = async (designSetId: number | null): Promise<void> => {
    loading.value = true
    error.value = null
    try {
      await $fetch('/api/users/me/active-design-set', {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({ designSetId })
      })
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to set active design set'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    
    // Design Names
    fetchDesignNames,
    createDesignName,
    deleteDesignName,
    
    // Design Sets
    fetchDesignSets,
    fetchDesignSet,
    createDesignSet,
    updateDesignSet,
    
    // User Preferences
    fetchAvailableDesignSets,
    setActiveDesignSet,
    
    // Card Designs
    updateCardDesign
  }
}

