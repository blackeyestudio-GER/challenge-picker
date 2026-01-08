<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useNotifications } from '~/composables/useNotifications'

definePageMeta({
  layout: false
})

const { initTheme } = useThemeSwitcher()
const route = useRoute()
const { verifyEmail, resendVerificationEmail, isAuthenticated, loadAuth } = useAuth()
const { success: showSuccess, error: showError } = useNotifications()

const verificationToken = ref<string | null>(null)
const loading = ref(false)
const error = ref('')
const success = ref('')
const email = ref('')
const resending = ref(false)

onMounted(() => {
  initTheme()
  loadAuth()
  
  // Check for token in URL
  const token = route.query.token as string | undefined
  if (token) {
    verificationToken.value = token
    handleVerify()
  }
})

const handleVerify = async () => {
  if (!verificationToken.value) {
    error.value = 'No verification token provided'
    return
  }

  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const result = await verifyEmail(verificationToken.value)
    
    if (result.success) {
      success.value = result.message || 'Email verified successfully!'
      showSuccess('Email verified successfully!')
      setTimeout(() => {
        navigateTo('/login')
      }, 2000)
    } else {
      error.value = result.error || 'Failed to verify email'
      showError(result.error || 'Failed to verify email')
    }
  } catch (e: any) {
    error.value = e.message || 'An error occurred'
    showError(e.message || 'An error occurred')
  } finally {
    loading.value = false
  }
}

const handleResend = async () => {
  if (!email.value) {
    error.value = 'Please enter your email address'
    return
  }

  error.value = ''
  resending.value = true

  try {
    const result = await resendVerificationEmail(email.value)
    
    if (result.success) {
      success.value = result.message || 'Verification email sent! Check your inbox.'
      showSuccess('Verification email sent!')
    } else {
      error.value = result.error || 'Failed to send verification email'
      showError(result.error || 'Failed to send verification email')
    }
  } catch (e: any) {
    error.value = e.message || 'An error occurred'
    showError(e.message || 'An error occurred')
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <ThemeToggle />
    <div class="auth-page__background"></div>
    
    <div class="auth-page__content">
      <div class="auth-page__header">
        <h1 class="auth-page__logo">Challenge Picker</h1>
        <p class="auth-page__subtitle">Email Verification</p>
      </div>

      <!-- Verification Success/Error -->
      <div v-if="verificationToken" class="auth-page__form-card">
        <div v-if="success" class="auth-page__message auth-page__message--success">
          {{ success }}
        </div>

        <div v-if="error" class="auth-page__message auth-page__message--error">
          {{ error }}
        </div>

        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"></div>
          <p class="text-gray-400">Verifying email...</p>
        </div>

        <div v-else-if="!success && !error" class="text-center py-8">
          <p class="text-gray-400 mb-6">Click the button below to verify your email.</p>
          <button
            @click="handleVerify"
            class="auth-page__submit"
          >
            Verify Email
          </button>
        </div>
      </div>

      <!-- Resend Verification Form -->
      <div v-else class="auth-page__form-card">
        <form @submit.prevent="handleResend" class="auth-page__form">
          <div v-if="success" class="auth-page__message auth-page__message--success">
            {{ success }}
          </div>

          <div v-if="error" class="auth-page__message auth-page__message--error">
            {{ error }}
          </div>

          <div class="auth-page__field">
            <label for="email" class="auth-page__label">Email</label>
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

          <button
            type="submit"
            :disabled="resending"
            class="auth-page__submit"
          >
            <span v-if="resending">Sending...</span>
            <span v-else>Resend Verification Email</span>
          </button>
        </form>

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

