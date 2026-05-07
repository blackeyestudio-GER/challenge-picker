<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdmin } from '~/composables/useAdmin'
import { Icon } from '#components'
import AdminHeader from '~/components/admin/AdminHeader.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchShopSettings, updateShopSettings, loading } = useAdmin()
const { success, notifyApiError } = useNotify()
const shopEnabled = ref(true)
const updating = ref(false)
const errorMessage = ref<string | null>(null)

onMounted(async () => {
  await loadSettings()
})

const loadSettings = async () => {
  try {
    errorMessage.value = null
    const settings = await fetchShopSettings()
    shopEnabled.value = settings.shopEnabled
  } catch (err: unknown) {
    errorMessage.value = 'Failed to load shop settings'
    notifyApiError(err, 'Failed to load shop settings')
  }
}

const toggleShop = async () => {
  updating.value = true
  try {
    errorMessage.value = null
    const newStatus = !shopEnabled.value
    await updateShopSettings(newStatus)
    shopEnabled.value = newStatus
    success(newStatus ? 'Shop enabled' : 'Shop disabled')
  } catch (err) {
    errorMessage.value = 'Failed to update shop settings'
    notifyApiError(err, 'Failed to update shop settings')
  } finally {
    updating.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 admin-shop-page">
    <AdminHeader
      title="Shop Management"
      description="Manage shop settings and features"
    />

    <LoadingState v-if="loading && !updating" message="Loading settings..." />

    <ErrorState v-else-if="errorMessage" :message="errorMessage" />

    <!-- Settings Cards -->
    <div v-else class="space-y-6">
      <!-- Shop Status Toggle -->
      <div class="admin-panel p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <span class="admin-icon-orb">
                <Icon name="heroicons:shopping-bag" class="w-6 h-6" />
              </span>
              <h2 class="text-2xl font-bold text-[var(--color-text-primary)]">Shop Status</h2>
            </div>
            <p class="admin-text-muted mb-4">
              {{ shopEnabled 
                ? 'Shop is currently OPEN. Users can browse and purchase designs.' 
                : 'Shop is currently CLOSED. Users will see a "temporarily unavailable" message.' 
              }}
            </p>
            
            <!-- Current Status Badge -->
            <div
              class="inline-flex items-center gap-2 px-4 py-2 rounded-lg mb-6 admin-badge"
              :class="shopEnabled ? 'admin-badge--success' : 'admin-badge--danger'"
            >
              <div
                class="w-2 h-2 rounded-full animate-pulse"
                :class="shopEnabled ? 'bg-[var(--status-active-text)]' : 'bg-[var(--status-failed-text)]'"
              />
              <span class="font-semibold">
                {{ shopEnabled ? 'OPEN' : 'CLOSED' }}
              </span>
            </div>
          </div>

          <button
            :disabled="updating"
            class="px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            :class="shopEnabled 
              ? 'btn btn-danger' 
              : 'btn btn-success'"
            @click="toggleShop"
          >
            <Icon 
              :name="updating ? 'heroicons:arrow-path' : (shopEnabled ? 'heroicons:x-circle' : 'heroicons:check-circle')" 
              class="w-5 h-5"
              :class="{'animate-spin': updating}"
            />
            {{ updating ? 'Updating...' : (shopEnabled ? 'Disable Shop' : 'Enable Shop') }}
          </button>
        </div>

        <div v-if="!shopEnabled" class="mt-6 p-4 admin-panel admin-shop-page__warning flex items-start gap-3">
          <Icon name="heroicons:exclamation-triangle" class="w-6 h-6 flex-shrink-0 mt-0.5 text-[var(--status-pending-text)]" />
          <div>
            <h3 class="font-semibold text-[var(--status-pending-text)] mb-1">Shop is Disabled</h3>
            <p class="text-sm admin-text-muted">
              Users cannot access the shop or make purchases. The shop page will display a maintenance message.
              Enable the shop when you're ready to accept purchases again.
            </p>
          </div>
        </div>
      </div>

      <!-- Use Cases Card -->
      <div class="admin-panel p-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="admin-icon-orb">
            <Icon name="heroicons:information-circle" class="w-6 h-6" />
          </span>
          <h2 class="text-xl font-bold text-[var(--color-text-primary)]">When to Disable the Shop</h2>
        </div>
        
        <ul class="space-y-3">
          <li class="admin-list-item">
            <Icon name="heroicons:bug-ant" class="w-5 h-5 text-[var(--status-failed-text)] flex-shrink-0 mt-0.5" />
            <span><strong>Bug Found:</strong> Discovered a critical bug in the checkout process</span>
          </li>
          <li class="admin-list-item">
            <Icon name="heroicons:credit-card" class="w-5 h-5 text-[var(--status-pending-text)] flex-shrink-0 mt-0.5" />
            <span><strong>Payment Issues:</strong> Stripe webhook not working or payment processing errors</span>
          </li>
          <li class="admin-list-item">
            <Icon name="heroicons:scale" class="w-5 h-5 text-[var(--status-completed-text)] flex-shrink-0 mt-0.5" />
            <span><strong>Tax Compliance:</strong> Need to configure VAT/tax settings correctly</span>
          </li>
          <li class="admin-list-item">
            <Icon name="heroicons:wrench-screwdriver" class="w-5 h-5 text-[var(--color-accent-primary)] flex-shrink-0 mt-0.5" />
            <span><strong>Maintenance:</strong> Updating prices, adding new designs, or making changes</span>
          </li>
          <li class="admin-list-item">
            <Icon name="heroicons:beaker" class="w-5 h-5 text-[var(--status-active-text)] flex-shrink-0 mt-0.5" />
            <span><strong>Testing:</strong> Need to test shop features before going live</span>
          </li>
        </ul>
      </div>

      <div class="admin-panel p-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="admin-icon-orb">
            <Icon name="heroicons:chart-bar" class="w-6 h-6" />
          </span>
          <h2 class="text-xl font-bold text-[var(--color-text-primary)]">Quick Links</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <NuxtLink
            to="/shop"
            class="admin-panel admin-panel--interactive p-4 flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:shopping-bag" class="w-5 h-5 text-[var(--color-accent-primary)]" />
              <span class="text-[var(--color-text-primary)] font-semibold">View Shop</span>
            </div>
            <Icon name="heroicons:arrow-right" class="w-5 h-5 admin-text-subtle group-hover:text-[var(--color-accent-primary)] transition-colors" />
          </NuxtLink>

          <NuxtLink
            to="/admin/designs"
            class="admin-panel admin-panel--interactive p-4 flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:paint-brush" class="w-5 h-5 text-[var(--color-accent-secondary)]" />
              <span class="text-[var(--color-text-primary)] font-semibold">Manage Designs</span>
            </div>
            <Icon name="heroicons:arrow-right" class="w-5 h-5 admin-text-subtle group-hover:text-[var(--color-accent-primary)] transition-colors" />
          </NuxtLink>

          <a
            href="https://dashboard.stripe.com"
            target="_blank"
            class="admin-panel admin-panel--interactive p-4 flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:credit-card" class="w-5 h-5 text-[var(--color-accent-primary)]" />
              <span class="text-[var(--color-text-primary)] font-semibold">Stripe Dashboard</span>
            </div>
            <Icon name="heroicons:arrow-top-right-on-square" class="w-5 h-5 admin-text-subtle group-hover:text-[var(--color-accent-primary)] transition-colors" />
          </a>

          <NuxtLink
            to="/shop/purchases"
            class="admin-panel admin-panel--interactive p-4 flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:receipt-percent" class="w-5 h-5 text-[var(--color-accent-secondary)]" />
              <span class="text-[var(--color-text-primary)] font-semibold">View Purchases</span>
            </div>
            <Icon name="heroicons:arrow-right" class="w-5 h-5 admin-text-subtle group-hover:text-[var(--color-accent-primary)] transition-colors" />
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-shop-page__warning {
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--status-pending-bg) 82%, var(--color-bg-card)), color-mix(in srgb, var(--color-bg-card) 92%, rgba(0, 0, 0, 0.03)));
}
</style>
