<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useArtist, type ArtistEarnings, type EarningsHistoryItem, type PayoutRequest } from '~/composables/useArtist'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { user, loadAuth } = useAuth()
const { fetchEarnings, fetchEarningsHistory, requestPayout, fetchPayoutRequests, loading, error } = useArtist()

const earnings = ref<ArtistEarnings | null>(null)
const earningsHistory = ref<EarningsHistoryItem[]>([])
const payoutRequests = ref<PayoutRequest[]>([])
const pendingPayoutAmount = ref('0.00')
// Removed manual payout request functionality - now automated via cron

const MINIMUM_PAYOUT = 10.00
const availableBalance = computed(() => {
  if (!earnings.value) return '0.00'
  const total = parseFloat(earnings.value.totalEarnings)
  const pending = parseFloat(pendingPayoutAmount.value)
  return Math.max(0, total - pending).toFixed(2)
})

const hasPendingPayout = computed(() => {
  return payoutRequests.value.some(r => r.status === 'pending')
})

const nextPayoutInfo = computed(() => {
  if (!earnings.value) return null
  const balance = parseFloat(availableBalance.value)
  if (balance >= MINIMUM_PAYOUT && !hasPendingPayout.value) {
    return {
      message: 'Next payout will be processed automatically when cron job runs',
      canProcess: true
    }
  } else if (balance < MINIMUM_PAYOUT) {
    const needed = (MINIMUM_PAYOUT - balance).toFixed(2)
    return {
      message: `Need $${needed} more to reach minimum payout threshold`,
      canProcess: false
    }
  } else if (hasPendingPayout.value) {
    return {
      message: 'Payout request pending admin approval',
      canProcess: false
    }
  }
  return null
})

onMounted(async () => {
  loadAuth()
  await loadData()
})

const loadData = async () => {
  try {
    await Promise.all([
      loadEarnings(),
      loadEarningsHistory(),
      loadPayoutRequests()
    ])
  } catch (err) {
    console.error('Failed to load artist data:', err)
  }
}

const loadEarnings = async () => {
  try {
    earnings.value = await fetchEarnings()
  } catch (err) {
    console.error('Failed to load earnings:', err)
  }
}

const loadEarningsHistory = async () => {
  try {
    const data = await fetchEarningsHistory(20, 0)
    earningsHistory.value = data.earnings
  } catch (err) {
    console.error('Failed to load earnings history:', err)
  }
}

const loadPayoutRequests = async () => {
  try {
    const data = await fetchPayoutRequests()
    payoutRequests.value = data.payoutRequests
    pendingPayoutAmount.value = data.pendingAmount
  } catch (err) {
    console.error('Failed to load payout requests:', err)
  }
}

// Manual payout requests removed - now automated via cron job

const formatCurrency = (amount: string) => {
  return `$${parseFloat(amount).toFixed(2)}`
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-500/20 text-yellow-300 border-yellow-500/50'
    case 'approved':
      return 'bg-green-500/20 text-green-300 border-green-500/50'
    case 'rejected':
      return 'bg-red-500/20 text-red-300 border-red-500/50'
    case 'paid':
      return 'bg-blue-500/20 text-blue-300 border-blue-500/50'
    default:
      return 'bg-gray-500/20 text-gray-300 border-gray-500/50'
  }
}
</script>

<template>
  <div class="artist-dashboard">
    <div class="page-header">
      <h1 class="page-title">Artist Dashboard</h1>
      <p class="page-description">View your earnings and manage payouts</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-900/20 border border-red-700/50 rounded-xl p-4 mb-6">
      <p class="text-red-300">{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !earnings" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
      <p class="mt-4 text-gray-400">Loading earnings...</p>
    </div>

    <!-- Earnings Overview -->
    <div v-if="earnings" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-gradient-to-br from-green-600/20 to-green-800/20 border border-green-500/30 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-2">
          <Icon name="heroicons:banknotes" class="w-6 h-6 text-green-400" />
          <h3 class="text-lg font-semibold text-white">Total Earnings</h3>
        </div>
        <p class="text-3xl font-bold text-white">{{ formatCurrency(earnings.totalEarnings) }}</p>
      </div>

      <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/20 border border-blue-500/30 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-2">
          <Icon name="heroicons:chart-bar" class="w-6 h-6 text-blue-400" />
          <h3 class="text-lg font-semibold text-white">Total Sales</h3>
        </div>
        <p class="text-3xl font-bold text-white">{{ earnings.totalSales }}</p>
      </div>

      <div class="bg-gradient-to-br from-purple-600/20 to-purple-800/20 border border-purple-500/30 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-2">
          <Icon name="heroicons:wallet" class="w-6 h-6 text-purple-400" />
          <h3 class="text-lg font-semibold text-white">Available Balance</h3>
        </div>
        <p class="text-3xl font-bold text-white">{{ formatCurrency(availableBalance) }}</p>
        <div v-if="nextPayoutInfo" class="mt-4 p-3 bg-purple-500/20 border border-purple-500/30 rounded-lg">
          <p class="text-sm text-purple-200 text-center">
            <Icon name="heroicons:clock" class="w-4 h-4 inline mr-1" />
            {{ nextPayoutInfo.message }}
          </p>
        </div>
        <p v-else class="mt-2 text-sm text-purple-300 text-center">
          Minimum: ${{ MINIMUM_PAYOUT.toFixed(2) }}
        </p>
      </div>
    </div>

    <!-- Design Sets Earnings -->
    <div v-if="earnings && earnings.designSets.length > 0" class="mb-8">
      <h2 class="text-2xl font-bold text-white mb-4">Earnings by Design Set</h2>
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl overflow-hidden">
        <table class="w-full">
          <thead class="bg-white/5">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-white">Design Set</th>
              <th class="px-6 py-3 text-right text-sm font-semibold text-white">Sales</th>
              <th class="px-6 py-3 text-right text-sm font-semibold text-white">Earnings</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">
            <tr v-for="designSet in earnings.designSets" :key="designSet.designSetId" class="hover:bg-white/5">
              <td class="px-6 py-4 text-white">{{ designSet.designName }}</td>
              <td class="px-6 py-4 text-right text-gray-300">{{ designSet.purchaseCount }}</td>
              <td class="px-6 py-4 text-right font-semibold text-white">{{ formatCurrency(designSet.totalEarnings) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Earnings -->
    <div v-if="earningsHistory.length > 0" class="mb-8">
      <h2 class="text-2xl font-bold text-white mb-4">Recent Earnings</h2>
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl overflow-hidden">
        <div class="divide-y divide-white/10">
          <div
            v-for="item in earningsHistory"
            :key="item.id"
            class="px-6 py-4 hover:bg-white/5 flex items-center justify-between"
          >
            <div>
              <p class="text-white font-semibold">{{ item.designSetName || 'Unknown Design Set' }}</p>
              <p class="text-sm text-gray-400">{{ formatDate(item.earnedAt) }}</p>
            </div>
            <div class="text-right">
              <p class="text-white font-semibold">{{ formatCurrency(item.amount) }}</p>
              <p class="text-xs text-gray-400">from {{ formatCurrency(item.purchasePrice) }} sale</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payout Requests -->
    <div v-if="payoutRequests.length > 0" class="mb-8">
      <h2 class="text-2xl font-bold text-white mb-4">Payout History</h2>
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl overflow-hidden">
        <div class="divide-y divide-white/10">
          <div
            v-for="request in payoutRequests"
            :key="request.id"
            class="px-6 py-4 hover:bg-white/5"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="flex items-center gap-3 mb-1">
                  <span class="text-white font-semibold">{{ formatCurrency(request.amount) }}</span>
                  <span :class="['px-2 py-1 rounded-full text-xs border', getStatusBadgeClass(request.status)]">
                    {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                  </span>
                  <span v-if="request.isAutomated" class="px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-300 border border-blue-500/50">
                    Automated
                  </span>
                </div>
                <p class="text-sm text-gray-400">
                  {{ request.isAutomated ? 'Auto-generated' : 'Manual request' }} • {{ formatDate(request.requestedAt) }}
                  <span v-if="request.processedAt"> • Processed: {{ formatDate(request.processedAt) }}</span>
                </p>
                <p v-if="request.adminNotes" class="text-sm text-gray-300 mt-2 italic">
                  {{ request.adminNotes }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty States -->
    <div v-if="earnings && earnings.designSets.length === 0" class="text-center py-12 bg-white/5 rounded-xl border border-white/10">
      <Icon name="heroicons:chart-bar" class="w-16 h-16 text-gray-500 mx-auto mb-4" />
      <h3 class="text-xl font-semibold text-white mb-2">No Earnings Yet</h3>
      <p class="text-gray-400">Start earning by creating design sets and getting them approved!</p>
    </div>

  </div>
</template>

<style scoped>
.artist-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}
</style>
