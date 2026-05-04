<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useShop, type Purchase, type Transaction } from '~/composables/useShop'
import { Icon } from '#components'
import { extractErrorMessage } from '~/utils/errorHandler'

definePageMeta({
  middleware: 'auth'
})

const { fetchMyPurchases, fetchMyTransactions, retryTransaction, loading } = useShop()
const { notifyApiError } = useNotify()
const purchases = ref<Purchase[]>([])
const transactions = ref<Transaction[]>([])
const activeTab = ref<'purchases' | 'transactions'>('purchases')
const error = ref<string | null>(null)

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  try {
    error.value = null
    purchases.value = await fetchMyPurchases()
    transactions.value = await fetchMyTransactions()
  } catch (err: unknown) {
    error.value = extractErrorMessage(err, 'Failed to load purchases')
    notifyApiError(err, 'Failed to load purchases')
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

const retryableTransactions = computed(() => {
  return transactions.value.filter(t => t.status === 'failed' || t.status === 'pending')
})

const purchasePreviewTiles = (purchase: Purchase): string[] => {
  const list = purchase.designSet.preview_images?.filter(s => s && s.length > 0) ?? []
  if (list.length > 0) {
    return list.slice(0, 4)
  }
  const single = purchase.designSet.preview_image
  return single ? [single] : []
}

const handleRetry = async (transactionId: number) => {
  try {
    const checkoutUrl = await retryTransaction(transactionId)
    window.location.href = checkoutUrl
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to retry transaction')
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
        class="px-6 py-3 font-semibold transition border-b-2"
        :class="{
          'text-cyan border-cyan': activeTab === 'purchases',
          'text-gray-400 border-transparent hover:text-gray-300': activeTab !== 'purchases'
        }"
        @click="activeTab = 'purchases'"
      >
        Completed Purchases
      </button>
      <button
        class="px-6 py-3 font-semibold transition border-b-2"
        :class="{
          'text-cyan border-cyan': activeTab === 'transactions',
          'text-gray-400 border-transparent hover:text-gray-300': activeTab !== 'transactions'
        }"
        @click="activeTab = 'transactions'"
      >
        Transaction History
        <span v-if="retryableTransactions.length > 0" class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full">
          {{ retryableTransactions.length }}
        </span>
      </button>
    </div>

    <LoadingState v-if="loading" message="Loading purchases..." />

    <ErrorState v-else-if="error" :message="error" />

    <!-- Purchases Tab -->
    <div v-else-if="activeTab === 'purchases'">
      <EmptyState
        v-if="purchases.length === 0"
        icon="heroicons:shopping-bag"
        title="No purchases yet"
        message="You haven't purchased any designs yet."
      >
        <NuxtLink
          to="/shop"
          class="btn btn-primary"
        >
          Browse Shop
        </NuxtLink>
      </EmptyState>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="purchase in purchases"
          :key="purchase.id"
          class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg overflow-hidden hover:border-cyan transition-all flex flex-col"
        >
          <DesignSetPreviewMosaic
            :images="purchasePreviewTiles(purchase)"
            :alt-prefix="purchase.designSet.name"
            variant="hero"
          />
          <div class="p-6 flex-1 flex flex-col">
            <div class="flex items-start justify-between gap-2 mb-4">
              <h3 class="text-xl font-bold text-white">{{ purchase.designSet.name }}</h3>
              <Icon name="heroicons:check-circle" class="w-6 h-6 text-green-500 flex-shrink-0" aria-hidden="true" />
            </div>

            <p v-if="purchase.designSet.theme" class="text-sm text-gray-400 mb-2">
              Theme: {{ purchase.designSet.theme }}
            </p>

            <p class="text-sm text-gray-400 mb-4 flex-1">{{ purchase.designSet.description }}</p>

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
    </div>

    <!-- Transactions Tab -->
    <div v-else>
      <EmptyState
        v-if="transactions.length === 0"
        icon="heroicons:document-text"
        title="No transactions yet"
        message="No transaction history is available for this account yet."
      />

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
                    'admin-badge admin-badge--warning': transaction.status === 'pending',
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
                class="px-4 py-2 bg-cyan hover:bg-cyan-dark text-white font-semibold rounded-lg transition-all flex items-center gap-2"
                @click="handleRetry(transaction.id)"
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
