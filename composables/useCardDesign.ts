import { ref, computed } from 'vue'
import { useAuth } from './useAuth'
import type {
  CardDesignData as GeneratedCardDesignData,
  CardDesignsResponse as GeneratedCardDesignsResponse
} from '~/generated/api-contracts'
import { extractErrorMessage } from '~/utils/errorHandler'

export type CardDesignData = GeneratedCardDesignData

export interface CardDesignResult {
  design: CardDesignData | null
  designSetId: number | null
  designSetName: string
  isLoading: boolean
  error: string | null
}

export type CardDesignsResponse = GeneratedCardDesignsResponse

/**
 * Composable for fetching card designs with fallback logic
 * Handles fetching designs for specific tarot card identifiers
 * Falls back to templates if full designs aren't available
 */
export const useCardDesign = () => {
  const { getAuthHeader } = useAuth()
  const config = useRuntimeConfig()
  
  const loading = ref(false)
  const error = ref<string | null>(null)
  const cardDesignsCache = ref<Record<string, CardDesignData | null>>({})
  const designSetInfo = ref<{
    designSetId: number | null
    designSetName: string
  }>({
    designSetId: null,
    designSetName: 'Text Only'
  })

  /**
   * Fetch card designs for given tarot card identifiers
   * @param identifiers Array of tarot card identifiers (e.g., ['the_fool', 'ace_of_wands'])
   * @param userUuid Optional user UUID to fetch their active design set (for viewers)
   * @returns Promise that resolves when designs are fetched
   */
  const fetchCardDesigns = async (
    identifiers: string[],
    userUuid?: string
  ): Promise<void> => {
    if (identifiers.length === 0) {
      return
    }

    loading.value = true
    error.value = null

    try {
      const params: { identifiers: string; userUuid?: string } = {
        identifiers: identifiers.join(',')
      }

      if (userUuid) {
        params.userUuid = userUuid
      }

      const response = await $fetch<CardDesignsResponse>(`${config.public.apiBase}/design/card-designs`, {
        method: 'GET',
        params,
        headers: userUuid ? {} : getAuthHeader()
      })

      if (response.success && response.data) {
        // Update cache
        Object.entries(response.data.cardDesigns).forEach(([identifier, design]) => {
          if (design) {
            cardDesignsCache.value[identifier] = {
              id: design.id,
              cardIdentifier: design.cardIdentifier,
              imageBase64: design.imageBase64,
              isTemplate: design.isTemplate,
              templateType: design.templateType as 'basic' | 'court' | 'legendary' | null
            }
          } else {
            cardDesignsCache.value[identifier] = null
          }
        })

        // Update design set info
        designSetInfo.value = {
          designSetId: response.data.designSetId,
          designSetName: response.data.designSetName
        }
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch card designs')
      // Initialize empty cache on error
      identifiers.forEach(identifier => {
        cardDesignsCache.value[identifier] = null
      })
    } finally {
      loading.value = false
    }
  }

  /**
   * Get card design for a specific tarot card identifier
   * @param identifier Tarot card identifier
   * @returns Card design data or null if not found
   */
  const getCardDesign = (identifier: string | null): CardDesignData | null => {
    if (!identifier) {
      return null
    }
    return cardDesignsCache.value[identifier] ?? null
  }

  /**
   * Check if a card design is a template (requires icon composition)
   */
  const isTemplate = (identifier: string | null): boolean => {
    const design = getCardDesign(identifier)
    return design?.isTemplate ?? false
  }

  /**
   * Get template type for a card design
   */
  const getTemplateType = (identifier: string | null): 'basic' | 'court' | 'legendary' | null => {
    const design = getCardDesign(identifier)
    return design?.templateType ?? null
  }

  /**
   * Clear the cache
   */
  const clearCache = (): void => {
    cardDesignsCache.value = {}
    designSetInfo.value = {
      designSetId: null,
      designSetName: 'Text Only'
    }
  }

  return {
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    designSetInfo: computed(() => designSetInfo.value),
    fetchCardDesigns,
    getCardDesign,
    isTemplate,
    getTemplateType,
    clearCache
  }
}
