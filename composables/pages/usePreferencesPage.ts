import { computed, nextTick, ref } from 'vue'
import type { ObsPreferences } from '~/composables/useObsPreferences'
import type { ActiveDesignSetResponse } from '~/generated/api-contracts'
import { useDesigns, type DesignSet } from '~/composables/useDesigns'
import { getApiErrorMessage } from '~/composables/useApiError'

export const usePreferencesPage = () => {
  const { user, loadAuth } = useAuth()
  const { preferences, loading, error, fetchPreferences, updatePreferences } = useObsPreferences()
  const { fetchAvailableDesignSets, setActiveDesignSet, getActiveDesignSet, loading: designsLoading } = useDesigns()
  const { success, notifyApiError } = useNotify()

  const availableDesigns = ref<DesignSet[]>([])
  const activeDesignId = ref<number | null>(null)
  const designSuccess = ref('')
  const designError = ref('')

  const obsUrls = computed(() => {
    const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
    const userUuid = user.value?.uuid

    if (!userUuid) {
      return {
        timer: '',
        rules: '',
        status: ''
      }
    }

    return {
      timer: `${baseUrl}/play/${userUuid}/timer`,
      rules: `${baseUrl}/play/${userUuid}/rules`,
      status: `${baseUrl}/play/${userUuid}/status`
    }
  })

  const openUrl = (url: string) => {
    window.open(url, '_blank')
  }

  const loadAvailableDesigns = async () => {
    try {
      availableDesigns.value = await fetchAvailableDesignSets()
      const response: ActiveDesignSetResponse = await getActiveDesignSet()
      if (response.success) {
        activeDesignId.value = response.data.id
      }
    } catch (error: unknown) {
      designError.value = getApiErrorMessage(error, 'Failed to load card designs')
    }
  }

  const bootstrap = async () => {
    loadAuth()
    await nextTick()

    try {
      await fetchPreferences()
      await loadAvailableDesigns()
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to load preferences')
    }
  }

  const preferencePreviewTiles = (design: DesignSet): string[] => {
    const list = design.previewImages?.filter((s) => s && s.length > 0) ?? []
    if (list.length > 0) {
      return list.slice(0, 4)
    }
    if (design.previewImage) {
      return [design.previewImage]
    }
    return []
  }

  const handleDesignChange = async (designSetId: number) => {
    designError.value = ''
    designSuccess.value = ''

    try {
      await setActiveDesignSet(designSetId)
      activeDesignId.value = designSetId
      designSuccess.value = 'Card design updated successfully!'
      success('Card design updated successfully')
      setTimeout(() => {
        designSuccess.value = ''
      }, 3000)
    } catch (error: unknown) {
      designError.value = getApiErrorMessage(error, 'Failed to update card design')
      notifyApiError(error, 'Failed to update card design')
    }
  }

  const updatePref = async <K extends keyof ObsPreferences>(key: K, value: ObsPreferences[K]) => {
    if (!preferences.value) return

    try {
      await updatePreferences({ [key]: value })
      success('Preference updated')
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to update preference')
    }
  }

  return {
    user,
    preferences,
    loading,
    error,
    designsLoading,
    availableDesigns,
    activeDesignId,
    designSuccess,
    designError,
    obsUrls,
    bootstrap,
    openUrl,
    fetchPreferences,
    preferencePreviewTiles,
    handleDesignChange,
    updatePref
  }
}
