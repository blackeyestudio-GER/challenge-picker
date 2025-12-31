<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { usePlaythrough } from '~/composables/usePlaythrough'

definePageMeta({
  middleware: 'auth'
})

const { user, isAdmin, loadAuth } = useAuth()
const { activePlaythrough, fetchActivePlaythrough } = usePlaythrough()
const loading = ref(true)
const browseRunsAvailable = ref(false)

onMounted(async () => {
  loadAuth()
  
  try {
    await fetchActivePlaythrough()
    
    // Check if browse runs feature is enabled (feature flag)
    try {
      const featureResponse = await $fetch<{ success: boolean; data: { feature: string; enabled: boolean } }>(
        '/api/features/browse_community_runs'
      )
      
      const featureEnabled = featureResponse.data.enabled
      
      // Only check data availability if feature is enabled
      if (featureEnabled) {
        const dataResponse = await $fetch<{ success: boolean; data: { available: boolean; count: number } }>(
          '/api/playthrough/browse/availability'
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
      <h2 class="dashboard-page__welcome-title">Welcome back, {{ user?.username }}! 👋</h2>
      <p class="dashboard-page__welcome-subtitle">Your streaming dashboard is ready to go!</p>
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
            :to="`/playthrough/${activePlaythrough.uuid}/setup`"
            class="dashboard-page__active-button"
          >
            Resume Game →
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-page__actions">
      <NuxtLink
        v-if="!activePlaythrough"
        to="/playthrough/new"
        class="bg-gradient-to-br from-cyan-muted to-cyan-dark text-white rounded-xl shadow-lg hover:shadow-cyan/30 transition-all p-6 flex items-center space-x-4 border border-cyan/20"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
          <Icon name="heroicons:play-circle" class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">New Game Session</h3>
          <p class="text-sm text-cyan-100">Start a new playthrough</p>
        </div>
      </NuxtLink>
      
      <div
        v-else
        class="bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg p-6 flex items-center space-x-4 border border-gray-700 opacity-50 cursor-not-allowed"
      >
        <div class="flex-shrink-0 bg-gray-700 rounded-lg p-3">
          <Icon name="heroicons:play-circle" class="w-8 h-8 text-gray-500" />
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-400">New Game Session</h3>
          <p class="text-sm text-gray-500">Finish current game first</p>
        </div>
      </div>

      <NuxtLink
        to="/my-runs"
        class="bg-gradient-to-br from-yellow-600 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-yellow/30 transition-all p-6 flex items-center space-x-4 border border-yellow/20"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
          <Icon name="heroicons:trophy" class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">My Completed Runs</h3>
          <p class="text-sm text-yellow-100">View & share your videos</p>
        </div>
      </NuxtLink>

      <NuxtLink
        v-if="browseRunsAvailable"
        to="/browse-runs"
        class="bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-xl shadow-lg hover:shadow-purple/30 transition-all p-6 flex items-center space-x-4 border border-purple/20"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
          <Icon name="heroicons:film" class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">Browse Community Runs</h3>
          <p class="text-sm text-purple-100">Watch challenge videos</p>
        </div>
      </NuxtLink>

      <NuxtLink
        to="/profile"
        class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all p-6 flex items-center space-x-4 border border-gray-800"
      >
        <div class="flex-shrink-0 bg-gray-800 rounded-lg p-3">
          <Icon name="heroicons:user-circle" class="w-8 h-8 text-gray-300" />
        </div>
        <div>
          <h3 class="text-lg font-semibold text-white">Edit Profile</h3>
          <p class="text-sm text-gray-400">Update your info and avatar</p>
        </div>
      </NuxtLink>

      <NuxtLink
        to="/obs-sources"
        class="bg-gradient-to-br from-magenta-muted to-magenta-dark text-white rounded-xl shadow-lg hover:shadow-magenta/30 transition-all p-6 flex items-center space-x-4 border border-magenta/20"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
          <Icon name="heroicons:video-camera" class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">OBS Browser Sources</h3>
          <p class="text-sm text-magenta-100">Streaming overlays</p>
        </div>
      </NuxtLink>

      <!-- Card Design Shop -->
      <NuxtLink
        to="/shop"
        class="bg-gradient-to-br from-yellow-500 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-yellow/30 transition-all p-6 flex items-center space-x-4 border border-yellow/20 hover:scale-105 transform"
      >
        <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
          <Icon name="heroicons:shopping-bag" class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">Card Design Shop</h3>
          <p class="text-sm text-yellow-100">Browse premium designs</p>
        </div>
      </NuxtLink>

      <!-- Admin Only: Manage Games -->
      <NuxtLink
        v-if="isAdmin"
        to="/games/manage"
        class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all p-6 flex items-center space-x-4 border border-gray-800"
      >
        <div class="flex-shrink-0 bg-gray-800 rounded-lg p-3">
          <Icon name="heroicons:puzzle-piece" class="w-8 h-8 text-gray-300" />
        </div>
        <div>
          <h3 class="text-lg font-semibold text-white">Manage Games</h3>
          <p class="text-sm text-gray-400">Add and edit game library</p>
        </div>
      </NuxtLink>
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

