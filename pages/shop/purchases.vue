<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useShop, type Purchase, type Transaction } from '~/composables/useShop'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { fetchMyPurchases, fetchMyTransactions, retryTransaction, loading } = useShop()
const purchases = ref<Purchase[]>([])
const transactions = ref<Transaction[]>([])
const activeTab = ref<'purchases' | 'transactions'>('purchases')

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  try {
    purchases.value = await fetchMyPurchases()
    transactions.value = await fetchMyTransactions()
  } catch (err) {
    console.error('Failed to load data:', err)
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatPrice = (price: string, currency: string | null) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency || 'USD'
  }).format(parseFloat(price))
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'completed':
      return 'text-green-500'
    case 'pending':
      return 'text-yellow-500'
    case 'failed':
      return 'text-red-500'
    case 'refunded':
      return 'text-gray-500'
    case 'cancelled':
      return 'text-gray-400'
    default:
      return 'text-gray-500'
  }
}

const retryableTransactions = computed(() => {
  return transactions.value.filter(t => t.status === 'failed' || t.status === 'pending')
})

const handleRetry = async (transactionId: number) => {
  try {
    const checkoutUrl = await retryTransaction(transactionId)
    window.location.href = checkoutUrl
  } catch (err) {
    console.error('Failed to retry transaction:', err)
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <NuxtLink
        to="/shop"
        class="text-cyan hover:text-cyan-light mb-4 inline-flex items-center gap-2"
      >
        <Icon name="heroicons:arrow-left" class="w-5 h-5" />
        Back to Shop
      </NuxtLink>
      <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
        My Purchases
      </h1>
      <p class="text-gray-300">View your purchase history and transaction details</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-3 mb-6 border-b border-gray-700">
      <button
        @click="activeTab = 'purchases'"
        class="px-6 py-3 font-semibold transition border-b-2"
        :class="{
          'text-cyan border-cyan': activeTab === 'purchases',
          'text-gray-400 border-transparent hover:text-gray-300': activeTab !== 'purchases'
        }"
      >
        Completed Purchases
      </button>
      <button
        @click="activeTab = 'transactions'"
        class="px-6 py-3 font-semibold transition border-b-2"
        :class="{
          'text-cyan border-cyan': activeTab === 'transactions',
          'text-gray-400 border-transparent hover:text-gray-300': activeTab !== 'transactions'
        }"
      >
        Transaction History
        <span v-if="retryableTransactions.length > 0" class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full">
          {{ retryableTransactions.length }}
        </span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Purchases Tab -->
    <div v-else-if="activeTab === 'purchases'">
      <div v-if="purchases.length === 0" class="text-center py-12">
        <Icon name="heroicons:shopping-bag" class="w-16 h-16 mx-auto text-gray-600 mb-4" />
        <p class="text-gray-400 mb-6">You haven't purchased any designs yet</p>
        <NuxtLink
          to="/shop"
          class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all inline-block"
        >
          Browse Shop
        </NuxtLink>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="purchase in purchases"
          :key="purchase.id"
          class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-cyan transition-all"
        >
          <div class="flex items-start justify-between mb-4">
            <h3 class="text-xl font-bold text-white">{{ purchase.designSet.name }}</h3>
            <Icon name="heroicons:check-circle" class="w-6 h-6 text-green-500 flex-shrink-0" />
          </div>
          
          <p v-if="purchase.designSet.theme" class="text-sm text-gray-400 mb-2">
            Theme: {{ purchase.designSet.theme }}
          </p>
          
          <p class="text-sm text-gray-400 mb-4">{{ purchase.designSet.description }}</p>
          
          <div class="border-t border-gray-700 pt-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-400">Purchased:</span>
              <span class="text-white">{{ formatDate(purchase.purchasedAt) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-400">Price:</span>
              <span class="text-white font-semibold">
                {{ parseFloat(purchase.pricePaid) === 0 ? 'Free / Gift' : formatPrice(purchase.pricePaid, purchase.currency) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions Tab -->
    <div v-else>
      <div v-if="transactions.length === 0" class="text-center py-12">
        <Icon name="heroicons:document-text" class="w-16 h-16 mx-auto text-gray-600 mb-4" />
        <p class="text-gray-400">No transaction history</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="transaction in transactions"
          :key="transaction.id"
          class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-gray-600 transition-all"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-bold text-white">
                  Transaction #{{ transaction.id }}
                </h3>
                <span
                  class="px-3 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-green-500/20 text-green-500': transaction.status === 'completed',
                    'bg-yellow-500/20 text-yellow-500': transaction.status === 'pending',
                    'bg-red-500/20 text-red-500': transaction.status === 'failed',
                    'bg-gray-500/20 text-gray-500': transaction.status === 'refunded' || transaction.status === 'cancelled'
                  }"
                >
                  {{ transaction.status.toUpperCase() }}
                </span>
              </div>
              
              <div class="space-y-1 text-sm text-gray-400">
                <p>Created: {{ formatDate(transaction.createdAt) }}</p>
                <p v-if="transaction.completedAt">Completed: {{ formatDate(transaction.completedAt) }}</p>
                <p class="font-semibold text-white">
                  Amount: {{ formatPrice(transaction.amount, transaction.currency) }}
                </p>
              </div>
            </div>

            <div v-if="transaction.status === 'failed' || transaction.status === 'pending'" class="ml-4">
              <button
                @click="handleRetry(transaction.id)"
                class="px-4 py-2 bg-cyan hover:bg-cyan-dark text-white font-semibold rounded-lg transition-all flex items-center gap-2"
              >
                <Icon name="heroicons:arrow-path" class="w-5 h-5" />
                Retry
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

