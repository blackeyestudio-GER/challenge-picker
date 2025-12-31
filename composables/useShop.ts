import { ref } from 'vue'
import { useAuth } from './useAuth'

export interface DesignSetShopItem {
  id: number
  name: string
  type: string
  is_premium: boolean
  price: string | null
  theme: string | null
  description: string | null
  owned: boolean
}

export interface Purchase {
  id: number
  designSet: {
    id: number
    name: string
    type: string
    theme: string | null
    description: string | null
  }
  purchasedAt: string
  pricePaid: string
  currency: string | null
}

export interface Transaction {
  id: number
  stripeSessionId: string
  status: string
  amount: string
  currency: string
  items: Array<{
    design_set_id: number
    quantity: number
    price: string
  }>
  createdAt: string
  completedAt: string | null
}

export const useShop = () => {
  const { token } = useAuth()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const getAuthHeader = () => ({
    'Authorization': `Bearer ${token.value}`,
    'Content-Type': 'application/json'
  })

  const fetchDesignSets = async (): Promise<DesignSetShopItem[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { design_sets: DesignSetShopItem[] } }>(
        '/api/shop/design-sets',
        { headers: getAuthHeader() }
      )
      return response.data.design_sets
    } catch (err: any) {
      error.value = err.data?.error || 'Failed to fetch design sets'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCheckoutSession = async (designSetIds: number[]): Promise<{ session_id: string; checkout_url: string }> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { session_id: string; checkout_url: string } }>(
        '/api/shop/create-checkout-session',
        {
          method: 'POST',
          headers: getAuthHeader(),
          body: { design_set_ids: designSetIds }
        }
      )
      return response.data
    } catch (err: any) {
      error.value = err.data?.error || 'Failed to create checkout session'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchMyPurchases = async (): Promise<Purchase[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { purchases: Purchase[] } }>(
        '/api/shop/my-purchases',
        { headers: getAuthHeader() }
      )
      return response.data.purchases
    } catch (err: any) {
      error.value = err.data?.error || 'Failed to fetch purchases'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchMyTransactions = async (): Promise<Transaction[]> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { transactions: Transaction[] } }>(
        '/api/shop/my-transactions',
        { headers: getAuthHeader() }
      )
      return response.data.transactions
    } catch (err: any) {
      error.value = err.data?.error || 'Failed to fetch transactions'
      throw err
    } finally {
      loading.value = false
    }
  }

  const retryTransaction = async (transactionId: number): Promise<string> => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch<{ success: boolean; data: { checkoutUrl: string } }>(
        `/api/shop/retry-transaction/${transactionId}`,
        {
          method: 'POST',
          headers: getAuthHeader()
        }
      )
      return response.data.checkoutUrl
    } catch (err: any) {
      error.value = err.data?.error || 'Failed to retry transaction'
      throw err
    } finally {
      loading.value = false
    }
  }

  const checkShopStatus = async (): Promise<{ enabled: boolean; message: string }> => {
    try {
      const response = await $fetch<{ success: boolean; data: { enabled: boolean; message: string } }>(
        '/api/shop/status'
      )
      return response.data
    } catch (err: any) {
      // Default to enabled if API fails
      return { enabled: true, message: 'Shop status unknown' }
    }
  }

  return {
    loading,
    error,
    fetchDesignSets,
    createCheckoutSession,
    fetchMyPurchases,
    fetchMyTransactions,
    retryTransaction,
    checkShopStatus
  }
}

