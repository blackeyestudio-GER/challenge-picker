import { ref } from 'vue'
import type { BrowseAvailabilityResponse, SentChallengeGroup, SentChallengeItem } from '~/generated/api-contracts'
import { useAuth } from '~/composables/useAuth'
import { useChallenges } from '~/composables/useChallenges'
import { usePlaythrough } from '~/composables/usePlaythrough'

export const useDashboardPage = () => {
  const { user, isAdmin, loadAuth, getAuthHeader } = useAuth()
  const { activePlaythrough, fetchActivePlaythrough } = usePlaythrough()
  const { fetchSentChallenges } = useChallenges()
  const { stats, fetchUserStats, loading: statsLoading } = useUserStats()
  const { notifyApiError } = useNotify()

  const loading = ref(true)
  const browseRunsAvailable = ref(false)
  const sentChallenges = ref<SentChallengeGroup[]>([])
  const challengesLoading = ref(false)
  const dashboardError = ref<string | null>(null)

  const loadSentChallenges = async () => {
    challengesLoading.value = true
    try {
      const data = await fetchSentChallenges()
      sentChallenges.value = Array.isArray(data) ? data : []
    } catch (err: unknown) {
      sentChallenges.value = []
      notifyApiError(err, 'Failed to load sent challenges')
    } finally {
      challengesLoading.value = false
    }
  }

  const bootstrap = async () => {
    loadAuth()

    try {
      await Promise.all([
        fetchActivePlaythrough(),
        loadSentChallenges(),
        fetchUserStats()
      ])

      try {
        const featureResponse = await $fetch<{ success: boolean; data: { feature: string; enabled: boolean } }>(
          '/api/features/browse_community_runs',
          {
            headers: getAuthHeader()
          }
        )

        const featureEnabled = featureResponse.data.enabled
        if (featureEnabled) {
          const dataResponse = await $fetch<BrowseAvailabilityResponse>(
            '/api/playthrough/browse/availability',
            {
              headers: getAuthHeader()
            }
          )
          browseRunsAvailable.value = dataResponse.data.available
        } else {
          browseRunsAvailable.value = false
        }
      } catch {
        browseRunsAvailable.value = false
      }
    } catch (err: unknown) {
      dashboardError.value = 'Some dashboard data could not be loaded.'
      notifyApiError(err, 'Failed to load dashboard data')
    } finally {
      loading.value = false
    }
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  }

  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case 'accepted':
        return 'dashboard-page__challenge-badge--accepted'
      case 'pending':
        return 'dashboard-page__challenge-badge--pending'
      case 'declined':
        return 'dashboard-page__challenge-badge--declined'
      default:
        return 'dashboard-page__challenge-badge--default'
    }
  }

  const getAcceptedCount = (challenges: SentChallengeItem[]) => challenges.filter((c) => c.status === 'accepted').length

  return {
    user,
    isAdmin,
    activePlaythrough,
    stats,
    statsLoading,
    loading,
    browseRunsAvailable,
    sentChallenges,
    challengesLoading,
    dashboardError,
    bootstrap,
    formatDate,
    getStatusBadgeClass,
    getAcceptedCount
  }
}
