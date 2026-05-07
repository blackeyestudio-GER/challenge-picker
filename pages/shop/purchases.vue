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
      <NuxtLink to="/shop" class="admin-link mb-4 inline-flex items-center gap-2">
        <Icon name="heroicons:arrow-left" class="w-5 h-5" />
        Back to Shop
      </NuxtLink>
      <h1 class="text-4xl font-bold text-[var(--color-text-primary)] mb-2">
        My Purchases
      </h1>
      <p class="text-[var(--color-text-secondary)]">View your purchase history and transaction details</p>
    </div>

    <div class="flex gap-3 mb-6 border-b border-[var(--color-border-secondary)]">
      <button
        class="px-6 py-3 font-semibold transition border-b-2"
        :class="{
          'text-[var(--color-accent-primary)] border-[var(--color-accent-primary)]': activeTab === 'purchases',
          'text-[var(--color-text-muted)] border-transparent hover:text-[var(--color-text-secondary)]': activeTab !== 'purchases'
        }"
        @click="activeTab = 'purchases'"
      >
        Completed Purchases
      </button>
      <button
        class="px-6 py-3 font-semibold transition border-b-2"
        :class="{
          'text-[var(--color-accent-primary)] border-[var(--color-accent-primary)]': activeTab === 'transactions',
          'text-[var(--color-text-muted)] border-transparent hover:text-[var(--color-text-secondary)]': activeTab !== 'transactions'
        }"
        @click="activeTab = 'transactions'"
      >
        Transaction History
        <span v-if="retryableTransactions.length > 0" class="ml-2 px-2 py-1 text-xs rounded-full bg-[var(--status-failed-text)] text-white">
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
          class="admin-panel admin-panel--interactive rounded-lg overflow-hidden flex flex-col"
        >
          <DesignSetPreviewMosaic
            :images="purchasePreviewTiles(purchase)"
            :alt-prefix="purchase.designSet.name"
            variant="hero"
          />
          <div class="p-6 flex-1 flex flex-col">
            <div class="flex items-start justify-between gap-2 mb-4">
              <h3 class="text-xl font-bold text-[var(--color-text-primary)]">{{ purchase.designSet.name }}</h3>
              <Icon name="heroicons:check-circle" class="w-6 h-6 text-[var(--status-active-text)] flex-shrink-0" aria-hidden="true" />
            </div>

            <p v-if="purchase.designSet.theme" class="text-sm text-[var(--color-text-muted)] mb-2">
              Theme: {{ purchase.designSet.theme }}
            </p>

            <p class="text-sm text-[var(--color-text-muted)] mb-4 flex-1">{{ purchase.designSet.description }}</p>

            <div class="border-t border-[var(--color-border-secondary)] pt-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-[var(--color-text-muted)]">Purchased:</span>
              <span class="text-[var(--color-text-primary)]">{{ formatDate(purchase.purchasedAt) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-[var(--color-text-muted)]">Price:</span>
              <span class="text-[var(--color-text-primary)] font-semibold">
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
          class="admin-panel admin-panel--interactive rounded-lg p-6"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-bold text-[var(--color-text-primary)]">
                  Transaction #{{ transaction.id }}
                </h3>
                <span
                  class="px-3 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'admin-badge admin-badge--success': transaction.status === 'completed',
                    'admin-badge admin-badge--warning': transaction.status === 'pending',
                    'admin-badge admin-badge--danger': transaction.status === 'failed',
                    'admin-badge admin-badge--secondary': transaction.status === 'refunded' || transaction.status === 'cancelled'
                  }"
                >
                  {{ transaction.status.toUpperCase() }}
                </span>
              </div>
              
              <div class="space-y-1 text-sm text-[var(--color-text-muted)]">
                <p>Created: {{ formatDate(transaction.createdAt) }}</p>
                <p v-if="transaction.completedAt">Completed: {{ formatDate(transaction.completedAt) }}</p>
                <p class="font-semibold text-[var(--color-text-primary)]">
                  Amount: {{ formatPrice(transaction.amount, transaction.currency) }}
                </p>
              </div>
            </div>

            <div v-if="transaction.status === 'failed' || transaction.status === 'pending'" class="ml-4">
              <button class="btn btn-primary px-4 py-2 font-semibold flex items-center gap-2" @click="handleRetry(transaction.id)">
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
