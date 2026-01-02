<template>
  <div v-if="pendingChallenges.length > 0" class="bg-gradient-to-r from-purple-900/90 to-pink-900/90 backdrop-blur-md border-b border-purple-700/50">
    <div class="max-w-7xl mx-auto px-4 py-3">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <svg class="w-6 h-6 text-yellow-400 flex-shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
          </svg>
          <div class="min-w-0">
            <p class="text-white font-medium">
              {{ pendingChallenges.length === 1 ? 'You have a challenge!' : `You have ${pendingChallenges.length} challenges!` }}
            </p>
            <p class="text-purple-200 text-sm truncate">
              {{ pendingChallenges[0].challenger.displayName }} challenged you to {{ pendingChallenges[0].playthrough.ruleset.game.name }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <button
            @click="showChallengesModal = true"
            class="px-4 py-2 bg-white text-purple-900 rounded-lg hover:bg-purple-50 transition font-medium text-sm"
          >
            View
          </button>
          <button
            @click="dismiss"
            class="p-2 text-purple-200 hover:text-white rounded-lg hover:bg-white/10 transition"
            aria-label="Dismiss"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Challenges Modal -->
    <div
      v-if="showChallengesModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4"
      @click.self="showChallengesModal = false"
    >
      <div class="bg-gray-900 rounded-lg p-6 max-w-2xl w-full border border-purple-500/30 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
            </svg>
            Pending Challenges
          </h2>
          <button
            @click="showChallengesModal = false"
            class="text-gray-400 hover:text-white transition"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <div
            v-for="challenge in pendingChallenges"
            :key="challenge.uuid"
            class="bg-gray-800 rounded-lg p-5 border border-gray-700 hover:border-purple-500/50 transition"
          >
            <div class="flex items-start gap-4">
              <!-- Game Image -->
              <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700">
                <img
                  v-if="challenge.playthrough.ruleset.game.imageBase64"
                  :src="`data:image/jpeg;base64,${challenge.playthrough.ruleset.game.imageBase64}`"
                  :alt="challenge.playthrough.ruleset.game.name"
                  class="w-full h-full object-cover"
                />
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-white font-medium mb-1">
                  <span class="text-purple-400">{{ challenge.challenger.displayName }}</span> challenged you!
                </p>
                <p class="text-gray-300 text-sm mb-2">
                  <strong>{{ challenge.playthrough.ruleset.game.name }}</strong> - {{ challenge.playthrough.ruleset.name }}
                </p>
                <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                  <span class="px-2 py-1 bg-gray-700 rounded">{{ challenge.playthrough.ruleset.difficulty }}</span>
                  <span>{{ challenge.playthrough.maxConcurrentRules }} max rules</span>
                  <span>Expires {{ formatRelativeTime(challenge.expiresAt) }}</span>
                </div>

                <div v-if="respondingTo === challenge.uuid" class="flex gap-2">
                  <div class="flex-1 text-center py-2 text-gray-400">
                    <span v-if="responseAction === 'accept'">Accepting challenge...</span>
                    <span v-else>Declining challenge...</span>
                  </div>
                </div>

                <div v-else class="flex gap-2">
                  <button
                    @click="respondToChallenge(challenge.uuid, 'accept')"
                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium"
                  >
                    Accept
                  </button>
                  <button
                    @click="respondToChallenge(challenge.uuid, 'decline')"
                    class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition font-medium"
                  >
                    Decline
                  </button>
                </div>

                <div v-if="errorForChallenge === challenge.uuid" class="mt-2 p-2 bg-red-900/50 border border-red-700 rounded text-sm text-red-200">
                  {{ errorMessage }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="pendingChallenges.length === 0" class="text-center py-8 text-gray-400">
          No pending challenges
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const { challenges: pendingChallenges, fetchMyChallenges, respondToChallenge: respondToChallengeAPI } = useChallenges()
const { user } = useAuth()
const router = useRouter()

const showChallengesModal = ref(false)
const respondingTo = ref<string | null>(null)
const responseAction = ref<'accept' | 'decline' | null>(null)
const errorForChallenge = ref<string | null>(null)
const errorMessage = ref('')

// Fetch challenges on mount if user is logged in
onMounted(() => {
  if (user.value) {
    fetchMyChallenges().catch(() => {
      // Silently fail if no challenges
    })
  }
})

// Poll for new challenges every 30 seconds
let pollInterval: number | null = null

onMounted(() => {
  if (user.value) {
    pollInterval = window.setInterval(() => {
      if (user.value) {
        fetchMyChallenges().catch(() => {})
      }
    }, 30000) // Poll every 30 seconds
  }
})

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval)
  }
})

const respondToChallenge = async (challengeUuid: string, action: 'accept' | 'decline') => {
  respondingTo.value = challengeUuid
  responseAction.value = action
  errorForChallenge.value = null

  try {
    const result = await respondToChallengeAPI(challengeUuid, action)
    
    if (action === 'accept' && result.playthroughUuid) {
      // Redirect to the new playthrough
      showChallengesModal.value = false
      await router.push(`/play/${result.playthroughUuid}`)
    } else {
      // Just remove from list
      await fetchMyChallenges()
    }
  } catch (err: any) {
    errorForChallenge.value = challengeUuid
    errorMessage.value = err.message || 'Failed to respond to challenge'
  } finally {
    respondingTo.value = null
    responseAction.value = null
  }
}

const dismiss = () => {
  // Just hide the banner, don't remove challenges
  showChallengesModal.value = false
}

const formatRelativeTime = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = date.getTime() - now.getTime()
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(hours / 24)
  
  if (days > 0) return `in ${days} day${days > 1 ? 's' : ''}`
  if (hours > 0) return `in ${hours} hour${hours > 1 ? 's' : ''}`
  return 'soon'
}
</script>

