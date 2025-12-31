<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

definePageMeta({
  layout: false // Register page has its own full-page design
})

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
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
  <div class="auth-page">
    <ThemeToggle />
    <!-- Animated background gradient overlay -->
    <div class="auth-page__background"></div>
    
    <!-- Content -->
    <div class="auth-page__content">
      <!-- Logo/Header -->
      <div class="auth-page__header">
        <h1 class="auth-page__logo">
          Challenge Picker
        </h1>
        <p class="auth-page__subtitle">Join the streaming community</p>
      </div>

      <!-- Register Form -->
      <div class="auth-page__form-card">
        <!-- Success Message -->
        <div v-if="success" class="auth-page__message auth-page__message--success">
          <p class="font-medium">🎉 Account created successfully!</p>
          <p class="text-sm mt-1">Logging you in and redirecting to dashboard...</p>
        </div>

        <form v-else @submit.prevent="handleRegister" class="auth-page__form">
          <!-- Error Message -->
          <div v-if="error" class="auth-page__message auth-page__message--error">
            {{ error }}
          </div>

          <!-- Email Field -->
          <div class="auth-page__field">
            <label for="email" class="auth-page__label">
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              autocomplete="email"
              class="auth-page__input"
              placeholder="you@example.com"
            >
          </div>

          <!-- Username Field -->
          <div class="auth-page__field">
            <label for="username" class="auth-page__label">
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
              class="auth-page__input auth-page__input--magenta"
              placeholder="username"
            >
            <p class="auth-page__hint">Letters, numbers, underscore, and hyphen only</p>
          </div>

          <!-- Password Field -->
          <div class="auth-page__field">
            <label for="password" class="auth-page__label">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              minlength="8"
              autocomplete="new-password"
              class="auth-page__input"
              placeholder="••••••••"
            >
            <p class="auth-page__hint">
              Minimum 8 characters (e.g., MyPass123, streaming2024, etc.)
            </p>
          </div>

          <!-- Confirm Password Field -->
          <div class="auth-page__field">
            <label for="confirm-password" class="auth-page__label">
              Confirm Password
            </label>
            <input
              id="confirm-password"
              v-model="confirmPassword"
              type="password"
              required
              autocomplete="new-password"
              class="auth-page__input auth-page__input--magenta"
              placeholder="••••••••"
            >
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="auth-page__submit"
          >
            <span v-if="loading">Creating account...</span>
            <span v-else>Create Account</span>
          </button>
        </form>

        <!-- Login Link -->
        <div class="auth-page__footer">
          <p class="auth-page__footer-text">
            Already have an account?
            <NuxtLink to="/login" class="auth-page__footer-link">
              Sign in
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

