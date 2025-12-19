<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: 'default'
})

const { register, login, isAuthenticated, loadAuth } = useAuth()
const router = useRouter()

// Form state
const email = ref('')
const username = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const error = ref('')
const success = ref(false)

// Load auth state and redirect if already authenticated
onMounted(() => {
  loadAuth()
  if (isAuthenticated.value) {
    navigateTo('/dashboard')
  }
})

const handleRegister = async () => {
  error.value = ''
  loading.value = true

  // Validate passwords match
  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match'
    loading.value = false
    return
  }

  try {
    const registerResult = await register(email.value, username.value, password.value)
    
    if (registerResult.success) {
      success.value = true
      
      // Auto-login after successful registration
      const loginResult = await login(email.value, password.value)
      
      if (loginResult.success) {
        // Redirect to dashboard after brief delay to show success message
        setTimeout(() => {
          navigateTo('/dashboard')
        }, 1000)
      } else {
        // If auto-login fails, redirect to login page
        error.value = 'Account created! Please log in.'
        setTimeout(() => {
          navigateTo('/login')
        }, 2000)
      }
    } else {
      error.value = registerResult.error || 'Registration failed'
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
        <p class="text-gray-400 text-lg">Join the streaming community</p>
      </div>

      <!-- Register Form -->
      <div class="bg-gray-900/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-gray-800">
        <!-- Success Message -->
        <div v-if="success" class="bg-green-500/10 border border-green-500/50 text-green-400 rounded-lg p-4 mb-6">
          <p class="font-medium">🎉 Account created successfully!</p>
          <p class="text-sm mt-1">Logging you in and redirecting to dashboard...</p>
        </div>

        <form v-else @submit.prevent="handleRegister" class="space-y-5">
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

          <!-- Username Field -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
              Username
            </label>
            <input
              id="username"
              v-model="username"
              type="text"
              required
              autocomplete="username"
              minlength="3"
              maxlength="50"
              pattern="[a-zA-Z0-9_-]+"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-magenta focus:border-transparent transition"
              placeholder="username"
            >
            <p class="mt-1 text-xs text-gray-500">Letters, numbers, underscore, and hyphen only</p>
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
              minlength="8"
              autocomplete="new-password"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan focus:border-transparent transition"
              placeholder="••••••••"
            >
            <p class="mt-1 text-xs text-gray-500">
              Minimum 8 characters (e.g., MyPass123, streaming2024, etc.)
            </p>
          </div>

          <!-- Confirm Password Field -->
          <div>
            <label for="confirm-password" class="block text-sm font-medium text-gray-300 mb-2">
              Confirm Password
            </label>
            <input
              id="confirm-password"
              v-model="confirmPassword"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-magenta focus:border-transparent transition"
              placeholder="••••••••"
            >
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-gradient-to-r from-cyan to-magenta hover:from-cyan-muted hover:to-magenta-muted text-white font-bold py-3 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-magenta/50"
          >
            <span v-if="loading">Creating account...</span>
            <span v-else>Create Account</span>
          </button>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-400">
            Already have an account?
            <NuxtLink to="/login" class="font-medium bg-gradient-to-r from-cyan to-magenta bg-clip-text text-transparent hover:from-cyan-dark hover:to-magenta-dark transition">
              Sign in
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

