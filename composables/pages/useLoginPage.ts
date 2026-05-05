import { computed, ref } from 'vue'
import type { User } from '~/composables/useAuth'
import { useAuth } from '~/composables/useAuth'
import { useNotifications } from '~/composables/useNotifications'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { getSafeRedirectPath } from '~/utils/safeRedirect'
import { extractErrorMessage } from '~/utils/errorHandler'

export const useLoginPage = () => {
  const { initTheme } = useThemeSwitcher()
  const { login, isAuthenticated, loadAuth, resendVerificationEmail, user, setAuthSession } = useAuth()
  const { success: showSuccess, error: showError } = useNotifications()
  const route = useRoute()

  const needsDiscordBanner = computed(() => route.query.needsDiscord === '1')
  const redirectAfterAuth = computed(() => getSafeRedirectPath(route.query.redirect))

  const email = ref('')
  const password = ref('')
  const loading = ref(false)
  const error = ref('')
  const showVerificationWarning = ref(false)
  const resendingVerification = ref(false)
  const discordLoading = ref(false)

  const bootstrap = () => {
    initTheme()

    const discordToken = route.query.discord_token as string
    const discordSuccess = route.query.discord_success as string
    const verify = route.query.verify as string
    const verified = route.query.verified as string

    if (verify === '1') {
      showSuccess('Account created! Please check your email to verify your account.')
      showVerificationWarning.value = true
    }

    if (verified === '1') {
      showSuccess('Email verified successfully! You can now log in.')
    }

    if (discordToken && discordSuccess) {
      void $fetch<{ success: boolean; data: User }>(`/api/users/me`, {
        headers: { Authorization: `Bearer ${discordToken}` },
      })
        .then((res) => {
          if (res.success && res.data) {
            setAuthSession(discordToken, res.data)
          } else {
            localStorage.setItem('auth_token', discordToken)
            loadAuth()
          }
        })
        .catch(() => {
          localStorage.setItem('auth_token', discordToken)
          loadAuth()
        })
        .finally(() => {
          const next = getSafeRedirectPath(route.query.redirect)
          void navigateTo(next ?? '/dashboard')
        })
      return
    }

    loadAuth()
    const next = redirectAfterAuth.value
    if (isAuthenticated.value && next) {
      void navigateTo(next)
      return
    }
    if (isAuthenticated.value && user.value?.discordId) {
      void navigateTo('/dashboard')
    } else if (isAuthenticated.value && user.value && !user.value.discordId) {
      void navigateTo('/profile')
    }
  }

  const handleLogin = async () => {
    error.value = ''
    loading.value = true

    try {
      const result = await login(email.value, password.value)

      if (result.success) {
        if (user.value && !user.value.emailVerified) {
          showVerificationWarning.value = true
          showError('Please verify your email address before logging in. Check your inbox for the verification link.')
          return
        }

        const next = redirectAfterAuth.value
        if (next) {
          showSuccess('Login successful!')
          await navigateTo(next)
          return
        }

        if (user.value && !user.value.discordId) {
          showSuccess('Welcome! Link Discord on your profile to open the dashboard.')
          await navigateTo('/profile')
          return
        }

        showSuccess('Login successful!')
        await navigateTo('/dashboard')
      } else {
        const errorMsg = result.error || 'Login failed'
        error.value = errorMsg
        showError(errorMsg)
      }
    } catch (e: unknown) {
      error.value = extractErrorMessage(e, 'An error occurred')
    } finally {
      loading.value = false
    }
  }

  const handleDiscordLogin = async () => {
    discordLoading.value = true
    error.value = ''

    try {
      const response = await $fetch<{ success: boolean; data?: { authUrl?: string } }>('/api/auth/discord/login')

      if (response.success && response.data?.authUrl) {
        const popup = window.open(response.data.authUrl, 'Discord Login', 'width=500,height=700')

        const handleMessage = (event: MessageEvent) => {
          const data = event.data

          if (data.type === 'discord_login_success' && data.token && data.user) {
            setAuthSession(data.token, data.user as User)
            popup?.close()
            window.removeEventListener('message', handleMessage)
            const next = redirectAfterAuth.value
            void navigateTo(next ?? '/dashboard')
          } else if (data.type === 'discord_login_error' || data.type === 'discord_error') {
            error.value = data.message || 'Discord login failed'
            showError(error.value)
            popup?.close()
            window.removeEventListener('message', handleMessage)
            discordLoading.value = false
          }
        }

        window.addEventListener('message', handleMessage)

        if (!popup || popup.closed) {
          error.value = 'Popup was blocked. Please allow popups for this site.'
          discordLoading.value = false
          return
        }

        const checkPopupInterval = setInterval(() => {
          if (popup?.closed) {
            clearInterval(checkPopupInterval)
            window.removeEventListener('message', handleMessage)

            const token = localStorage.getItem('auth_token')
            if (token) {
              loadAuth()
              const next = redirectAfterAuth.value
              void navigateTo(next ?? '/dashboard')
            } else {
              discordLoading.value = false
            }
          }
        }, 500)
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to initiate Discord login')
      discordLoading.value = false
    }
  }

  const handleResendVerification = async () => {
    if (!email.value) {
      showError('Please enter your email address first')
      return
    }

    resendingVerification.value = true
    try {
      const result = await resendVerificationEmail(email.value)
      if (result.success) {
        showSuccess('Verification email sent! Check your inbox.')
      } else {
        showError(result.error || 'Failed to resend verification email')
      }
    } catch (e: unknown) {
      showError(extractErrorMessage(e, 'Failed to resend verification email'))
    } finally {
      resendingVerification.value = false
    }
  }

  return {
    needsDiscordBanner,
    email,
    password,
    loading,
    error,
    showVerificationWarning,
    resendingVerification,
    discordLoading,
    bootstrap,
    handleLogin,
    handleDiscordLogin,
    handleResendVerification
  }
}
