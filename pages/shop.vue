<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useShop, type DesignSetShopItem } from '~/composables/useShop'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  middleware: 'auth'
})

const { fetchDesignSets, createCheckoutSession, checkShopStatus, loading } = useShop()
const { isAuthenticated } = useAuth()
const designSets = ref<DesignSetShopItem[]>([])
const checkoutLoading = ref(false)
const shopEnabled = ref(true)
const shopMessage = ref('')

const freeDesignSets = computed(() => designSets.value.filter(ds => !ds.is_premium))
const premiumDesignSets = computed(() => designSets.value.filter(ds => ds.is_premium))

onMounted(async () => {
  if (isAuthenticated.value) {
    // Check shop status first
    const status = await checkShopStatus()
    shopEnabled.value = status.enabled
    shopMessage.value = status.message
    
    // Only load design sets if shop is enabled
    if (shopEnabled.value) {
      await loadDesignSets()
    }
  }
})

const loadDesignSets = async () => {
  try {
    designSets.value = await fetchDesignSets()
  } catch (err) {
    console.error('Failed to load design sets:', err)
  }
}

const buyDesignSet = async (designSetId: number) => {
  checkoutLoading.value = true
  try {
    const { checkout_url } = await createCheckoutSession([designSetId])
    // Redirect to Stripe Checkout
    window.location.href = checkout_url
  } catch (err: any) {
    alert(err.data?.error || 'Failed to start checkout')
    checkoutLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-black py-8 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-4">
              Card Design Shop
            </h1>
            <p class="text-gray-300 text-lg">
              Customize your challenge experience with premium Tarot card designs
            </p>
          </div>
          <NuxtLink
            to="/shop/purchases"
            class="px-6 py-3 bg-gray-800 border border-gray-700 text-white font-semibold rounded-lg hover:bg-gray-700 hover:border-cyan transition-all flex items-center gap-2"
          >
            <Icon name="heroicons:receipt-percent" class="w-5 h-5" />
            My Purchases
          </NuxtLink>
        </div>
      </div>

      <!-- Shop Disabled Message -->
      <div v-if="!shopEnabled" class="max-w-2xl mx-auto text-center py-12">
        <div class="bg-yellow-500/10 border-2 border-yellow-500/30 rounded-2xl p-8 mb-6">
          <Icon name="heroicons:exclamation-triangle" class="w-16 h-16 text-yellow-500 mx-auto mb-4" />
          <h2 class="text-3xl font-bold text-white mb-4">Shop Temporarily Unavailable</h2>
          <p class="text-gray-300 text-lg mb-6">
            {{ shopMessage || 'The shop is currently undergoing maintenance. Please check back soon!' }}
          </p>
          <NuxtLink
            to="/dashboard"
            class="inline-flex items-center gap-2 px-6 py-3 bg-cyan hover:bg-cyan-dark text-white font-bold rounded-lg transition-all"
          >
            <Icon name="heroicons:arrow-left" class="w-5 h-5" />
            Back to Dashboard
          </NuxtLink>
        </div>
      </div>

      <!-- Loading State -->
      <div v-else-if="loading && designSets.length === 0" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
        <p class="text-white mt-4">Loading design sets...</p>
      </div>

      <!-- Checkout Loading Overlay -->
      <div v-if="checkoutLoading" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="text-center">
          <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan"></div>
          <p class="text-white mt-4 text-xl">Redirecting to checkout...</p>
        </div>
      </div>

      <div v-else-if="shopEnabled">
        <!-- Free Design Sets -->
        <div v-if="freeDesignSets.length > 0" class="mb-12">
          <h2 class="text-3xl font-bold text-white mb-6 flex items-center gap-3">
            <Icon name="heroicons:gift" class="w-8 h-8 text-green-400" />
            Free Design Sets
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <DesignSetCard
              v-for="designSet in freeDesignSets"
              :key="designSet.id"
              :design-set="designSet"
              @buy="buyDesignSet"
            />
          </div>
        </div>

        <!-- Premium Design Sets -->
        <div v-if="premiumDesignSets.length > 0">
          <h2 class="text-3xl font-bold text-white mb-6 flex items-center gap-3">
            <Icon name="heroicons:sparkles" class="w-8 h-8 text-yellow-400" />
            Premium Design Sets
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <DesignSetCard
              v-for="designSet in premiumDesignSets"
              :key="designSet.id"
              :design-set="designSet"
              @buy="buyDesignSet"
            />
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="designSets.length === 0 && !loading" class="text-center py-12">
          <Icon name="heroicons:shopping-bag" class="w-24 h-24 mx-auto text-gray-600 mb-4" />
          <p class="text-gray-400 text-lg">No design sets available yet</p>
        </div>
      </div>
    </div>
  </div>
</template>

