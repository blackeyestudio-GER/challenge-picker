import type { Ref } from 'vue'
import type { TimerDesign, StatusDesign, RulesDesign } from '~/types/obs-designs'

export interface ObsPreferences {
  showTimerInSetup: boolean
  showTimerInActive: boolean
  showTimerInPaused: boolean
  showTimerInCompleted: boolean

  showStatusInSetup: boolean
  showStatusInActive: boolean
  showStatusInPaused: boolean
  showStatusInCompleted: boolean

  timerPosition: 'none' | 'on_card' | 'below_card'
  timerDesign: TimerDesign
  statusDesign: StatusDesign
  rulesDesign: RulesDesign
  chromaKeyColor: string
}

export const useObsPreferences = () => {
  const config = useRuntimeConfig()
  const { getAuthHeader, loadAuth } = useAuth()
  const isDev = config.public.dev || false

  const preferences: Ref<ObsPreferences | null> = ref(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch user's OBS preferences with retry logic
   * If uuid is provided, uses public endpoint (no auth required)
   * Otherwise, uses authenticated endpoint
   */
  const fetchPreferences = async (uuid?: string, retryCount = 0): Promise<void> => {
    const maxRetries = 2
    loading.value = true
    error.value = null

    try {
      // Use public endpoint if UUID provided, otherwise authenticated endpoint
      const endpoint = uuid 
        ? `${config.public.apiBase}/play/${uuid}/preferences`
        : `${config.public.apiBase}/users/me/obs-preferences`
      
      // Ensure auth is loaded before making authenticated requests
      if (!uuid) {
        loadAuth()
      }
      
      const headers = uuid ? {} : getAuthHeader()
      
      const response = await $fetch<{ success: boolean; data: ObsPreferences }>(
        endpoint,
        {
          headers
        }
      )

      if (response.success) {
        preferences.value = response.data
        // Clear any previous errors on success
        error.value = null
      }
    } catch (err: any) {
      const status = err.statusCode || err.response?.status
      
      // Handle 401 specifically - user needs to re-authenticate
      if (status === 401) {
        error.value = 'Your session has expired. Please log in again.'
        // Don't retry 401 errors
        console.error('Authentication failed - token may be invalid or expired')
        
        // Clear invalid token if this is an authenticated request
        if (!uuid) {
          logout()
        }
        return
      }

      // Handle network/server errors with retry
      if (status >= 500 || !status) {
        if (retryCount < maxRetries) {
          console.warn(`Retrying OBS preferences fetch (attempt ${retryCount + 1}/${maxRetries})...`)
          await new Promise(resolve => setTimeout(resolve, 1000 * (retryCount + 1))) // Exponential backoff
          return fetchPreferences(retryCount + 1)
        }
      }

      // Set user-friendly error message
      const userMessage = err.data?.error?.message || 'Failed to load OBS preferences. Please try again.'
      error.value = userMessage
      
      // Log technical details for debugging
      console.error('Failed to fetch OBS preferences:', {
        status,
        message: err.message,
        data: err.data
      })
    } finally {
      loading.value = false
    }
  }

  /**
   * Update user's OBS preferences with error handling
   */
  const updatePreferences = async (updates: Partial<ObsPreferences>): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: ObsPreferences }>(
        `${config.public.apiBase}/users/me/obs-preferences`,
        {
          method: 'PUT',
          headers: getAuthHeader(),
          body: updates
        }
      )

      if (response.success) {
        preferences.value = response.data
        error.value = null // Clear any previous errors
      }
    } catch (err: any) {
      const status = err.statusCode || err.response?.status
      
      // Handle 401 specifically
      if (status === 401) {
        error.value = 'Your session has expired. Please log in again.'
        console.error('Authentication failed during update')
      } else if (status === 422 || status === 400) {
        // Validation errors
        error.value = err.data?.error?.message || 'Invalid preference values. Please check your input.'
      } else {
        error.value = err.data?.error?.message || 'Failed to update OBS preferences. Please try again.'
      }
      
      // Log for debugging
      console.error('Failed to update OBS preferences:', {
        status,
        message: err.message,
        data: err.data
      })
      
      throw err // Re-throw so the UI can handle it if needed
    } finally {
      loading.value = false
    }
  }

  return {
    preferences,
    loading,
    error,
    fetchPreferences,
    updatePreferences
  }
}

