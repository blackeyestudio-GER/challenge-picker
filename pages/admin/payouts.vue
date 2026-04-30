<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'

definePageMeta({
  middleware: 'admin'
})

const { getAuthHeader } = useAuth()
const { success, warning, notifyApiError } = useNotify()
const config = useRuntimeConfig()
const loading = ref(true)
const payoutRequests = ref<any[]>([])
const error = ref<string | null>(null)
const processingId = ref<number | null>(null)
const adminNotes = ref<Record<number, string>>({})

const loadPayoutRequests = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $fetch<{
      success: boolean
      data: {
        payoutRequests: Array<{
          id: number
          designerUuid: string
          designerUsername: string
          designerEmail: string
          amount: string
          currency: string
          status: string
          isAutomated: boolean
          requestedAt: string
        }>
      }
    }>(`${config.public.apiBase}/admin/payout-requests`, {
      headers: getAuthHeader()
    })

    if (response.success) {
      payoutRequests.value = response.data.payoutRequests
    }
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to load payout requests'
  } finally {
    loading.value = false
  }
}

const approvePayout = async (id: number) => {
  if (confirm('Approve this payout request?')) {
    processingId.value = id
    try {
      await $fetch(`${config.public.apiBase}/admin/payout-requests/${id}/approve`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({
          adminNotes: adminNotes.value[id] || null
        })
      })
      await loadPayoutRequests()
      success('Payout approved')
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to approve payout')
    } finally {
      processingId.value = null
    }
  }
}

const rejectPayout = async (id: number) => {
  const notes = adminNotes.value[id]
  if (!notes || notes.trim() === '') {
    warning('Admin notes are required when rejecting a payout request')
    return
  }

  if (confirm('Reject this payout request?')) {
    processingId.value = id
    try {
      await $fetch(`${config.public.apiBase}/admin/payout-requests/${id}/reject`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({
          adminNotes: notes
        })
      })
      await loadPayoutRequests()
      adminNotes.value[id] = ''
      success('Payout request rejected')
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to reject payout')
    } finally {
      processingId.value = null
    }
  }
}

const formatCurrency = (amount: string) => {
  return `$${parseFloat(amount).toFixed(2)}`
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  loadPayoutRequests()
})
</script>

<template>
  <div class="admin-payouts-page">
    <div class="page-header">
      <h1 class="page-title">Payout Requests</h1>
      <p class="page-description">Manage artist payout requests</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-900/20 border border-red-700/50 rounded-xl p-4 mb-6">
      <p class="text-red-300">{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
      <p class="mt-4 text-gray-400">Loading payout requests...</p>
    </div>

    <!-- Payout Requests Table -->
    <div v-else-if="payoutRequests.length > 0" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl overflow-hidden">
      <table class="w-full">
        <thead class="bg-white/5">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-white">Artist</th>
            <th class="px-6 py-3 text-right text-sm font-semibold text-white">Amount</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-white">Type</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-white">Requested</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-white">Notes</th>
            <th class="px-6 py-3 text-center text-sm font-semibold text-white">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/10">
          <tr v-for="request in payoutRequests" :key="request.id" class="hover:bg-white/5">
            <td class="px-6 py-4">
              <div>
                <p class="text-white font-semibold">{{ request.designerUsername }}</p>
                <p class="text-sm text-gray-400">{{ request.designerEmail }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <span class="text-white font-semibold">{{ formatCurrency(request.amount) }}</span>
            </td>
            <td class="px-6 py-4">
              <span v-if="request.isAutomated" class="px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-300 border border-blue-500/50">
                Automated
              </span>
              <span v-else class="px-2 py-1 rounded-full text-xs bg-purple-500/20 text-purple-300 border border-purple-500/50">
                Manual
              </span>
            </td>
            <td class="px-6 py-4 text-gray-300">
              {{ formatDate(request.requestedAt) }}
            </td>
            <td class="px-6 py-4">
              <textarea
                v-model="adminNotes[request.id]"
                placeholder="Admin notes..."
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-purple-500"
                rows="2"
              />
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-center gap-2">
                <button
                  @click="approvePayout(request.id)"
                  :disabled="processingId === request.id"
                  class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg text-sm font-semibold transition-colors"
                >
                  Approve
                </button>
                <button
                  @click="rejectPayout(request.id)"
                  :disabled="processingId === request.id"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-lg text-sm font-semibold transition-colors"
                >
                  Reject
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 bg-white/5 rounded-xl border border-white/10">
      <Icon name="heroicons:check-circle" class="w-16 h-16 text-gray-500 mx-auto mb-4" />
      <h3 class="text-xl font-semibold text-white mb-2">No Pending Requests</h3>
      <p class="text-gray-400">All payout requests have been processed</p>
    </div>
  </div>
</template>

<style scoped>
.admin-payouts-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}
</style>
