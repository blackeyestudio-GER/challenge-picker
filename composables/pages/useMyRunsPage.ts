import { computed, ref } from 'vue'
import type { CompletedPlaythroughsResponse, Playthrough } from '~/composables/usePlaythrough'
import { usePlaythrough } from '~/composables/usePlaythrough'

type FeedbackField = 'finishedRun' | 'recommended'

export const useMyRunsPage = () => {
  const { token } = useAuth()
  const { updatePlaythroughFeedback, deletePlaythrough } = usePlaythrough()
  const { success, notifyApiError } = useNotify()

  const completedRuns = ref<Playthrough[]>([])
  const loading = ref(true)
  const updatingFeedback = ref<string | null>(null)
  const deletingRunUuid = ref<string | null>(null)

  const sortedRuns = computed(() =>
    [...completedRuns.value].sort((a, b) => {
      const aTime = a.endedAt ? new Date(a.endedAt).getTime() : 0
      const bTime = b.endedAt ? new Date(b.endedAt).getTime() : 0
      return bTime - aTime
    })
  )

  const loadCompletedRuns = async () => {
    loading.value = true
    try {
      const response = await $fetch<CompletedPlaythroughsResponse>('/api/playthrough/completed', {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json',
        },
      })
      completedRuns.value = response.data.playthroughs
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to load completed runs')
    } finally {
      loading.value = false
    }
  }

  const bootstrap = async () => {
    await loadCompletedRuns()
  }

  const formatDuration = (seconds: number | null) => {
    if (!seconds) return 'N/A'
    const hours = Math.floor(seconds / 3600)
    const mins = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60

    if (hours > 0) {
      return `${hours}h ${mins}m ${secs}s`
    }

    return `${mins}m ${secs}s`
  }

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Unknown'

    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }

  const updateFeedback = async (run: Playthrough, field: FeedbackField, value: boolean | number | null) => {
    updatingFeedback.value = `${run.id}:${field}`

    try {
      const updated = await updatePlaythroughFeedback(
        run.uuid,
        field === 'finishedRun' ? value as boolean | null : null,
        field === 'recommended' ? value as number | null : null
      )

      const index = completedRuns.value.findIndex((item) => item.id === run.id)
      if (index !== -1) {
        completedRuns.value[index].finishedRun = updated.finishedRun
        completedRuns.value[index].recommended = updated.recommended
      }

      success('Feedback saved')
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to update feedback')
    } finally {
      updatingFeedback.value = null
    }
  }

  const isUpdating = (run: Playthrough, field: FeedbackField) => updatingFeedback.value === `${run.id}:${field}`

  const getFinishedStatusLabel = (run: Playthrough) => {
    if (run.finishedRun === true) return 'Completed'
    if (run.finishedRun === false) return 'Stopped'
    return null
  }

  const getRecommendationStatusLabel = (run: Playthrough) => {
    if (run.recommended === 1) return 'Recommended'
    if (run.recommended === 0) return 'Neutral'
    if (run.recommended === -1) return 'Not recommended'
    return null
  }

  const getVideoPlatformIcon = (url: string | null) => {
    if (!url) return 'heroicons:video-camera'
    if (url.includes('youtu')) return 'heroicons:play-circle'
    return 'heroicons:video-camera'
  }

  const canDeleteRun = (run: Playthrough) => run.totalDuration !== null && run.totalDuration < 180

  const isDeletingRun = (run: Playthrough) => deletingRunUuid.value === run.uuid

  const deleteShortRun = async (run: Playthrough) => {
    if (!canDeleteRun(run)) {
      return
    }

    deletingRunUuid.value = run.uuid

    try {
      await deletePlaythrough(run.uuid)
      completedRuns.value = completedRuns.value.filter((item) => item.uuid !== run.uuid)
      success('Short run deleted')
    } catch (err: unknown) {
      notifyApiError(err, 'Failed to delete run')
    } finally {
      deletingRunUuid.value = null
    }
  }

  const getFinishedIconClass = (run: Playthrough, value: boolean) => {
    if (run.finishedRun !== value) return ''
    return value ? 'my-runs-list__button-icon--yes' : 'my-runs-list__button-icon--no'
  }

  const getRecommendationIconClass = (run: Playthrough, value: number) => {
    if (run.recommended !== value) return ''
    if (value === 1) return 'my-runs-list__button-icon--yes'
    if (value === 0) return 'my-runs-list__button-icon--neutral'
    return 'my-runs-list__button-icon--no'
  }

  const getFinishedButtonStyle = (run: Playthrough, value: boolean) => {
    if (run.finishedRun !== value) return undefined

    return value
      ? {
          backgroundColor: '#065f46',
          borderColor: '#10b981',
          color: '#ecfdf5',
        }
      : {
          backgroundColor: '#7f1d1d',
          borderColor: '#ef4444',
          color: '#fef2f2',
        }
  }

  const getRecommendationButtonStyle = (run: Playthrough, value: number) => {
    if (run.recommended !== value) return undefined

    if (value === 1) {
      return {
        backgroundColor: '#065f46',
        borderColor: '#10b981',
        color: '#ecfdf5',
      }
    }

    if (value === 0) {
      return {
        backgroundColor: '#854d0e',
        borderColor: '#eab308',
        color: '#fefce8',
      }
    }

    return {
      backgroundColor: '#7f1d1d',
      borderColor: '#ef4444',
      color: '#fef2f2',
    }
  }

  return {
    loading,
    sortedRuns,
    bootstrap,
    formatDuration,
    formatDate,
    updateFeedback,
    isUpdating,
    getFinishedStatusLabel,
    getRecommendationStatusLabel,
    getVideoPlatformIcon,
    canDeleteRun,
    isDeletingRun,
    deleteShortRun,
    getFinishedIconClass,
    getRecommendationIconClass,
    getFinishedButtonStyle,
    getRecommendationButtonStyle
  }
}
