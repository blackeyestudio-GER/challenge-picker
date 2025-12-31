<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdmin } from '~/composables/useAdmin'
import { Icon } from '#components'
import AdminHeader from '~/components/admin/AdminHeader.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchShopSettings, updateShopSettings, loading } = useAdmin()
const shopEnabled = ref(true)
const updating = ref(false)

onMounted(async () => {
  await loadSettings()
})

const loadSettings = async () => {
  try {
    const settings = await fetchShopSettings()
    shopEnabled.value = settings.shopEnabled
  } catch (err) {
    console.error('Failed to load shop settings:', err)
  }
}

const toggleShop = async () => {
  updating.value = true
  try {
    const newStatus = !shopEnabled.value
    await updateShopSettings(newStatus)
    shopEnabled.value = newStatus
  } catch (err) {
    console.error('Failed to toggle shop:', err)
    alert('Failed to update shop settings')
  } finally {
    updating.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <AdminHeader
      title="Shop Management"
      description="Manage shop settings and features"
    />

    <!-- Loading State -->
    <div v-if="loading && !updating" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan"></div>
      <p class="text-white mt-4">Loading settings...</p>
    </div>

    <!-- Settings Cards -->
    <div v-else class="space-y-6">
      <!-- Shop Status Toggle -->
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <Icon name="heroicons:shopping-bag" class="w-6 h-6 text-cyan" />
              <h2 class="text-2xl font-bold text-white">Shop Status</h2>
            </div>
            <p class="text-gray-400 mb-4">
              {{ shopEnabled 
                ? 'Shop is currently OPEN. Users can browse and purchase designs.' 
                : 'Shop is currently CLOSED. Users will see a "temporarily unavailable" message.' 
              }}
            </p>
            
            <!-- Current Status Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg mb-6"
                 :class="shopEnabled ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'">
              <div class="w-2 h-2 rounded-full animate-pulse"
                   :class="shopEnabled ? 'bg-green-400' : 'bg-red-400'"></div>
              <span class="font-semibold">
                {{ shopEnabled ? 'OPEN' : 'CLOSED' }}
              </span>
            </div>
          </div>

          <!-- Toggle Button -->
          <button
            @click="toggleShop"
            :disabled="updating"
            class="px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            :class="shopEnabled 
              ? 'bg-red-600 hover:bg-red-700 text-white' 
              : 'bg-green-600 hover:bg-green-700 text-white'"
          >
            <Icon 
              :name="updating ? 'heroicons:arrow-path' : (shopEnabled ? 'heroicons:x-circle' : 'heroicons:check-circle')" 
              class="w-5 h-5"
              :class="{'animate-spin': updating}"
            />
            {{ updating ? 'Updating...' : (shopEnabled ? 'Disable Shop' : 'Enable Shop') }}
          </button>
        </div>

        <!-- Warning Message when Disabled -->
        <div v-if="!shopEnabled" class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg flex items-start gap-3">
          <Icon name="heroicons:exclamation-triangle" class="w-6 h-6 text-yellow-500 flex-shrink-0 mt-0.5" />
          <div>
            <h3 class="font-semibold text-yellow-500 mb-1">Shop is Disabled</h3>
            <p class="text-sm text-gray-300">
              Users cannot access the shop or make purchases. The shop page will display a maintenance message.
              Enable the shop when you're ready to accept purchases again.
            </p>
          </div>
        </div>
      </div>

      <!-- Use Cases Card -->
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
        <div class="flex items-center gap-3 mb-4">
          <Icon name="heroicons:information-circle" class="w-6 h-6 text-cyan" />
          <h2 class="text-xl font-bold text-white">When to Disable the Shop</h2>
        </div>
        
        <ul class="space-y-3 text-gray-300">
          <li class="flex items-start gap-3">
            <Icon name="heroicons:bug-ant" class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" />
            <span><strong class="text-white">Bug Found:</strong> Discovered a critical bug in the checkout process</span>
          </li>
          <li class="flex items-start gap-3">
            <Icon name="heroicons:credit-card" class="w-5 h-5 text-orange-400 flex-shrink-0 mt-0.5" />
            <span><strong class="text-white">Payment Issues:</strong> Stripe webhook not working or payment processing errors</span>
          </li>
          <li class="flex items-start gap-3">
            <Icon name="heroicons:scale" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" />
            <span><strong class="text-white">Tax Compliance:</strong> Need to configure VAT/tax settings correctly</span>
          </li>
          <li class="flex items-start gap-3">
            <Icon name="heroicons:wrench-screwdriver" class="w-5 h-5 text-cyan-400 flex-shrink-0 mt-0.5" />
            <span><strong class="text-white">Maintenance:</strong> Updating prices, adding new designs, or making changes</span>
          </li>
          <li class="flex items-start gap-3">
            <Icon name="heroicons:beaker" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" />
            <span><strong class="text-white">Testing:</strong> Need to test shop features before going live</span>
          </li>
        </ul>
      </div>

      <!-- Quick Stats Card -->
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
        <div class="flex items-center gap-3 mb-4">
          <Icon name="heroicons:chart-bar" class="w-6 h-6 text-cyan" />
          <h2 class="text-xl font-bold text-white">Quick Links</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <NuxtLink
            to="/shop"
            class="p-4 bg-gray-700/50 hover:bg-gray-700 border border-gray-600 hover:border-cyan rounded-lg transition-all flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:shopping-bag" class="w-5 h-5 text-cyan" />
              <span class="text-white font-semibold">View Shop</span>
            </div>
            <Icon name="heroicons:arrow-right" class="w-5 h-5 text-gray-400 group-hover:text-cyan transition-colors" />
          </NuxtLink>

          <NuxtLink
            to="/admin/designs"
            class="p-4 bg-gray-700/50 hover:bg-gray-700 border border-gray-600 hover:border-cyan rounded-lg transition-all flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:paint-brush" class="w-5 h-5 text-magenta" />
              <span class="text-white font-semibold">Manage Designs</span>
            </div>
            <Icon name="heroicons:arrow-right" class="w-5 h-5 text-gray-400 group-hover:text-cyan transition-colors" />
          </NuxtLink>

          <a
            href="https://dashboard.stripe.com"
            target="_blank"
            class="p-4 bg-gray-700/50 hover:bg-gray-700 border border-gray-600 hover:border-cyan rounded-lg transition-all flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:credit-card" class="w-5 h-5 text-cyan" />
              <span class="text-white font-semibold">Stripe Dashboard</span>
            </div>
            <Icon name="heroicons:arrow-top-right-on-square" class="w-5 h-5 text-gray-400 group-hover:text-cyan transition-colors" />
          </a>

          <NuxtLink
            to="/shop/purchases"
            class="p-4 bg-gray-700/50 hover:bg-gray-700 border border-gray-600 hover:border-cyan rounded-lg transition-all flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <Icon name="heroicons:receipt-percent" class="w-5 h-5 text-magenta" />
              <span class="text-white font-semibold">View Purchases</span>
            </div>
            <Icon name="heroicons:arrow-right" class="w-5 h-5 text-gray-400 group-hover:text-cyan transition-colors" />
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

