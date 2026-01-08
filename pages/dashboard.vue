<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { usePlaythrough } from '~/composables/usePlaythrough'
import { useChallenges } from '~/composables/useChallenges'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { user, isAdmin, loadAuth, getAuthHeader } = useAuth()
const { activePlaythrough, fetchActivePlaythrough } = usePlaythrough()
const { fetchSentChallenges } = useChallenges()
const loading = ref(true)
const browseRunsAvailable = ref(false)
const sentChallenges = ref<any[]>([])
const challengesLoading = ref(false)

const loadSentChallenges = async () => {
  challengesLoading.value = true
  try {
    const data = await fetchSentChallenges()
    sentChallenges.value = data.challenges || []
  } catch (err) {
    console.error('Failed to load sent challenges:', err)
  } finally {
    challengesLoading.value = false
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'accepted':
      return 'bg-green-500/20 text-green-300 border-green-500/50'
    case 'pending':
      return 'bg-yellow-500/20 text-yellow-300 border-yellow-500/50'
    case 'declined':
      return 'bg-red-500/20 text-red-300 border-red-500/50'
    default:
      return 'bg-gray-500/20 text-gray-300 border-gray-500/50'
  }
}

const getAcceptedCount = (challenges: any[]) => {
  return challenges.filter(c => c.status === 'accepted').length
}

onMounted(async () => {
  loadAuth()
  
  try {
    await fetchActivePlaythrough()
    await loadSentChallenges()
    
    // Check if browse runs feature is enabled (feature flag)
    try {
      const featureResponse = await $fetch<{ success: boolean; data: { feature: string; enabled: boolean } }>(
        '/api/features/browse_community_runs',
        {
          headers: getAuthHeader()
        }
      )
      
      const featureEnabled = featureResponse.data.enabled
      
      // Only check data availability if feature is enabled
      if (featureEnabled) {
        const dataResponse = await $fetch<{ success: boolean; data: { available: boolean; count: number } }>(
          '/api/playthrough/browse/availability',
          {
            headers: getAuthHeader()
          }
        )
        browseRunsAvailable.value = dataResponse.data.available
      } else {
        browseRunsAvailable.value = false
      }
    } catch (featureError) {
      // Feature check failed, assume not available
      browseRunsAvailable.value = false
      console.warn('Failed to check browse runs feature:', featureError)
    }
  } catch (err) {
    console.error('Failed to check for active playthrough:', err)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="dashboard-page">
    <!-- Welcome Section -->
    <div class="dashboard-page__welcome">
      <ClientOnly>
        <h2 class="dashboard-page__welcome-title">Welcome back, {{ user?.username || 'Guest' }}! 👋</h2>
        <template #fallback>
          <h2 class="dashboard-page__welcome-title">Welcome back! 👋</h2>
        </template>
      </ClientOnly>
      <p class="dashboard-page__welcome-subtitle">Your streaming dashboard is ready to go!</p>
    </div>

    <!-- Email Verification Warning -->
    <div v-if="user && !user.emailVerified && user.oauthProvider === null" class="bg-yellow-900/20 border border-yellow-700/50 rounded-xl p-4 mb-6">
      <div class="flex items-start gap-3">
        <Icon name="heroicons:exclamation-triangle" class="w-6 h-6 text-yellow-400 flex-shrink-0 mt-0.5" />
        <div class="flex-1 min-w-0">
          <h3 class="text-yellow-400 font-semibold mb-1">Verify Your Email</h3>
          <p class="text-yellow-200/80 text-sm mb-3">Please verify your email address to access all features.</p>
          <NuxtLink
            to="/auth/verify-email"
            class="inline-block px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm font-medium transition"
          >
            Verify Email
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Active Game Alert -->
    <div v-if="!loading && activePlaythrough" class="dashboard-page__active-alert">
      <div class="dashboard-page__active-card">
        <div class="dashboard-page__active-content">
          <div class="dashboard-page__active-left">
            <div class="dashboard-page__active-icon-wrapper">
              <Icon name="heroicons:play" class="dashboard-page__active-icon" />
            </div>
            <div class="dashboard-page__active-text">
              <h3 class="dashboard-page__active-title">Game in Progress!</h3>
              <p class="dashboard-page__active-subtitle">You have an active game session running</p>
            </div>
          </div>
          <NuxtLink
            :to="`/play/${activePlaythrough.uuid}`"
            class="dashboard-page__active-button"
          >
            Resume Game →
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-page__actions grid grid-cols-1 md:grid-cols-2 gap-4">
      <NuxtLink
        v-if="!activePlaythrough"
        to="/playthrough/new"
        class="bg-gradient-to-br from-cyan-muted to-cyan-dark text-white rounded-xl shadow-lg hover:shadow-cyan/30 transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-cyan/20 min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:play-circle" class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold">New Game Session</h3>
          <p class="text-xs md:text-sm text-cyan-100">Start a new playthrough</p>
        </div>
      </NuxtLink>
      
      <div
        v-else
        class="bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-gray-700 opacity-50 cursor-not-allowed min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-gray-700 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:play-circle" class="w-6 h-6 md:w-8 md:h-8 text-gray-500" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold text-gray-400">New Game Session</h3>
          <p class="text-xs md:text-sm text-gray-500">Finish current game first</p>
        </div>
      </div>

      <NuxtLink
        to="/my-runs"
        class="bg-gradient-to-br from-yellow-600 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-yellow/30 transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-yellow/20 min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:trophy" class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold">My Completed Runs</h3>
          <p class="text-xs md:text-sm text-yellow-100">View & share your videos</p>
        </div>
      </NuxtLink>

      <NuxtLink
        v-if="browseRunsAvailable"
        to="/browse-runs"
        class="bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-xl shadow-lg hover:shadow-purple/30 transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-purple/20 min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:film" class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold">Browse Community Runs</h3>
          <p class="text-xs md:text-sm text-purple-100">Watch challenge videos</p>
        </div>
      </NuxtLink>

      <NuxtLink
        to="/profile"
        class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-gray-800 min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-gray-800 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:user-circle" class="w-6 h-6 md:w-8 md:h-8 text-gray-300" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold text-white">Edit Profile</h3>
          <p class="text-xs md:text-sm text-gray-400">Update your info and avatar</p>
        </div>
      </NuxtLink>

      <NuxtLink
        to="/preferences"
        class="bg-gradient-to-br from-magenta-muted to-magenta-dark text-white rounded-xl shadow-lg hover:shadow-magenta/30 transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-magenta/20 min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:cog-6-tooth" class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold">Preferences</h3>
          <p class="text-xs md:text-sm text-magenta-100">Card designs & OBS overlays</p>
        </div>
      </NuxtLink>

      <!-- Card Design Shop -->
      <NuxtLink
        to="/shop"
        class="bg-gradient-to-br from-yellow-500 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-yellow/30 transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-yellow/20 hover:scale-105 transform min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:shopping-bag" class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold">Card Design Shop</h3>
          <p class="text-xs md:text-sm text-yellow-100">Browse premium designs</p>
        </div>
      </NuxtLink>

      <!-- Admin Only: Manage Games -->
      <NuxtLink
        v-if="isAdmin"
        to="/games/manage"
        class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all p-4 md:p-6 flex items-center space-x-3 md:space-x-4 border border-gray-800 min-h-[80px] md:min-h-auto"
      >
        <div class="flex-shrink-0 bg-gray-800 rounded-lg p-2 md:p-3">
          <Icon name="heroicons:puzzle-piece" class="w-6 h-6 md:w-8 md:h-8 text-gray-300" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base md:text-lg font-semibold text-white">Manage Games</h3>
          <p class="text-xs md:text-sm text-gray-400">Add and edit game library</p>
        </div>
      </NuxtLink>
    </div>

    <!-- Sent Challenges Section -->
    <div v-if="sentChallenges.length > 0" class="mt-8">
      <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
        <Icon name="heroicons:trophy" class="w-6 h-6 text-orange-400" />
        My Challenges
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="challengeGroup in sentChallenges"
          :key="challengeGroup.playthroughUuid"
          class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-4 hover:border-orange-500/50 transition-all"
        >
          <!-- Game Image and Info -->
          <div class="flex items-start gap-3 mb-3">
            <div
              v-if="challengeGroup.game.imageBase64"
              class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700"
            >
              <img
                :src="`data:image/jpeg;base64,${challengeGroup.game.imageBase64}`"
                :alt="challengeGroup.game.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-white font-semibold text-sm line-clamp-1">{{ challengeGroup.game.name }}</h3>
              <p class="text-gray-400 text-xs">{{ challengeGroup.ruleset.name }}</p>
              <p class="text-gray-500 text-xs mt-1">{{ formatDate(challengeGroup.createdAt) }}</p>
            </div>
          </div>

          <!-- Challenge Stats -->
          <div class="mb-3 space-y-1">
            <div class="flex items-center justify-between text-xs">
              <span class="text-gray-400">Total Challenges:</span>
              <span class="text-white font-semibold">{{ challengeGroup.challenges.length }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-gray-400">Accepted:</span>
              <span class="text-green-400 font-semibold">{{ getAcceptedCount(challengeGroup.challenges) }}</span>
            </div>
          </div>

          <!-- Participants List -->
          <div class="mb-3 max-h-32 overflow-y-auto space-y-1">
            <div
              v-for="challenge in challengeGroup.challenges"
              :key="challenge.uuid"
              class="flex items-center justify-between text-xs bg-black/20 rounded px-2 py-1"
            >
              <span class="text-gray-300 truncate">{{ challenge.challengedUser.username }}</span>
              <span
                class="px-2 py-0.5 rounded text-xs border flex-shrink-0"
                :class="getStatusBadgeClass(challenge.status)"
              >
                {{ challenge.status }}
              </span>
            </div>
          </div>

          <!-- View Comparison Button -->
          <NuxtLink
            :to="`/challenges/comparison/${challengeGroup.playthroughUuid}`"
            class="block w-full text-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg transition-all text-sm font-semibold"
          >
            📊 View Comparison
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Empty State for Challenges -->
    <div v-else-if="!challengesLoading && !loading" class="mt-8">
      <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-8 text-center">
        <Icon name="heroicons:trophy" class="w-12 h-12 text-gray-500 mx-auto mb-3" />
        <h3 class="text-lg font-semibold text-white mb-2">No Challenges Yet</h3>
        <p class="text-gray-400 text-sm mb-4">
          Challenge someone from your playthrough to see comparison results here!
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes pulse-slow {
  0%, 100% {
    opacity: 1;
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
  }
  50% {
    opacity: 0.95;
    box-shadow: 0 0 40px rgba(34, 197, 94, 0.5);
  }
}

.animate-pulse-slow {
  animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

