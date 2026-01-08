<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useNotifications } from '~/composables/useNotifications'

definePageMeta({
  layout: false // Reset password page has its own full-page design
})

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})

const route = useRoute()
const { requestPasswordReset, resetPassword, isAuthenticated, loadAuth } = useAuth()
const { success: showSuccess, error: showError } = useNotifications()

// Check if we have a token in the URL (reset mode) or not (request mode)
const resetToken = ref<string | null>(null)
const isResetMode = computed(() => !!resetToken.value)

// Form state
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

// Load auth state and redirect if already authenticated
onMounted(() => {
  loadAuth()
  if (isAuthenticated.value) {
    navigateTo('/dashboard')
    return
  }

  // Check for token in URL
  const token = route.query.token as string | undefined
  if (token) {
    resetToken.value = token
  }
})

const handleRequestReset = async () => {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const result = await requestPasswordReset(email.value)
    
    if (result.success) {
      const successMsg = result.message || 'Password reset email sent! Check your inbox.'
      success.value = successMsg
      showSuccess(successMsg)
    } else {
      const errorMsg = result.error || 'Failed to send reset email'
      error.value = errorMsg
      showError(errorMsg)
    }
  } catch (e: any) {
    error.value = e.message || 'An error occurred'
  } finally {
    loading.value = false
  }
}

const handleResetPassword = async () => {
  error.value = ''
  success.value = ''

  // Validate passwords match
  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match'
    return
  }

  // Validate password length
  if (password.value.length < 8) {
    error.value = 'Password must be at least 8 characters long'
    return
  }

  if (!resetToken.value) {
    error.value = 'Invalid reset token'
    return
  }

  loading.value = true

  try {
    const result = await resetPassword(resetToken.value, password.value)
    
    if (result.success) {
      const successMsg = result.message || 'Password reset successfully! Redirecting to login...'
      success.value = successMsg
      showSuccess('Password reset successfully!')
      // Redirect to login after 2 seconds
      setTimeout(() => {
        navigateTo('/login')
      }, 2000)
    } else {
      const errorMsg = result.error || 'Failed to reset password'
      error.value = errorMsg
      showError(errorMsg)
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
        <p class="auth-page__subtitle">
          <span v-if="isResetMode">Reset your password</span>
          <span v-else>Forgot your password?</span>
        </p>
      </div>

      <!-- Request Reset Form -->
      <div v-if="!isResetMode" class="auth-page__form-card">
        <form @submit.prevent="handleRequestReset" class="auth-page__form">
          <!-- Success Message -->
          <div v-if="success" class="auth-page__message auth-page__message--success">
            {{ success }}
          </div>

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

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="auth-page__submit"
          >
            <span v-if="loading">Sending...</span>
            <span v-else>Send Reset Link</span>
          </button>
        </form>

        <!-- Back to Login Link -->
        <div class="auth-page__footer">
          <p class="auth-page__footer-text">
            Remember your password?
            <NuxtLink to="/login" class="auth-page__footer-link">
              Sign in
            </NuxtLink>
          </p>
        </div>
      </div>

      <!-- Reset Password Form -->
      <div v-else class="auth-page__form-card">
        <form @submit.prevent="handleResetPassword" class="auth-page__form">
          <!-- Success Message -->
          <div v-if="success" class="auth-page__message auth-page__message--success">
            {{ success }}
          </div>

          <!-- Error Message -->
          <div v-if="error" class="auth-page__message auth-page__message--error">
            {{ error }}
          </div>

          <!-- Password Field -->
          <div class="auth-page__field">
            <label for="password" class="auth-page__label">
              New Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              autocomplete="new-password"
              class="auth-page__input auth-page__input--magenta"
              placeholder="••••••••"
              minlength="8"
            >
            <p class="auth-page__field-hint">Must be at least 8 characters</p>
          </div>

          <!-- Confirm Password Field -->
          <div class="auth-page__field">
            <label for="confirmPassword" class="auth-page__label">
              Confirm Password
            </label>
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              type="password"
              required
              autocomplete="new-password"
              class="auth-page__input auth-page__input--magenta"
              placeholder="••••••••"
              minlength="8"
            >
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="auth-page__submit"
          >
            <span v-if="loading">Resetting...</span>
            <span v-else>Reset Password</span>
          </button>
        </form>

        <!-- Back to Login Link -->
        <div class="auth-page__footer">
          <p class="auth-page__footer-text">
            <NuxtLink to="/login" class="auth-page__footer-link">
              Back to login
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-page__field-hint {
  @apply text-xs text-gray-400 mt-1;
}
</style>

