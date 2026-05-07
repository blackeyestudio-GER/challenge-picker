<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
void route.query.session_id

const goToShop = () => {
  router.push('/shop')
}

onMounted(() => {
  // Could verify purchase with backend here if needed
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--theme-background-art), var(--color-bg-primary);">
    <div class="max-w-2xl mx-auto text-center">
      <div class="mb-8 inline-block">
        <div class="relative">
          <div class="absolute inset-0 rounded-full blur-2xl" style="background: color-mix(in srgb, var(--status-active-bg) 82%, transparent);" />
          <Icon name="heroicons:check-circle" class="relative w-32 h-32 text-[var(--status-active-text)]" />
        </div>
      </div>

      <h1 class="text-5xl font-bold text-[var(--color-text-primary)] mb-4">
        Purchase Successful! 🎉
      </h1>
      <p class="text-xl text-[var(--color-text-secondary)] mb-8">
        Your card designs have been unlocked and are ready to use!
      </p>

      <div class="admin-panel rounded-xl p-6 mb-8 text-left">
        <h3 class="text-lg font-semibold text-[var(--color-text-primary)] mb-3 flex items-center gap-2">
          <Icon name="heroicons:information-circle" class="w-5 h-5 text-[var(--color-accent-primary)]" />
          What's Next?
        </h3>
        <ul class="space-y-2 text-[var(--color-text-secondary)]">
          <li class="flex items-start gap-2">
            <Icon name="heroicons:check" class="w-5 h-5 text-[var(--status-active-text)] mt-0.5 flex-shrink-0" />
            <span>Your new designs are now available in your account</span>
          </li>
          <li class="flex items-start gap-2">
            <Icon name="heroicons:check" class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" />
            <span>You can switch between designs in your settings (coming soon)</span>
          </li>
          <li class="flex items-start gap-2">
            <Icon name="heroicons:check" class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" />
            <span>All future updates to these designs are free</span>
          </li>
          <li class="flex items-start gap-2">
            <Icon name="heroicons:check" class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" />
            <span>A receipt has been sent to your email</span>
          </li>
        </ul>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button class="btn btn-primary px-8 py-4 font-bold" @click="goToShop">
          Browse More Designs
        </button>
        <NuxtLink
          to="/"
          class="btn btn-secondary px-8 py-4 font-bold inline-block"
        >
          Go to Dashboard
        </NuxtLink>
      </div>

      <p class="mt-8 text-sm text-[var(--color-text-muted)]">
        Having issues? Contact support at 
        <a href="mailto:support@challenge-picker.com" class="admin-link">
          support@challenge-picker.com
        </a>
      </p>
    </div>
  </div>
</template>
