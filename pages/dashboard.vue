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

onMounted(async () => {
  loadAuth()
  
  try {
    await fetchActivePlaythrough()
  } catch (err) {
    console.error('Failed to check for active playthrough:', err)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-cyan-muted to-magenta-muted rounded-2xl shadow-2xl p-8 mb-8 text-white border border-cyan/20">
      <h2 class="text-3xl font-bold mb-2">Welcome back, {{ user?.username }}! 👋</h2>
      <p class="text-cyan-50">Your streaming dashboard is ready to go!</p>
    </div>

    <!-- Active Game Alert -->
    <div v-if="!loading && activePlaythrough" class="mb-8">
      <div class="bg-gradient-to-r from-green-600/80 to-cyan-600/80 backdrop-blur-sm rounded-2xl shadow-2xl p-6 border border-green-400/30 animate-pulse-slow">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
              <Icon name="heroicons:play" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h3 class="text-2xl font-bold text-white mb-1">Game in Progress!</h3>
              <p class="text-green-50">You have an active game session running</p>
            </div>
          </div>
          <NuxtLink
            :to="`/playthrough/${activePlaythrough.uuid}/setup`"
            class="px-6 py-3 bg-white text-green-700 rounded-lg font-semibold hover:bg-green-50 transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
          >
            Resume Game →
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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

