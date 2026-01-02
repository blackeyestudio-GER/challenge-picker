<template>
  <div class="min-h-screen" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
    <AppHeader />
    
    <div class="max-w-4xl mx-auto px-4 py-12">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-purple-500 mx-auto mb-4"></div>
        <p class="text-white/60">Loading challenge...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-900/20 border border-red-700 rounded-lg p-8 text-center">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h2 class="text-2xl font-bold text-white mb-2">Challenge Not Found</h2>
        <p class="text-gray-300 mb-6">{{ error }}</p>
        <NuxtLink to="/" class="inline-block px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
          Go to Homepage
        </NuxtLink>
      </div>

      <!-- Challenge Details -->
      <div v-else-if="challengeData" class="space-y-6">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center gap-3 mb-4">
            <svg class="w-12 h-12 text-yellow-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <h1 class="text-4xl font-bold text-white">You've Been Challenged!</h1>
            <svg class="w-12 h-12 text-yellow-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <p class="text-xl text-gray-300">
            <span class="text-purple-400 font-semibold">{{ challengeData.hostUsername }}</span> challenges you to complete this playthrough!
          </p>
        </div>

        <!-- Challenge Card -->
        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-8">
          <div class="flex items-start gap-6">
            <!-- Game Image -->
            <div class="w-32 h-32 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700 shadow-lg">
              <img
                v-if="challengeData.game.imageBase64"
                :src="`data:image/jpeg;base64,${challengeData.game.imageBase64}`"
                :alt="challengeData.game.name"
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Details -->
            <div class="flex-1">
              <h2 class="text-3xl font-bold text-white mb-2">{{ challengeData.game.name }}</h2>
              <h3 class="text-xl text-purple-400 mb-4">{{ challengeData.ruleset.name }}</h3>
              
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-black/30 rounded-lg p-3">
                  <p class="text-gray-400 text-sm mb-1">Difficulty</p>
                  <p class="text-white font-semibold">{{ challengeData.ruleset.difficulty }}</p>
                </div>
                <div class="bg-black/30 rounded-lg p-3">
                  <p class="text-gray-400 text-sm mb-1">Max Active Rules</p>
                  <p class="text-white font-semibold">{{ challengeData.maxConcurrentRules }}</p>
                </div>
              </div>

              <p class="text-gray-300 text-sm">
                {{ challengeData.ruleset.description || 'Accept this challenge and compete with the same ruleset!' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="!user" class="bg-gradient-to-r from-purple-900/50 to-pink-900/50 rounded-xl border border-purple-500/30 p-6 text-center">
          <p class="text-white text-lg mb-4">Sign in or create an account to accept this challenge!</p>
          <div class="flex gap-4 justify-center">
            <NuxtLink to="/login" class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-medium">
              Sign In
            </NuxtLink>
            <NuxtLink to="/register" class="px-8 py-3 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition font-medium">
              Create Account
            </NuxtLink>
          </div>
        </div>

        <div v-else class="text-center">
          <button
            @click="acceptChallenge"
            :disabled="accepting || hasActivePlaythrough"
            class="px-12 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-lg rounded-lg transition font-bold shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="accepting">Accepting Challenge...</span>
            <span v-else-if="hasActivePlaythrough">You already have an active playthrough</span>
            <span v-else>🔥 Accept Challenge 🔥</span>
          </button>

          <p v-if="hasActivePlaythrough" class="text-gray-400 text-sm mt-3">
            Complete or stop your current playthrough first
          </p>

          <div v-if="acceptError" class="mt-4 p-4 bg-red-900/50 border border-red-700 rounded-lg">
            <p class="text-red-200">{{ acceptError }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

const route = useRoute()
const router = useRouter()
const { user } = useAuth()
const { fetchMyPlayScreen } = usePlaythrough()

const playthroughUuid = computed(() => route.params.uuid as string)

const challengeData = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const accepting = ref(false)
const acceptError = ref<string | null>(null)
const hasActivePlaythrough = ref(false)

// Fetch challenge details
const fetchChallengeDetails = async () => {
  loading.value = true
  error.value = null

  try {
    const config = useRuntimeConfig()
    const response = await $fetch<{
      success: boolean
      data?: any
      error?: { code: string; message: string }
    }>(`${config.public.apiBase}/challenges/${playthroughUuid.value}/details`)

    if (response.success && response.data) {
      challengeData.value = response.data
    } else {
      error.value = response.error?.message || 'Challenge not found'
    }
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to load challenge'
  } finally {
    loading.value = false
  }
}

// Check if user has active playthrough
const checkActivePlaythrough = async () => {
  if (!user.value) return

  try {
    await fetchMyPlayScreen()
    hasActivePlaythrough.value = true
  } catch (err) {
    hasActivePlaythrough.value = false
  }
}

// Accept challenge
const acceptChallenge = async () => {
  if (!user.value) {
    router.push('/login')
    return
  }

  accepting.value = true
  acceptError.value = null

  try {
    const config = useRuntimeConfig()
    const { getAuthHeader } = useAuth()
    
    const response = await $fetch<{
      success: boolean
      data?: { playthroughUuid: string }
      error?: { code: string; message: string }
    }>(`${config.public.apiBase}/challenges/${playthroughUuid.value}/accept`, {
      method: 'POST',
      headers: getAuthHeader()
    })

    if (response.success && response.data) {
      // Redirect to new playthrough
      router.push(`/play/${response.data.playthroughUuid}`)
    } else {
      acceptError.value = response.error?.message || 'Failed to accept challenge'
    }
  } catch (err: any) {
    acceptError.value = err.data?.error?.message || 'Failed to accept challenge'
  } finally {
    accepting.value = false
  }
}

onMounted(async () => {
  await fetchChallengeDetails()
  if (user.value) {
    await checkActivePlaythrough()
  }
})

// Watch for user login
watch(user, async (newUser) => {
  if (newUser) {
    await checkActivePlaythrough()
  }
})
</script>

