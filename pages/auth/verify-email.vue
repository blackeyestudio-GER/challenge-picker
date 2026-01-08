<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useNotifications } from '~/composables/useNotifications'

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
  } catch (e: any) {
    const errorMsg = e.data?.error?.message || e.message || 'Verification failed'
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
  } catch (e: any) {
    const errorMsg = e.data?.error?.message || e.message || 'Failed to resend verification email'
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
    <div class="auth-page__background"></div>
    
    <div class="auth-page__content">
      <div class="auth-page__header">
        <h1 class="auth-page__logo">
          Challenge Picker
        </h1>
        <p class="auth-page__subtitle">Email Verification</p>
      </div>

      <div class="auth-page__form-card">
        <!-- Success State -->
        <div v-if="verified" class="text-center">
          <div class="text-6xl mb-4">✅</div>
          <h2 class="text-2xl font-bold text-white mb-4">Email Verified!</h2>
          <p class="text-gray-400 mb-6">Your email has been successfully verified. Redirecting to login...</p>
          <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-cyan-500 mx-auto"></div>
        </div>

        <!-- Loading State -->
        <div v-else-if="loading && verificationToken" class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"></div>
          <p class="text-gray-400">Verifying your email...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center">
          <div class="text-6xl mb-4">❌</div>
          <h2 class="text-2xl font-bold text-white mb-4">Verification Failed</h2>
          <p class="text-gray-400 mb-6">{{ error }}</p>
          
          <div class="space-y-3">
            <button
              @click="resendVerification"
              :disabled="loading"
              class="auth-page__submit w-full"
            >
              <span v-if="loading">Sending...</span>
              <span v-else>Resend Verification Email</span>
            </button>
            <NuxtLink to="/login" class="block text-center text-cyan-400 hover:text-cyan-300">
              Back to Login
            </NuxtLink>
          </div>
        </div>

        <!-- No Token State -->
        <div v-else class="text-center">
          <div class="text-6xl mb-4">📧</div>
          <h2 class="text-2xl font-bold text-white mb-4">Check Your Email</h2>
          <p class="text-gray-400 mb-6">
            We've sent a verification link to your email address. Please click the link to verify your account.
          </p>
          
          <div class="space-y-3">
            <button
              @click="resendVerification"
              :disabled="loading"
              class="auth-page__submit w-full"
            >
              <span v-if="loading">Sending...</span>
              <span v-else>Resend Verification Email</span>
            </button>
            <NuxtLink to="/login" class="block text-center text-cyan-400 hover:text-cyan-300">
              Back to Login
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
