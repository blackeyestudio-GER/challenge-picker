<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'
import { Icon } from '#components'
import { extractErrorMessage } from '~/utils/errorHandler'

definePageMeta({
  middleware: 'auth'
})

const { user, loadAuth, getAuthHeader } = useAuth()
const router = useRouter()
const config = useRuntimeConfig()
const error = ref<string | null>(null)
const portfolioUrl = ref('')
const submitting = ref(false)

onMounted(() => {
  loadAuth()
  if (user.value?.isArtist) {
    router.push('/artist/dashboard')
  }
})

const submitApplication = async () => {
  submitting.value = true
  error.value = null

  try {
    await $fetch(`${config.public.apiBase}/artist/apply`, {
      method: 'POST',
      headers: getAuthHeader(),
      body: JSON.stringify({
        portfolioUrl: portfolioUrl.value || null
      })
    })

    // Reload auth to get updated user with isArtist flag
    await loadAuth()
    router.push('/artist/dashboard')
  } catch (err: unknown) {
    error.value = extractErrorMessage(err, 'Failed to submit application')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="artist-apply-page">
    <div class="page-header">
      <h1 class="page-title">Become an Artist</h1>
      <p class="page-description">Join our artist program and earn from your design sets</p>
    </div>

    <div class="max-w-2xl mx-auto">
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 md:p-8">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-white mb-4">How It Works</h2>
          <ul class="space-y-3 text-gray-300">
            <li class="flex items-start gap-3">
              <Icon name="heroicons:check-circle" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" />
              <span>Create premium design sets for the shop</span>
            </li>
            <li class="flex items-start gap-3">
              <Icon name="heroicons:check-circle" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" />
              <span>Earn a commission on every sale</span>
            </li>
            <li class="flex items-start gap-3">
              <Icon name="heroicons:check-circle" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" />
              <span>Request payouts when you reach the minimum threshold</span>
            </li>
            <li class="flex items-start gap-3">
              <Icon name="heroicons:check-circle" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" />
              <span>Track your earnings and sales in real-time</span>
            </li>
          </ul>
        </div>

        <div v-if="error" class="mb-6 p-4 bg-red-900/20 border border-red-700/50 rounded-lg">
          <p class="text-red-300">{{ error }}</p>
        </div>

        <form class="space-y-6" @submit.prevent="submitApplication">
          <div>
            <label class="block text-sm font-semibold text-white mb-2">
              Portfolio URL (Optional)
            </label>
            <input
              v-model="portfolioUrl"
              type="url"
              placeholder="https://your-portfolio.com"
              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500"
            >
            <p class="text-xs text-gray-400 mt-1">Share your portfolio to help us review your work</p>
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 text-white rounded-lg font-semibold transition-all"
          >
            {{ submitting ? 'Submitting...' : 'Apply to Become an Artist' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.artist-apply-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}
</style>
