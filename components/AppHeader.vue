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
  <header class="app-header">
    <div class="max-w-7xl mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo/Brand -->
        <NuxtLink to="/dashboard" class="flex items-center gap-2">
          <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta">
            Challenge Picker
          </h1>
        </NuxtLink>

        <!-- Right Side: Vote Counter + Theme Switcher + User Menu -->
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
            class="app-header__user-button"
          >
            <!-- Avatar Image or Gradient Fallback -->
            <div class="app-header__user-button__avatar">
              <img 
                v-if="user?.avatar" 
                :src="user.avatar" 
                :alt="user.username"
                class="w-full h-full object-cover"
              />
              <span v-else class="app-header__user-button__avatar-initial">
                {{ user?.username?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <span class="app-header__user-button__username">{{ user?.username }}</span>
            <svg 
              class="app-header__user-button__icon"
              :class="{ 'app-header__user-button__icon--rotated': dropdownOpen }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <Transition name="app-header__dropdown">
            <div
              v-if="dropdownOpen"
              class="app-header__dropdown"
            >
              <!-- Profile Link -->
              <NuxtLink
                to="/profile"
                @click="closeDropdown"
                class="app-header__menu-item"
              >
                <svg class="app-header__menu-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profile</span>
              </NuxtLink>

              <!-- OBS Preferences Link -->
              <NuxtLink
                to="/obs-sources"
                @click="closeDropdown"
                class="app-header__menu-item"
              >
                <svg class="app-header__menu-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>OBS Preferences</span>
              </NuxtLink>

              <!-- Themes Link -->
              <NuxtLink
                to="/themes"
                @click="closeDropdown"
                class="app-header__menu-item"
              >
                <svg class="app-header__menu-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                <span>Themes</span>
              </NuxtLink>

              <!-- Admin Section (only for admins) -->
              <template v-if="user?.isAdmin">
                <div class="app-header__dropdown__divider"></div>
                
                <NuxtLink
                  to="/admin"
                  @click="closeDropdown"
                  class="app-header__menu-item app-header__menu-item--accent"
                >
                  <svg class="app-header__menu-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                  </svg>
                  <span>Admin Panel</span>
                </NuxtLink>
              </template>

              <!-- Divider -->
              <div class="app-header__dropdown__divider"></div>

              <!-- Logout Button -->
              <button
                @click="handleLogout"
                class="app-header__menu-item app-header__menu-item--danger"
              >
                <svg class="app-header__menu-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

<!-- Styles are now in assets/css/components/header.css using BEM methodology -->
