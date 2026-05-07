<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useNotifications } from '~/composables/useNotifications'
import { extractErrorMessage } from '~/utils/errorHandler'

definePageMeta({
  layout: false
})

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})

const route = useRoute()
const { isAuthenticated, loadAuth } = useAuth()
const { success: showSuccess, error: showError } = useNotifications()

const verificationToken = ref<string | null>(null)
const loading = ref(false)
const verified = ref(false)
const error = ref('')

onMounted(async () => {
  loadAuth()
  if (isAuthenticated.value) {
    // Already logged in, check if verified
    const { user } = useAuth()
    if (user.value?.emailVerified) {
      verified.value = true
      return
    }
  }

  // Check for token in URL
  const token = route.query.token as string | undefined
  if (token) {
    verificationToken.value = token
    await verifyEmail(token)
  }
})

const verifyEmail = async (token: string) => {
  loading.value = true
  error.value = ''

  try {
    const response = await $fetch<{ success: boolean; message: string }>(
      '/api/auth/verify-email',
      {
        method: 'POST',
        body: { token }
      }
    )

    if (response.success) {
      verified.value = true
      showSuccess('Email verified successfully!')
      
      // Redirect to login after 2 seconds
      setTimeout(() => {
        navigateTo('/login?verified=1')
      }, 2000)
    } else {
      error.value = 'Verification failed'
    }
  } catch (e: unknown) {
    const errorMsg = extractErrorMessage(e, 'Verification failed')
    error.value = errorMsg
    showError(errorMsg)
  } finally {
    loading.value = false
  }
}

const resendVerification = async () => {
  const email = route.query.email as string | undefined
  if (!email) {
    error.value = 'Email address required'
    return
  }

  loading.value = true
  error.value = ''

  try {
    const response = await $fetch<{ success: boolean; message: string }>(
      '/api/auth/resend-verification',
      {
        method: 'POST',
        body: { email }
      }
    )

    if (response.success) {
      showSuccess('Verification email sent! Check your inbox.')
    }
  } catch (e: unknown) {
    const errorMsg = extractErrorMessage(e, 'Failed to resend verification email')
    error.value = errorMsg
    showError(errorMsg)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <ThemeToggle />
    <div class="auth-page__background"/>
    
    <div class="auth-page__content">
      <div class="auth-page__header">
        <h1 class="auth-page__logo">
          Challenge Picker
        </h1>
        <p class="auth-page__subtitle">Email Verification</p>
      </div>

      <div class="auth-page__form-card">
        <!-- Success State -->
        <div v-if="verified" class="auth-page__state">
          <div class="auth-page__state-icon">✅</div>
          <h2 class="auth-page__state-title">Email Verified!</h2>
          <p class="auth-page__state-copy">Your email has been successfully verified. Redirecting to login...</p>
          <div class="auth-page__spinner"/>
        </div>

        <!-- Loading State -->
        <div v-else-if="loading && verificationToken" class="auth-page__state">
          <div class="auth-page__spinner"/>
          <p class="auth-page__state-copy">Verifying your email...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="auth-page__state">
          <div class="auth-page__state-icon">❌</div>
          <h2 class="auth-page__state-title">Verification Failed</h2>
          <p class="auth-page__state-copy">{{ error }}</p>
          
          <div class="space-y-3">
            <button
              :disabled="loading"
              class="auth-page__submit w-full"
              @click="resendVerification"
            >
              <span v-if="loading">Sending...</span>
              <span v-else>Resend Verification Email</span>
            </button>
            <NuxtLink to="/login" class="auth-page__inline-link">
              Back to Login
            </NuxtLink>
          </div>
        </div>

        <!-- No Token State -->
        <div v-else class="auth-page__state">
          <div class="auth-page__state-icon">📧</div>
          <h2 class="auth-page__state-title">Check Your Email</h2>
          <p class="auth-page__state-copy">
            We've sent a verification link to your email address. Please click the link to verify your account.
          </p>
          
          <div class="space-y-3">
            <button
              :disabled="loading"
              class="auth-page__submit w-full"
              @click="resendVerification"
            >
              <span v-if="loading">Sending...</span>
              <span v-else>Resend Verification Email</span>
            </button>
            <NuxtLink to="/login" class="auth-page__inline-link">
              Back to Login
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
