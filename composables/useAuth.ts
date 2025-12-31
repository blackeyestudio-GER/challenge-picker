import { ref, computed } from 'vue'

interface User {
  uuid: string
  email: string
  username: string
  avatar: string | null
  oauthProvider: string | null
  isAdmin: boolean
  discordId: string | null
  discordUsername: string | null
  discordAvatar: string | null
  twitchId: string | null
  twitchUsername: string | null
  twitchAvatar: string | null
  theme: string | null
}

interface AuthResponse {
  token: string
  user: User
  expiresIn: number
}

const user = ref<User | null>(null)
const token = ref<string | null>(null)

export const useAuth = () => {
  const config = useRuntimeConfig()
  const isDev = config.public.dev || false
  
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.isAdmin ?? false)

  // Load auth state from localStorage on init
  const loadAuth = () => {
    if (import.meta.client) {
      const savedToken = localStorage.getItem('auth_token')
      const savedUser = localStorage.getItem('auth_user')
      
      if (savedToken && savedUser) {
        token.value = savedToken
        user.value = JSON.parse(savedUser)
        
        // Apply user's theme preference
        if (user.value?.theme && import.meta.client) {
          const html = document.documentElement
          html.classList.remove('theme-default', 'theme-light')
          if (user.value.theme !== 'default') {
            html.classList.add(`theme-${user.value.theme}`)
          }
          localStorage.setItem('theme', user.value.theme)
        }
        
        // Debug logging in development
        if (isDev) {
          console.log('🔐 Auth loaded:', {
            token: savedToken,
            user: JSON.parse(savedUser)
          })
        }
      }
    }
  }

  // Register new user
  const register = async (email: string, username: string, password: string) => {
    try {
      const response = await $fetch<{ success: boolean; data: User; message: string }>('/api/users', {
        method: 'POST',
        body: { email, username, password }
      })

      if (response.success) {
        // Debug logging in development
        if (isDev) {
          console.log('✅ Registration successful:', response.data)
        }
        return { success: true, user: response.data }
      }
      
      throw new Error('Registration failed')
    } catch (error: any) {
      console.error('Registration error:', error)
      // Better error extraction
      let message = 'Registration failed'
      
      if (error.data?.error?.message) {
        message = error.data.error.message
      } else if (error.data?.message) {
        message = error.data.message
      } else if (error.message) {
        message = error.message
      } else if (error.statusMessage) {
        message = error.statusMessage
      }
      
      return { success: false, error: message }
    }
  }

  // Login user
  const login = async (email: string, password: string) => {
    try {
      const response = await $fetch<{ success: boolean; data: AuthResponse; message: string }>('/api/auth/login', {
        method: 'POST',
        body: { email, password }
      })

      if (response.success) {
        token.value = response.data.token
        user.value = response.data.user

        // Apply user's theme preference
        if (response.data.user.theme && import.meta.client) {
          const html = document.documentElement
          html.classList.remove('theme-default', 'theme-light')
          if (response.data.user.theme !== 'default') {
            html.classList.add(`theme-${response.data.user.theme}`)
          }
          localStorage.setItem('theme', response.data.user.theme)
        }

        // Save to localStorage
        if (import.meta.client) {
          localStorage.setItem('auth_token', response.data.token)
          localStorage.setItem('auth_user', JSON.stringify(response.data.user))
        }

        // Debug logging in development
        if (isDev) {
          console.log('✅ Login successful:', {
            token: response.data.token,
            user: response.data.user
          })
        }

        return { success: true }
      }

      throw new Error('Login failed')
    } catch (error: any) {
      const message = error.data?.error?.message || error.message || 'Login failed'
      return { success: false, error: message }
    }
  }

  // Logout user
  const logout = () => {
    token.value = null
    user.value = null

    if (import.meta.client) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  // Get auth header for API requests
  const getAuthHeader = () => {
    return token.value ? { Authorization: `Bearer ${token.value}` } : {}
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    loadAuth,
    register,
    login,
    logout,
    getAuthHeader
  }
}

