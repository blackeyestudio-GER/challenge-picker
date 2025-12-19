<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  middleware: 'auth'
})

const { user, isAdmin, logout, loadAuth } = useAuth()

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
    </main>
  </div>
</template>

