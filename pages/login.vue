<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { useNotifications } from '~/composables/useNotifications'

definePageMeta({
  layout: false // Login page has its own full-page design
})

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})

const { login, isAuthenticated, loadAuth } = useAuth()
const { success: showSuccess, error: showError } = useNotifications()
const router = useRouter()

// Form state
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

// Load auth state and redirect if already authenticated
onMounted(() => {
  // Check for Discord token in URL (fallback method)
  const route = useRoute()
  const discordToken = route.query.discord_token as string
  const discordSuccess = route.query.discord_success as string
  const verify = route.query.verify as string
  
  // Show verification message if redirected from registration
  if (verify === '1') {
    showSuccess('Account created! Please check your email to verify your account.')
  }
  
  if (discordToken && discordSuccess) {
    console.log('[Discord Login] Token received via URL fallback')
    localStorage.setItem('auth_token', discordToken)
    // Remove token from URL and redirect
    navigateTo('/dashboard')
    return
  }
  
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
      showSuccess('Login successful!')
      await navigateTo('/dashboard')
    } else {
      const errorMsg = result.error || 'Login failed'
      error.value = errorMsg
      showError(errorMsg)
    }
  } catch (e: any) {
    error.value = e.message || 'An error occurred'
  } finally {
    loading.value = false
  }
}

// Discord OAuth login
const discordLoading = ref(false)

const handleDiscordLogin = async () => {
  discordLoading.value = true
  error.value = ''
  
  try {
    const response = await $fetch('/api/auth/discord/login')
    
    if (response.success && response.data.authUrl) {
      // Open Discord OAuth in popup
      const popup = window.open(response.data.authUrl, 'Discord Login', 'width=500,height=700')
      
      // Listen for OAuth callback messages
      const handleMessage = (event: MessageEvent) => {
        console.log('[Discord Login] Received message:', event.data, 'from origin:', event.origin)
        
        const data = event.data
        
        if (data.type === 'discord_login_success' && data.token) {
          console.log('[Discord Login] Success! Saving token and redirecting...')
          // Save token and user data
          localStorage.setItem('auth_token', data.token)
          localStorage.setItem('auth_user', JSON.stringify(data.user))
          
          // Close popup and redirect
          popup?.close()
          window.removeEventListener('message', handleMessage)
          navigateTo('/dashboard')
        } else if (data.type === 'discord_login_error' || data.type === 'discord_error') {
          console.error('[Discord Login] Error:', data.message)
          error.value = data.message || 'Discord login failed'
          popup?.close()
          window.removeEventListener('message', handleMessage)
          discordLoading.value = false
        }
      }
      
      window.addEventListener('message', handleMessage)
      
      // Also check if popup was blocked
      if (!popup || popup.closed) {
        error.value = 'Popup was blocked. Please allow popups for this site.'
        discordLoading.value = false
        return
      }
      
      // Backup: Poll for popup close and check localStorage (in case postMessage fails)
      const checkPopupInterval = setInterval(() => {
        if (popup?.closed) {
          console.log('[Discord Login] Popup closed, checking localStorage...')
          clearInterval(checkPopupInterval)
          window.removeEventListener('message', handleMessage)
          
          // Check if token was saved (from backend fallback)
          const token = localStorage.getItem('auth_token')
          if (token) {
            console.log('[Discord Login] Token found in localStorage, redirecting...')
            navigateTo('/dashboard')
          } else {
            console.log('[Discord Login] No token found, login may have been cancelled')
            discordLoading.value = false
          }
        }
      }, 500)
    }
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to initiate Discord login'
    discordLoading.value = false
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
        <p class="auth-page__subtitle">Sign in to start streaming</p>
      </div>

      <!-- Login Form -->
      <div class="auth-page__form-card">
        <form @submit.prevent="handleLogin" class="auth-page__form">
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

          <!-- Password Field -->
          <div class="auth-page__field">
            <div class="flex items-center justify-between">
              <label for="password" class="auth-page__label">
                Password
              </label>
              <NuxtLink to="/auth/reset-password" class="auth-page__forgot-link">
                Forgot password?
              </NuxtLink>
            </div>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              autocomplete="current-password"
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
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- OAuth Section (Coming Soon) -->
        <div class="auth-page__oauth">
          <div class="auth-page__oauth-divider">
            <div class="auth-page__oauth-divider-line"></div>
            <div class="auth-page__oauth-divider-text">Or continue with</div>
          </div>

          <div class="auth-page__oauth-buttons">
            <button
              disabled
              class="auth-page__oauth-button"
            >
              <svg class="auth-page__oauth-icon" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/>
              </svg>
              <span class="auth-page__oauth-text">Twitch (Soon)</span>
            </button>
            <button
              @click="handleDiscordLogin"
              :disabled="discordLoading"
              class="auth-page__oauth-button auth-page__oauth-button--discord"
            >
              <svg class="auth-page__oauth-icon" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z"/>
              </svg>
              <span v-if="discordLoading" class="auth-page__oauth-text">Connecting...</span>
              <span v-else class="auth-page__oauth-text">Discord</span>
            </button>
          </div>
        </div>

        <!-- Register Link -->
        <div class="auth-page__footer">
          <p class="auth-page__footer-text">
            Don't have an account?
            <NuxtLink to="/register" class="auth-page__footer-link">
              Sign up
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

