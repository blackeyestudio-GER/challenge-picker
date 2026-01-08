<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { user } = useAuth()
const { currentTheme, availableThemes, switchTheme, initTheme, syncTheme } = useThemeSwitcher()
const loading = ref(false)

// Initialize theme on mount to ensure currentTheme is synced
onMounted(() => {
  // First sync with DOM state (in case theme was already applied)
  syncTheme()
  
  // Then check user profile and apply if different
  if (user.value?.theme) {
    const userTheme = user.value.theme as 'default' | 'light'
    if (currentTheme.value !== userTheme) {
      switchTheme(userTheme)
    }
  } else {
    // If no user theme, ensure we're synced with localStorage
    initTheme()
  }
})

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

// Get badge background color for each theme
const getThemeBadgeBgColor = (themeName: string): string => {
  const badgeColors: Record<string, string> = {
    default: 'rgba(6, 182, 212, 0.2)',  // cyan with opacity for dark theme
    light: 'rgba(103, 232, 249, 0.4)',   // pastel cyan for light theme
  }
  return badgeColors[themeName] || badgeColors.default
}

// Get badge text color for each theme
const getThemeBadgeTextColor = (themeName: string): string => {
  const textColors: Record<string, string> = {
    default: '#06b6d4',  // bright cyan for dark theme
    light: '#0891b2',    // darker cyan for light theme (better contrast)
  }
  return textColors[themeName] || textColors.default
}

// Get badge border color for each theme
const getThemeBadgeBorderColor = (themeName: string): string => {
  const borderColors: Record<string, string> = {
    default: 'rgba(6, 182, 212, 0.5)',  // cyan border for dark theme
    light: 'rgba(8, 145, 178, 0.4)',   // darker cyan border for light theme
  }
  return borderColors[themeName] || borderColors.default
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
  <div class="themes-page">
    <!-- Page Header -->
    <div class="themes-page__header">
      <div class="themes-page__header-content">
        <NuxtLink
          to="/dashboard"
          class="themes-page__back-button"
        >
          <Icon name="heroicons:arrow-left" class="themes-page__back-icon" />
        </NuxtLink>
        <h1 class="themes-page__title">Themes</h1>
      </div>
      <p class="themes-page__description">Choose your preferred color theme. Your selection will be saved to your profile.</p>
    </div>

    <!-- Theme Cards -->
    <div class="themes-page__grid">
        <div
          v-for="theme in availableThemes"
          :key="theme.name"
          @click="handleThemeSelect(theme.name)"
          :class="[
            'themes-page__card',
            currentTheme === theme.name 
              ? 'themes-page__card--active' 
              : 'themes-page__card--inactive'
          ]"
        >
          <!-- Color Stripes -->
          <div class="themes-page__card-stripes">
            <div
              v-for="(color, index) in getThemeColors(theme.name)"
              :key="index"
              :style="{ backgroundColor: color }"
              class="themes-page__card-stripe"
            />
          </div>

          <!-- Content -->
          <div class="themes-page__card-content">
            <div class="themes-page__card-header">
              <h3 class="themes-page__card-title">{{ theme.label }}</h3>
              <Icon
                v-if="currentTheme === theme.name"
                name="heroicons:check-circle"
                class="themes-page__card-check"
              />
            </div>
            <p class="themes-page__card-description">{{ theme.description }}</p>
            
            <!-- Selected Badge -->
            <div
              v-if="currentTheme === theme.name"
              class="themes-page__card-badge"
              :style="{
                backgroundColor: getThemeBadgeBgColor(theme.name),
                color: getThemeBadgeTextColor(theme.name),
                borderColor: getThemeBadgeBorderColor(theme.name)
              }"
            >
              <Icon name="heroicons:check" class="themes-page__card-badge-icon" />
              <span>Active</span>
            </div>
          </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="themes-page__loading-overlay"
    >
      <div class="themes-page__loading-card">
        <Icon name="heroicons:arrow-path" class="themes-page__loading-icon" />
        <span class="themes-page__loading-text">Saving theme...</span>
      </div>
    </div>
  </div>
</template>

