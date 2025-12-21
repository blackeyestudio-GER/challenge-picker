<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: false // Login page has its own full-page design
})

const { login, isAuthenticated, loadAuth } = useAuth()
const router = useRouter()

// Form state
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

// Load auth state and redirect if already authenticated
onMounted(() => {
  loadAuth()
  if (isAuthenticated.value) {
    navigateTo('/dashboard')
  }
})

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    const result = await login(email.value, password.value)
    
    if (result.success) {
      await navigateTo('/dashboard')
    } else {
      error.value = result.error || 'Login failed'
    }
  } catch (e: any) {
    error.value = e.message || 'An error occurred'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900 px-4 py-12 relative overflow-hidden">
    <!-- Animated background gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-cyan/20 via-gray-900 to-magenta/20"></div>
    
    <!-- Content -->
    <div class="max-w-md w-full relative z-10">
      <!-- Logo/Header -->
      <div class="text-center mb-8">
        <h1 class="text-5xl font-bold bg-gradient-to-r from-cyan to-magenta bg-clip-text text-transparent mb-3">
          Challenge Picker
        </h1>
        <p class="text-gray-400 text-lg">Sign in to start streaming</p>
      </div>

      <!-- Login Form -->
      <div class="bg-gray-900/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-gray-800">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Error Message -->
          <div v-if="error" class="bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg p-4 text-sm">
            {{ error }}
          </div>

          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              autocomplete="email"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan focus:border-transparent transition"
              placeholder="you@example.com"
            >
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              autocomplete="current-password"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-magenta focus:border-transparent transition"
              placeholder="••••••••"
            >
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-gradient-to-r from-cyan to-magenta hover:from-cyan-muted hover:to-magenta-muted text-white font-bold py-3 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-cyan/50"
          >
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- OAuth Section (Coming Soon) -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-gray-900/80 text-gray-500">Or continue with</span>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-3">
            <button
              disabled
              class="flex items-center justify-center px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/30 text-gray-600 cursor-not-allowed"
            >
              <span class="text-sm font-medium">Twitch (Soon)</span>
            </button>
            <button
              disabled
              class="flex items-center justify-center px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/30 text-gray-600 cursor-not-allowed"
            >
              <span class="text-sm font-medium">Discord (Soon)</span>
            </button>
          </div>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-400">
            Don't have an account?
            <NuxtLink to="/register" class="font-medium bg-gradient-to-r from-cyan to-magenta bg-clip-text text-transparent hover:from-cyan-dark hover:to-magenta-dark transition">
              Sign up
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

