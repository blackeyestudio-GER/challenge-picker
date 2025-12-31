<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { user } = useAuth()
const { currentTheme, availableThemes, switchTheme } = useThemeSwitcher()
const loading = ref(false)

// Get 5 main colors for each theme (predefined)
const getThemeColors = (themeName: string): string[] => {
  const themeColors: Record<string, string[]> = {
    default: [
      '#111212',  // bg-primary (dark)
      '#06b6d4',  // accent-primary (cyan)
      '#d946ef',  // accent-secondary (magenta)
      '#eab308',  // legendary (gold)
      '#a855f7',  // court (purple)
    ],
    light: [
      '#ffffff',  // bg-primary (white)
      '#0891b2',  // accent-primary (cyan)
      '#c026d3',  // accent-secondary (magenta)
      '#d97706',  // legendary (gold)
      '#9333ea',  // court (purple)
    ],
  }
  return themeColors[themeName] || themeColors.default
}

const handleThemeSelect = async (themeName: string) => {
  if (loading.value) return
  
  // Switch theme immediately (before API call)
  switchTheme(themeName as 'default' | 'light')
  
  loading.value = true
  try {
    // Save to backend
    if (user.value) {
        const response = await $fetch('/api/users/me/theme', {
          method: 'PUT',
          body: { theme: themeName },
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        })
        
        // Update user in auth store and localStorage
        if (response.success && response.data) {
          const { user: authUser } = useAuth()
          authUser.value = response.data
          localStorage.setItem('auth_user', JSON.stringify(response.data))
        }
      }
  } catch (error) {
    console.error('Failed to update theme:', error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center gap-4 mb-2">
        <NuxtLink
          to="/dashboard"
          class="p-2 rounded-lg hover:bg-gray-800 transition-colors"
        >
          <Icon name="heroicons:arrow-left" class="w-6 h-6 text-gray-400" />
        </NuxtLink>
        <h1 class="text-3xl font-bold text-white">Themes</h1>
      </div>
      <p class="text-gray-400 ml-14">Choose your preferred color theme. Your selection will be saved to your profile.</p>
    </div>

    <!-- Theme Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="theme in availableThemes"
          :key="theme.name"
          @click="handleThemeSelect(theme.name)"
          :class="[
            'bg-gray-800/80 backdrop-blur-sm border rounded-xl cursor-pointer transition-all duration-300 hover:scale-105 overflow-hidden',
            currentTheme === theme.name 
              ? 'border-cyan ring-2 ring-cyan ring-offset-2 ring-offset-gray-900' 
              : 'border-gray-700 hover:border-gray-600'
          ]"
        >
          <!-- Color Stripes -->
          <div class="flex h-24 mb-4 rounded-t-lg overflow-hidden">
            <div
              v-for="(color, index) in getThemeColors(theme.name)"
              :key="index"
              :style="{ backgroundColor: color }"
              class="flex-1"
            />
          </div>

          <!-- Content -->
          <div class="p-6">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-xl font-bold text-white">{{ theme.label }}</h3>
              <Icon
                v-if="currentTheme === theme.name"
                name="heroicons:check-circle"
                class="w-6 h-6 text-cyan"
              />
            </div>
            <p class="text-sm text-gray-400 mb-4">{{ theme.description }}</p>
            
            <!-- Selected Badge -->
            <div
              v-if="currentTheme === theme.name"
              class="inline-flex items-center gap-2 px-3 py-1 bg-cyan/20 text-cyan rounded-lg text-sm font-semibold"
            >
              <Icon name="heroicons:check" class="w-4 h-4" />
              <span>Active</span>
            </div>
          </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-gray-800 rounded-lg p-6 flex items-center gap-4 border border-gray-700">
        <Icon name="heroicons:arrow-path" class="w-6 h-6 text-cyan animate-spin" />
        <span class="text-white font-semibold">Saving theme...</span>
      </div>
    </div>
  </div>
</template>

