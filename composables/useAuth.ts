import { ref, computed } from 'vue'

interface User {
  id: number
  email: string
  username: string
  avatar: string | null
  oauthProvider: string | null
}

interface AuthResponse {
  token: string
  user: User
  expiresIn: number
}

const user = ref<User | null>(null)
const token = ref<string | null>(null)

export const useAuth = () => {
  const isAuthenticated = computed(() => !!token.value)

  // Load auth state from localStorage on init
  const loadAuth = () => {
    if (process.client) {
      const savedToken = localStorage.getItem('auth_token')
      const savedUser = localStorage.getItem('auth_user')
      
      if (savedToken && savedUser) {
        token.value = savedToken
        user.value = JSON.parse(savedUser)
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
        return { success: true, user: response.data }
      }
      
      throw new Error('Registration failed')
    } catch (error: any) {
      const message = error.data?.error?.message || error.message || 'Registration failed'
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

        // Save to localStorage
        if (process.client) {
          localStorage.setItem('auth_token', response.data.token)
          localStorage.setItem('auth_user', JSON.stringify(response.data.user))
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

    if (process.client) {
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
    loadAuth,
    register,
    login,
    logout,
    getAuthHeader
  }
}

