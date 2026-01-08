<script setup lang="ts">
import { Icon } from '#components'
import { useUserStats } from '~/composables/useUserStats'

const { user, logout, loadAuth, isAuthenticated } = useAuth()
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
  // Load auth state
  loadAuth()
  
  // Only fetch user stats if authenticated
  if (isAuthenticated.value) {
    fetchUserStats()
  }
  
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
        <NuxtLink to="/dashboard" class="flex items-center gap-2 min-w-0">
          <h1 class="text-xl sm:text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta truncate">
            Challenge Picker
          </h1>
        </NuxtLink>

        <!-- Right Side: Vote Counter + Theme Switcher + User Menu -->
        <div class="flex items-center gap-2 sm:gap-4">
          <!-- Vote Counter (gamification badge) - Hidden on mobile if username is long -->
          <div 
            v-if="totalVotes > 0"
            class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-yellow-400"
            title="Total votes contributed"
          >
            <Icon name="heroicons:star-solid" class="w-4 h-4" />
            <span class="font-semibold text-sm">{{ totalVotes }}</span>
          </div>

          <!-- User Menu -->
          <ClientOnly>
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
                  {{ user?.username?.charAt(0).toUpperCase() || '?' }}
                </span>
              </div>
              <span class="app-header__user-button__username">{{ user?.username || 'Guest' }}</span>
              <Icon 
                name="heroicons:chevron-down"
                class="app-header__user-button__icon"
                :class="{ 'app-header__user-button__icon--rotated': dropdownOpen }"
              />
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
                  <Icon name="heroicons:user-circle" class="app-header__menu-item__icon" />
                  <span>Profile</span>
                </NuxtLink>

                <!-- Preferences Link -->
                <NuxtLink
                  to="/preferences"
                  @click="closeDropdown"
                  class="app-header__menu-item"
                >
                  <Icon name="heroicons:cog-6-tooth" class="app-header__menu-item__icon" />
                  <span>Preferences</span>
                </NuxtLink>

                <!-- Themes Link -->
                <NuxtLink
                  to="/themes"
                  @click="closeDropdown"
                  class="app-header__menu-item"
                >
                  <Icon name="heroicons:paint-brush" class="app-header__menu-item__icon" />
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
                    <Icon name="heroicons:shield-check" class="app-header__menu-item__icon" />
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
                  <Icon name="heroicons:arrow-right-on-rectangle" class="app-header__menu-item__icon" />
                  <span>Logout</span>
                </button>
              </div>
            </Transition>
            </div>
            <template #fallback>
              <!-- Fallback during SSR - show placeholder button -->
              <div class="app-header__user-button">
                <div class="app-header__user-button__avatar">
                  <span class="app-header__user-button__avatar-initial">?</span>
                </div>
                <span class="app-header__user-button__username">Loading...</span>
                <Icon name="heroicons:chevron-down" class="app-header__user-button__icon" />
              </div>
            </template>
          </ClientOnly>
        </div>
      </div>
    </div>
  </header>
</template>

<!-- Styles are now in assets/css/components/header.css using BEM methodology -->
