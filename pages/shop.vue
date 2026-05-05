<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useShop, type DesignSetShopItem } from '~/composables/useShop'
import { useAuth } from '~/composables/useAuth'
import { extractErrorMessage } from '~/utils/errorHandler'

definePageMeta({
  middleware: 'auth'
})

const { fetchDesignSets, createCheckoutSession, checkShopStatus, loading } = useShop()
const { isAuthenticated } = useAuth()
const { notifyApiError } = useNotify()
const designSets = ref<DesignSetShopItem[]>([])
const checkoutLoading = ref(false)
const shopEnabled = ref(true)
const shopMessage = ref('')
const errorMessage = ref('')

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
  } catch (err: unknown) {
    errorMessage.value = extractErrorMessage(err, 'Failed to load design sets')
    notifyApiError(err, 'Failed to load design sets')
  }
}

const buyDesignSet = async (designSetId: number) => {
  checkoutLoading.value = true
  try {
    const { checkout_url } = await createCheckoutSession([designSetId])
    // Redirect to Stripe Checkout
    window.location.href = checkout_url
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to start checkout')
    checkoutLoading.value = false
  }
}
</script>

<template>
  <div class="shop-page">
      <!-- Header -->
      <div class="shop-page__header">
        <div class="shop-page__header-content">
          <div>
            <h1 class="shop-page__title">
              Card Design Shop
            </h1>
            <p class="shop-page__description">
              Customize your challenge experience with premium Tarot card designs
            </p>
          </div>
          <NuxtLink
            to="/shop/purchases"
            class="shop-page__purchases-link"
          >
            <Icon name="heroicons:receipt-percent" class="w-5 h-5" />
            My Purchases
          </NuxtLink>
        </div>
      </div>

      <!-- Shop Disabled Message -->
      <div v-if="!shopEnabled" class="shop-page__disabled">
        <div class="shop-page__disabled-card">
          <Icon name="heroicons:exclamation-triangle" class="shop-page__disabled-icon" />
          <h2 class="shop-page__disabled-title">Shop Temporarily Unavailable</h2>
          <p class="shop-page__disabled-message">
            {{ shopMessage || 'The shop is currently undergoing maintenance. Please check back soon!' }}
          </p>
          <NuxtLink
            to="/dashboard"
            class="shop-page__disabled-button"
          >
            <Icon name="heroicons:arrow-left" class="w-5 h-5" />
            Back to Dashboard
          </NuxtLink>
        </div>
      </div>

      <!-- Loading State -->
      <LoadingState v-else-if="loading && designSets.length === 0" message="Loading design sets..." />

      <ErrorState v-else-if="errorMessage" :message="errorMessage" />

      <!-- Checkout Loading Overlay -->
      <div v-if="checkoutLoading" class="shop-page__checkout-overlay">
        <div class="shop-page__checkout-content">
          <div class="shop-page__checkout-spinner"/>
          <p class="shop-page__checkout-text">Redirecting to checkout...</p>
        </div>
      </div>

      <div v-else-if="shopEnabled">
        <!-- Free Design Sets -->
        <div v-if="freeDesignSets.length > 0" class="shop-page__section">
          <h2 class="section-title">
            <Icon name="heroicons:gift" class="shop-page__section-icon shop-page__section-icon--green" />
            Free Design Sets
          </h2>
          <div class="shop-page__grid">
            <DesignSetCard
              v-for="designSet in freeDesignSets"
              :key="designSet.id"
              :design-set="designSet"
              @buy="buyDesignSet"
            />
          </div>
        </div>

        <!-- Premium Design Sets -->
        <div v-if="premiumDesignSets.length > 0" class="shop-page__section">
          <h2 class="section-title">
            <Icon name="heroicons:sparkles" class="shop-page__section-icon shop-page__section-icon--yellow" />
            Premium Design Sets
          </h2>
          <div class="shop-page__grid">
            <DesignSetCard
              v-for="designSet in premiumDesignSets"
              :key="designSet.id"
              :design-set="designSet"
              @buy="buyDesignSet"
            />
          </div>
        </div>

        <!-- Empty State -->
        <EmptyState
          v-if="designSets.length === 0 && !loading"
          icon="heroicons:shopping-bag"
          title="No design sets available"
          message="The shop is active, but there are no design sets available yet."
        />
      </div>
  </div>
</template>
