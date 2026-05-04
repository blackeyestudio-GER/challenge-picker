<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdmin, type AdminPayoutRequest } from '~/composables/useAdmin'
import AdminHeader from '~/components/admin/AdminHeader.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchPayoutRequests, approvePayoutRequest, rejectPayoutRequest } = useAdmin()
const { success, warning, notifyApiError } = useNotify()
const loading = ref(true)
const payoutRequests = ref<AdminPayoutRequest[]>([])
const error = ref<string | null>(null)
const processingId = ref<number | null>(null)
const adminNotes = ref<Record<number, string>>({})

const loadPayoutRequests = async () => {
  loading.value = true
  error.value = null
  try {
    payoutRequests.value = await fetchPayoutRequests()
  } catch {
    error.value = 'Failed to load payout requests'
  } finally {
    loading.value = false
  }
}

const approvePayout = async (id: number) => {
  if (confirm('Approve this payout request?')) {
    processingId.value = id
    try {
      await approvePayoutRequest(id, adminNotes.value[id] || null)
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
      await rejectPayoutRequest(id, notes)
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
    <AdminHeader
      title="Payout Requests"
      description="Review, approve, and reject creator payout requests."
      back-to="/admin"
      back-label="Back to Admin Dashboard"
    />

    <LoadingState v-if="loading" message="Loading payout requests..." />

    <ErrorState v-else-if="error" :message="error" />

    <!-- Payout Requests Table -->
    <div v-else-if="payoutRequests.length > 0" class="admin-payouts-table">
      <table class="admin-payouts-table__table">
        <thead class="admin-payouts-table__head">
          <tr>
            <th class="admin-payouts-table__heading admin-payouts-table__heading--left">Artist</th>
            <th class="admin-payouts-table__heading admin-payouts-table__heading--right">Amount</th>
            <th class="admin-payouts-table__heading admin-payouts-table__heading--left">Type</th>
            <th class="admin-payouts-table__heading admin-payouts-table__heading--left">Requested</th>
            <th class="admin-payouts-table__heading admin-payouts-table__heading--left">Notes</th>
            <th class="admin-payouts-table__heading admin-payouts-table__heading--center">Actions</th>
          </tr>
        </thead>
        <tbody class="admin-payouts-table__body">
          <tr v-for="request in payoutRequests" :key="request.id" class="admin-payouts-table__row">
            <td class="admin-payouts-table__cell">
              <div>
                <p class="admin-payouts-table__artist">{{ request.designerUsername || 'Unknown designer' }}</p>
                <p class="admin-payouts-table__meta">{{ request.designerEmail || 'No email available' }}</p>
              </div>
            </td>
            <td class="admin-payouts-table__cell admin-payouts-table__cell--right">
              <span class="admin-payouts-table__amount">{{ formatCurrency(request.amount) }}</span>
            </td>
            <td class="admin-payouts-table__cell">
              <span v-if="request.isAutomated" class="admin-badge admin-badge--info">
                Automated
              </span>
              <span v-else class="admin-badge admin-badge--accent">
                Manual
              </span>
            </td>
            <td class="admin-payouts-table__cell admin-payouts-table__cell--muted">
              {{ request.requestedAt ? formatDate(request.requestedAt) : 'Unknown' }}
            </td>
            <td class="admin-payouts-table__cell">
              <textarea
                v-model="adminNotes[request.id]"
                placeholder="Admin notes..."
                class="admin-payouts-table__notes"
                rows="2"
              />
            </td>
            <td class="admin-payouts-table__cell">
              <div class="admin-payouts-table__actions">
                <button
                  :disabled="processingId === request.id"
                  class="btn btn-success btn-sm"
                  @click="approvePayout(request.id)"
                >
                  Approve
                </button>
                <button
                  :disabled="processingId === request.id"
                  class="btn btn-danger btn-sm"
                  @click="rejectPayout(request.id)"
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
    <EmptyState
      v-else
      icon="heroicons:check-circle"
      title="No pending payout requests"
      message="All payout requests have been processed."
    />
  </div>
</template>

<style scoped>
.admin-payouts-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.admin-payouts-table {
  border: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
  border-radius: 1rem;
  overflow: hidden;
  background: color-mix(in srgb, var(--color-surface, #111827) 88%, transparent);
  backdrop-filter: blur(12px);
}

.admin-payouts-table__table {
  width: 100%;
  border-collapse: collapse;
}

.admin-payouts-table__head {
  background: color-mix(in srgb, var(--color-surface-elevated, #1f2937) 72%, transparent);
}

.admin-payouts-table__heading,
.admin-payouts-table__cell {
  padding: 1rem 1.5rem;
}

.admin-payouts-table__heading {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-text, #fff);
}

.admin-payouts-table__heading--left {
  text-align: left;
}

.admin-payouts-table__heading--right,
.admin-payouts-table__cell--right {
  text-align: right;
}

.admin-payouts-table__heading--center {
  text-align: center;
}

.admin-payouts-table__body {
  border-top: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
}

.admin-payouts-table__row {
  border-top: 1px solid var(--color-border-subtle, rgba(255, 255, 255, 0.08));
}

.admin-payouts-table__row:hover {
  background: color-mix(in srgb, var(--color-surface-elevated, #1f2937) 40%, transparent);
}

.admin-payouts-table__artist,
.admin-payouts-table__amount {
  color: var(--color-text, #fff);
  font-weight: 700;
}

.admin-payouts-table__meta,
.admin-payouts-table__cell--muted {
  color: var(--color-text-muted, #9ca3af);
}

.admin-payouts-table__notes {
  width: 100%;
  min-height: 4.5rem;
  border: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
  border-radius: 0.75rem;
  background: color-mix(in srgb, var(--color-surface-elevated, #1f2937) 84%, transparent);
  color: var(--color-text, #fff);
  padding: 0.75rem 0.875rem;
  resize: vertical;
}

.admin-payouts-table__notes:focus {
  outline: 2px solid var(--color-accent, #22d3ee);
  outline-offset: 1px;
}

.admin-payouts-table__actions {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
}
</style>
