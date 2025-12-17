import type { Ref } from 'vue'
import type { TimerDesign, StatusDesign, RulesDesign } from '~/types/obs-designs'

export interface ObsPreferences {
  showTimerInSetup: boolean
  showTimerInActive: boolean
  showTimerInPaused: boolean
  showTimerInCompleted: boolean

  showRulesInSetup: boolean
  showRulesInActive: boolean
  showRulesInPaused: boolean
  showRulesInCompleted: boolean

  showStatusInSetup: boolean
  showStatusInActive: boolean
  showStatusInPaused: boolean
  showStatusInCompleted: boolean

  timerPosition: 'none' | 'on_card' | 'below_card'
  timerDesign: TimerDesign
  statusDesign: StatusDesign
  rulesDesign: RulesDesign
}

export const useObsPreferences = () => {
  const config = useRuntimeConfig()
  const { setAuthHeader } = useAuth()

  const preferences: Ref<ObsPreferences | null> = ref(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch user's OBS preferences
   */
  const fetchPreferences = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: ObsPreferences }>(
        `${config.public.apiBase}/api/users/me/obs-preferences`,
        {
          headers: setAuthHeader()
        }
      )

      if (response.success) {
        preferences.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to load OBS preferences'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update user's OBS preferences
   */
  const updatePreferences = async (updates: Partial<ObsPreferences>): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: ObsPreferences }>(
        `${config.public.apiBase}/api/users/me/obs-preferences`,
        {
          method: 'PUT',
          headers: setAuthHeader(),
          body: updates
        }
      )

      if (response.success) {
        preferences.value = response.data
      }
    } catch (err: any) {
      error.value = err.data?.error?.message || 'Failed to update OBS preferences'
      throw err
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

