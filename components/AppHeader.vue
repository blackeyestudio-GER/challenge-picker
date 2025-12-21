<script setup lang="ts">
import { useUserStats } from '~/composables/useUserStats'

const { user, logout } = useAuth()
const { totalVotes, fetchUserStats } = useUserStats()
const dropdownOpen = ref(false)

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleLogout = async () => {
  await logout()
  await navigateTo('/login')
}

// Close dropdown when clicking outside
onMounted(() => {
  // Fetch user stats on mount
  fetchUserStats()
  
  const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement
    if (!target.closest('.dropdown-container')) {
      closeDropdown()
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>

<template>
  <header class="bg-gray-900/95 backdrop-blur-md border-b border-gray-700 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo/Brand -->
        <NuxtLink to="/dashboard" class="flex items-center gap-2">
          <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta">
            Challenge Picker
          </h1>
        </NuxtLink>

        <!-- Right Side: Vote Counter + User Menu -->
        <div class="flex items-center gap-4">
          <!-- Vote Counter (gamification badge) -->
          <div 
            v-if="totalVotes > 0"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-yellow-400"
            title="Total votes contributed"
          >
            <Icon name="heroicons:star-solid" class="w-4 h-4" />
            <span class="font-semibold text-sm">{{ totalVotes }}</span>
          </div>

          <!-- User Menu -->
          <div class="relative dropdown-container">
          <button
            @click="toggleDropdown"
            class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition border border-gray-600"
          >
            <!-- Avatar Image or Gradient Fallback -->
            <div class="w-8 h-8 rounded-full overflow-hidden bg-gradient-to-br from-cyan to-magenta flex items-center justify-center">
              <img 
                v-if="user?.avatar" 
                :src="user.avatar" 
                :alt="user.username"
                class="w-full h-full object-cover"
              />
              <span v-else class="text-white font-bold">
                {{ user?.username?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <span class="text-white font-semibold">{{ user?.username }}</span>
            <svg 
              class="w-4 h-4 text-gray-400 transition-transform"
              :class="{ 'rotate-180': dropdownOpen }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <Transition name="dropdown">
            <div
              v-if="dropdownOpen"
              class="absolute right-0 mt-2 min-w-full w-max bg-gray-800 rounded-lg shadow-xl border border-gray-700 overflow-hidden py-2"
            >
              <!-- Profile Link -->
              <NuxtLink
                to="/profile"
                @click="closeDropdown"
                class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profile</span>
              </NuxtLink>

              <!-- OBS Preferences Link -->
              <NuxtLink
                to="/obs-sources"
                @click="closeDropdown"
                class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>OBS Preferences</span>
              </NuxtLink>

              <!-- Divider -->
              <div class="my-2 border-t border-gray-700"></div>

              <!-- Logout Button -->
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-gray-700 hover:text-red-300 transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
              </button>
            </div>
          </Transition>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
