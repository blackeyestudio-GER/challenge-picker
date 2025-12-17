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
  <div class="min-h-screen bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold text-gray-900">Challenge Picker</h1>
          </div>
          <div class="flex items-center space-x-4">
            <NuxtLink
              to="/profile"
              class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition"
            >
              <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                <img v-if="user?.avatar" :src="user.avatar" alt="Avatar" class="w-full h-full object-cover">
                <svg v-else class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
              </div>
              <span class="hidden sm:inline">{{ user?.username }}</span>
            </NuxtLink>
            <button
              @click="handleLogout"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition"
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
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-lg p-8 mb-8 text-white">
        <h2 class="text-3xl font-bold mb-2">Welcome back, {{ user?.username }}! 👋</h2>
        <p class="text-blue-100">Your profile is all set up and ready to go!</p>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <NuxtLink
          to="/playthrough/new"
          class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-xl shadow-lg hover:shadow-xl transition p-6 flex items-center space-x-4"
        >
          <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold">New Game Session</h3>
            <p class="text-sm text-green-100">Start a new playthrough</p>
          </div>
        </NuxtLink>

        <NuxtLink
          to="/profile"
          class="bg-white rounded-xl shadow hover:shadow-lg transition p-6 flex items-center space-x-4"
        >
          <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Edit Profile</h3>
            <p class="text-sm text-gray-600">Update your info and avatar</p>
          </div>
        </NuxtLink>

        <NuxtLink
          to="/obs-sources"
          class="bg-gradient-to-br from-purple-500 to-pink-600 text-white rounded-xl shadow-lg hover:shadow-xl transition p-6 flex items-center space-x-4"
        >
          <div class="flex-shrink-0 bg-white/20 rounded-lg p-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold">OBS Browser Sources</h3>
            <p class="text-sm text-purple-100">Streaming overlays</p>
          </div>
        </NuxtLink>
      </div>

      <!-- Info Section -->
      <div class="bg-white rounded-xl shadow p-8">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Authentication System Complete! 🎉</h3>
          <p class="text-gray-600 mb-6">
            Your user authentication and profile management system is fully functional with:
          </p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-gray-900">Secure Registration</p>
                <p class="text-sm text-gray-600">Email/password with validation</p>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-gray-900">JWT Authentication</p>
                <p class="text-sm text-gray-600">Secure token-based sessions</p>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-gray-900">Profile Management</p>
                <p class="text-sm text-gray-600">Edit info, upload avatar</p>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="font-medium text-gray-900">Mobile Responsive</p>
                <p class="text-sm text-gray-600">Works on all devices</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

