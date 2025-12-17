<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  middleware: 'auth'
})

const { user, logout, loadAuth } = useAuth()

onMounted(() => {
  loadAuth()
})

const handleLogout = async () => {
  logout()
  await navigateTo('/login')
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800">
    <!-- Top Navigation -->
    <nav class="bg-gray-900/80 backdrop-blur-sm shadow-lg border-b border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold bg-gradient-to-r from-cyan to-magenta bg-clip-text text-transparent">Challenge Picker</h1>
          </div>
          <div class="flex items-center space-x-4">
            <NuxtLink
              to="/profile"
              class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition"
            >
              <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-800 flex items-center justify-center border border-gray-700">
                <img v-if="user?.avatar" :src="user.avatar" alt="Avatar" class="w-full h-full object-cover">
                <Icon v-else name="heroicons:user-circle-solid" class="w-5 h-5 text-gray-500" />
              </div>
              <span class="hidden sm:inline">{{ user?.username }}</span>
            </NuxtLink>
            <button
              @click="handleLogout"
              class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Section -->
      <div class="bg-gradient-to-r from-cyan-muted to-magenta-muted rounded-2xl shadow-2xl p-8 mb-8 text-white border border-cyan/20">
        <h2 class="text-3xl font-bold mb-2">Welcome back, {{ user?.username }}! 👋</h2>
        <p class="text-cyan-50">Your streaming dashboard is ready to go!</p>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <NuxtLink
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
      </div>

      <!-- Info Section -->
      <div class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-2xl p-8 border border-gray-800">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-bold text-white mb-4">Streaming System Ready! 🎉</h3>
          <p class="text-gray-400 mb-6">
            Your streaming challenge system is fully operational with:
          </p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-cyan mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-white">Secure Registration</p>
                <p class="text-sm text-gray-400">Email/password with validation</p>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-magenta mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-white">JWT Authentication</p>
                <p class="text-sm text-gray-400">Secure token-based sessions</p>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-cyan mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-white">Profile Management</p>
                <p class="text-sm text-gray-400">Edit info, upload avatar</p>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-magenta mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-white">Mobile Responsive</p>
                <p class="text-sm text-gray-400">Works on all devices</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

