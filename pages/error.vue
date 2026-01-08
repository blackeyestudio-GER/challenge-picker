<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

defineProps<{
  error: {
    statusCode?: number
    statusMessage?: string
    message?: string
  }
}>()

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})

const handleError = () => clearError({ redirect: '/' })
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4">
    <div class="max-w-md w-full text-center">
      <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700">
        <!-- Error Icon -->
        <div class="text-6xl mb-4">
          <span v-if="error.statusCode === 404">🔍</span>
          <span v-else-if="error.statusCode === 403">🔒</span>
          <span v-else-if="error.statusCode === 500">⚠️</span>
          <span v-else>😕</span>
        </div>

        <!-- Error Title -->
        <h1 class="text-3xl font-bold text-white mb-2">
          <span v-if="error.statusCode === 404">Page Not Found</span>
          <span v-else-if="error.statusCode === 403">Access Denied</span>
          <span v-else-if="error.statusCode === 500">Server Error</span>
          <span v-else>Something Went Wrong</span>
        </h1>

        <!-- Error Message -->
        <p class="text-gray-400 mb-6">
          <span v-if="error.statusCode === 404">
            The page you're looking for doesn't exist or has been moved.
          </span>
          <span v-else-if="error.statusCode === 403">
            You don't have permission to access this resource.
          </span>
          <span v-else-if="error.statusCode === 500">
            An internal server error occurred. Please try again later.
          </span>
          <span v-else>
            {{ error.message || 'An unexpected error occurred.' }}
          </span>
        </p>

        <!-- Error Code (if available) -->
        <p v-if="error.statusCode" class="text-gray-500 text-sm mb-6">
          Error Code: {{ error.statusCode }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button
            @click="handleError"
            class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-semibold rounded-lg transition-all"
          >
            Go Home
          </button>
          <button
            @click="$router.back()"
            class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition-all"
          >
            Go Back
          </button>
        </div>
      </div>

      <!-- Helpful Links -->
      <div class="mt-6 text-gray-500 text-sm">
        <NuxtLink to="/" class="hover:text-cyan-400 transition">Home</NuxtLink>
        <span class="mx-2">•</span>
        <NuxtLink to="/dashboard" class="hover:text-cyan-400 transition">Dashboard</NuxtLink>
        <span class="mx-2">•</span>
        <NuxtLink to="/login" class="hover:text-cyan-400 transition">Login</NuxtLink>
      </div>
    </div>
  </div>
</template>

