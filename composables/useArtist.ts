import { ref } from 'vue'
import { useAuth } from './useAuth'

export interface ArtistEarnings {
  totalEarnings: string
  totalSales: number
  designSets: Array<{
    designSetId: number
    designName: string
    totalEarnings: string
    purchaseCount: number
  }>
}

export interface EarningsHistoryItem {
  id: number
  designSetId: number | null
  designSetName: string | null
  amount: string
  purchasePrice: string
  feePercentage: string
  currency: string | null
  earnedAt: string
}

export interface PayoutRequest {
  id: number
  amount: string
  currency: string
  status: 'pending' | 'approved' | 'rejected' | 'paid'
  isAutomated: boolean
  requestedAt: string
  processedAt: string | null
  adminNotes: string | null
}

export const useArtist = () => {
  const config = useRuntimeConfig()
  const { getAuthHeader } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchEarnings = async (): Promise<ArtistEarnings> => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{
        success: boolean
        data: ArtistEarnings
      }>(`${config.public.apiBase}/artist/earnings`, {
        headers: getAuthHeader()
      })

      if (response.success) {
        return response.data
      }
      throw new Error('Failed to fetch earnings')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch earnings'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchEarningsHistory = async (limit = 50, offset = 0): Promise<{
    earnings: EarningsHistoryItem[]
    total: number
    limit: number
    offset: number
  }> => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{
        success: boolean
        data: {
          earnings: EarningsHistoryItem[]
          total: number
          limit: number
          offset: number
        }
      }>(`${config.public.apiBase}/artist/earnings/history`, {
        params: { limit, offset },
        headers: getAuthHeader()
      })

      if (response.success) {
        return response.data
      }
      throw new Error('Failed to fetch earnings history')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch earnings history'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Manual payout requests removed - payouts are now automated via cron job
  // The requestPayout function is kept for backward compatibility but should not be used
  const requestPayout = async (amount: string, currency = 'USD'): Promise<PayoutRequest> => {
    throw new Error('Manual payout requests are disabled. Payouts are processed automatically via cron job.')
  }

  const fetchPayoutRequests = async (limit = 50, offset = 0): Promise<{
    payoutRequests: PayoutRequest[]
    pendingAmount: string
  }> => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{
        success: boolean
        data: {
          payoutRequests: PayoutRequest[]
          pendingAmount: string
        }
      }>(`${config.public.apiBase}/artist/payout-requests`, {
        params: { limit, offset },
        headers: getAuthHeader()
      })

      if (response.success) {
        return response.data
      }
      throw new Error('Failed to fetch payout requests')
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to fetch payout requests'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    fetchEarnings,
    fetchEarningsHistory,
    requestPayout,
    fetchPayoutRequests
  }
}
