<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const sessionId = route.query.session_id as string

const goToShop = () => {
  router.push('/shop')
}

onMounted(() => {
  // Could verify purchase with backend here if needed
  console.log('Purchase completed, session ID:', sessionId)
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-black flex items-center justify-center px-4">
    <div class="max-w-2xl mx-auto text-center">
      <!-- Success Icon -->
      <div class="mb-8 inline-block">
        <div class="relative">
          <div class="absolute inset-0 bg-green-500/20 rounded-full blur-2xl"></div>
          <Icon name="heroicons:check-circle" class="relative w-32 h-32 text-green-400" />
        </div>
      </div>

      <!-- Success Message -->
      <h1 class="text-5xl font-bold text-white mb-4">
        Purchase Successful! 🎉
      </h1>
      <p class="text-xl text-gray-300 mb-8">
        Your card designs have been unlocked and are ready to use!
      </p>

      <!-- Info Box -->
      <div class="bg-cyan/10 border border-cyan/30 rounded-xl p-6 mb-8 text-left">
        <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
          <Icon name="heroicons:information-circle" class="w-5 h-5 text-cyan" />
          What's Next?
        </h3>
        <ul class="space-y-2 text-gray-300">
          <li class="flex items-start gap-2">
            <Icon name="heroicons:check" class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" />
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
        <button
          @click="goToShop"
          class="px-8 py-4 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg hover:shadow-xl transition-all"
        >
          Browse More Designs
        </button>
        <NuxtLink
          to="/"
          class="px-8 py-4 bg-gray-700 text-white font-bold rounded-lg hover:bg-gray-600 transition-all inline-block"
        >
          Go to Dashboard
        </NuxtLink>
      </div>

      <!-- Support -->
      <p class="mt-8 text-sm text-gray-500">
        Having issues? Contact support at 
        <a href="mailto:support@challenge-picker.com" class="text-cyan hover:underline">
          support@challenge-picker.com
        </a>
      </p>
    </div>
  </div>
</template>

