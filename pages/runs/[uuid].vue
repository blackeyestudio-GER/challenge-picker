<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Icon } from '#components'
import type { PublicRunPlaythrough, PublicRunResponse, PublicRunRule, PublicRunHistoryEntry } from '~/composables/usePlaythrough'
import { extractErrorMessage } from '~/utils/errorHandler'

const route = useRoute()
const config = useRuntimeConfig()
const { loadAuth, isAuthenticated } = useAuth()
const uuid = route.params.uuid as string

const playthrough = ref<PublicRunPlaythrough | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const copied = ref(false)

onMounted(async () => {
  loadAuth()
  await loadPlaythrough()
})

const loadPlaythrough = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $fetch<PublicRunResponse>(
      `${config.public.apiBase}/playthrough/public/${uuid}`
    )
    playthrough.value = response.data.playthrough
  } catch (err: unknown) {
    error.value = extractErrorMessage(err, 'Failed to load playthrough')
    console.error('Failed to load playthrough:', err)
  } finally {
    loading.value = false
  }
}

const formatDuration = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  if (hours > 0) {
    return `${hours}h ${mins}m ${secs}s`
  }
  return `${mins}m ${secs}s`
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const shareUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return `${window.location.origin}/runs/${uuid}`
  }
  return ''
})

const copyShareLink = async () => {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(shareUrl.value)
      copied.value = true
      setTimeout(() => {
        copied.value = false
      }, 2000)
    } catch (err) {
      console.error('Failed to copy:', err)
    }
  }
}

const extractVideoId = (url: string | null): { platform: 'youtube' | 'twitch' | null; id: string | null } => {
  if (!url) return { platform: null, id: null }
  
  // YouTube patterns
  const youtubePatterns = [
    /youtube\.com\/watch\?v=([\w-]+)/,
    /youtu\.be\/([\w-]+)/,
    /youtube\.com\/embed\/([\w-]+)/,
  ]
  
  for (const pattern of youtubePatterns) {
    const match = url.match(pattern)
    if (match) {
      return { platform: 'youtube', id: match[1] }
    }
  }
  
  // Twitch patterns
  const twitchPatterns = [
    /twitch\.tv\/videos\/(\d+)/,
    /twitch\.tv\/[\w-]+\/clip\/([\w-]+)/,
  ]
  
  for (const pattern of twitchPatterns) {
    const match = url.match(pattern)
    if (match) {
      return { platform: 'twitch', id: match[1] }
    }
  }
  
  return { platform: null, id: null }
}

const usedRules = computed(() => playthrough.value?.usedRules ?? [])
const ruleHistory = computed(() => playthrough.value?.ruleHistory ?? [])

const formatHistoryDate = (dateString: string | null) => {
  if (!dateString) return 'Not recorded'

  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getRuleIconName = (rule: PublicRunRule | PublicRunHistoryEntry) => {
  switch (rule.type) {
    case 'legendary':
      return 'heroicons:star'
    case 'court':
      return 'heroicons:user-circle'
    case 'counter':
      return 'heroicons:calculator'
    default:
      return 'heroicons:bolt'
  }
}

const getRuleIconClass = (rule: PublicRunRule | PublicRunHistoryEntry) => ({
  'text-yellow-500': rule.type === 'legendary',
  'text-purple-500': rule.type === 'court',
  'text-red-500': rule.type === 'counter',
  'text-cyan': !rule.type || rule.type === 'basic'
})

const finishedRunLabel = computed(() => {
  if (!playthrough.value) return 'Unknown'
  if (playthrough.value.finishedRun === true) return 'Completed'
  if (playthrough.value.finishedRun === false) return 'Stopped early'
  return 'Not rated'
})

const recommendedLabel = computed(() => {
  if (!playthrough.value) return 'Unknown'
  if (playthrough.value.recommended === 1) return 'Recommended'
  if (playthrough.value.recommended === 0) return 'Neutral'
  if (playthrough.value.recommended === -1) return 'Not recommended'
  return 'Not rated'
})

const finishedRunIconClass = computed(() => {
  if (!playthrough.value) return 'text-gray-400'
  if (playthrough.value.finishedRun === true) return 'text-green-400'
  if (playthrough.value.finishedRun === false) return 'text-red-400'
  return 'text-gray-400'
})

const recommendedIconClass = computed(() => {
  if (!playthrough.value) return 'text-gray-400'
  if (playthrough.value.recommended === 1) return 'text-green-400'
  if (playthrough.value.recommended === 0) return 'text-yellow-400'
  if (playthrough.value.recommended === -1) return 'text-red-400'
  return 'text-gray-400'
})

const finishedRunCardClass = computed(() => {
  if (!playthrough.value) return 'bg-gray-800/80 border-gray-700'
  if (playthrough.value.finishedRun === true) return 'bg-green-500/10 border-green-500/40'
  if (playthrough.value.finishedRun === false) return 'bg-red-500/10 border-red-500/40'
  return 'bg-gray-800/80 border-gray-700'
})

const recommendationCardClass = computed(() => {
  if (!playthrough.value) return 'bg-gray-800/80 border-gray-700'
  if (playthrough.value.recommended === 1) return 'bg-green-500/10 border-green-500/40'
  if (playthrough.value.recommended === 0) return 'bg-yellow-500/10 border-yellow-500/40'
  if (playthrough.value.recommended === -1) return 'bg-red-500/10 border-red-500/40'
  return 'bg-gray-800/80 border-gray-700'
})

const playThisChallengeUrl = computed(() => {
  const gameId = playthrough.value?.game.id
  const rulesetId = playthrough.value?.ruleset.id
  if (!gameId || !rulesetId) {
    return null
  }

  return `/playthrough/game/${gameId}/ruleset/${rulesetId}`
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-black py-8 px-4">
    <div class="max-w-5xl mx-auto">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan mb-4"/>
        <p class="text-white text-lg">Loading challenge run...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-20">
        <Icon name="heroicons:exclamation-triangle" class="w-24 h-24 mx-auto text-red-500 mb-4" />
        <h1 class="text-3xl font-bold text-white mb-2">Playthrough Not Found</h1>
        <p class="text-gray-400 mb-6">{{ error }}</p>
        <NuxtLink
          to="/"
          class="inline-flex items-center gap-2 px-6 py-3 bg-cyan hover:bg-cyan-dark text-white font-bold rounded-lg transition-all"
        >
          <Icon name="heroicons:home" class="w-5 h-5" />
          Go Home
        </NuxtLink>
      </div>

      <!-- Playthrough Details -->
      <div v-else-if="playthrough">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between mb-4">
            <NuxtLink
              to="/runs"
              class="text-cyan hover:text-cyan-light flex items-center gap-2"
            >
              <Icon name="heroicons:arrow-left" class="w-5 h-5" />
              Back to Runs
            </NuxtLink>
            <button
              class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-cyan text-white rounded-lg transition-all"
              @click="copyShareLink"
            >
              <Icon :name="copied ? 'heroicons:check' : 'heroicons:share'" class="w-5 h-5" />
              {{ copied ? 'Copied!' : 'Share Run' }}
            </button>
          </div>

          <div v-if="isAuthenticated && playThisChallengeUrl" class="mb-4">
            <NuxtLink
              :to="playThisChallengeUrl"
              class="inline-flex items-center gap-2 px-4 py-2 bg-cyan hover:bg-cyan-dark text-white font-semibold rounded-lg transition-all"
            >
              <Icon name="heroicons:play-circle" class="w-5 h-5" />
              Play This Challenge
            </NuxtLink>
          </div>
          
          <h1 class="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
            {{ playthrough.game.name }}
          </h1>
          <p class="text-2xl text-gray-300">{{ playthrough.ruleset.name }}</p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Game Image -->
          <div class="lg:col-span-1">
            <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg overflow-hidden">
              <img
                v-if="playthrough.game.imageUrl"
                :src="playthrough.game.imageUrl"
                :alt="playthrough.game.name"
                class="w-full h-64 object-cover"
              >
              <div v-else class="w-full h-64 bg-gray-900 flex items-center justify-center">
                <Icon name="heroicons:photo" class="w-16 h-16 text-gray-600" />
              </div>
            </div>
          </div>

          <!-- Run Stats -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Player Info -->
            <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
              <div class="flex items-center gap-4">
                <div v-if="playthrough.user.avatarUrl" class="w-16 h-16 rounded-full overflow-hidden border-2 border-cyan">
                  <img :src="playthrough.user.avatarUrl" :alt="playthrough.user.username" class="w-full h-full object-cover" >
                </div>
                <div v-else class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center border-2 border-cyan">
                  <Icon name="heroicons:user" class="w-8 h-8 text-gray-400" />
                </div>
                <div>
                  <p class="text-sm text-gray-400">Completed by</p>
                  <p class="text-2xl font-bold text-white">{{ playthrough.user.username }}</p>
                </div>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-2">
                  <Icon name="heroicons:clock" class="w-6 h-6 text-cyan" />
                  <p class="text-sm text-gray-400">Duration</p>
                </div>
                <p class="text-2xl font-bold text-white">{{ formatDuration(playthrough.totalDuration) }}</p>
              </div>

              <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-2">
                  <Icon name="heroicons:calendar" class="w-6 h-6 text-cyan" />
                  <p class="text-sm text-gray-400">Completed</p>
                </div>
                <p class="text-lg font-bold text-white">{{ formatDate(playthrough.endedAt) }}</p>
              </div>

              <div :class="['backdrop-blur-sm border rounded-lg p-6', finishedRunCardClass]">
                <div class="flex items-center gap-3 mb-2">
                  <Icon name="heroicons:flag" :class="['w-6 h-6', finishedRunIconClass]" />
                  <p class="text-sm text-gray-400">Run Result</p>
                </div>
                <p class="text-lg font-bold text-white">{{ finishedRunLabel }}</p>
              </div>

              <div :class="['backdrop-blur-sm border rounded-lg p-6', recommendationCardClass]">
                <div class="flex items-center gap-3 mb-2">
                  <Icon name="heroicons:hand-thumb-up" :class="['w-6 h-6', recommendedIconClass]" />
                  <p class="text-sm text-gray-400">Player Feedback</p>
                </div>
                <p class="text-lg font-bold text-white">{{ recommendedLabel }}</p>
              </div>
            </div>

            <!-- Video Link -->
            <div v-if="playthrough.videoUrl" class="bg-gradient-to-r from-cyan/10 to-magenta/10 border border-cyan/40 rounded-lg p-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="flex items-center gap-3 mb-2">
                    <Icon
                      :name="extractVideoId(playthrough.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:video-camera'"
                      class="w-6 h-6"
                      :class="extractVideoId(playthrough.videoUrl).platform === 'youtube' ? 'text-red-500' : 'text-purple-500'"
                    />
                    <p class="text-sm text-gray-300">Watch the full run</p>
                  </div>
                  <p class="text-lg font-semibold text-white">
                    {{ extractVideoId(playthrough.videoUrl).platform === 'youtube' ? 'YouTube' : 'Twitch' }} Recording
                  </p>
                </div>
                <a
                  :href="playthrough.videoUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-6 py-3 bg-cyan hover:bg-cyan-dark text-white font-bold rounded-lg transition-all flex items-center gap-2"
                >
                  <Icon name="heroicons:play" class="w-5 h-5" />
                  Watch Now
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Ruleset Description -->
        <div v-if="playthrough.ruleset.description" class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 mb-8">
          <div class="flex items-center gap-3 mb-3">
            <Icon name="heroicons:document-text" class="w-6 h-6 text-cyan" />
            <h2 class="text-xl font-bold text-white">About This Challenge</h2>
          </div>
          <p class="text-gray-300">{{ playthrough.ruleset.description }}</p>
        </div>

        <!-- Rules Used -->
        <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 mb-8">
          <div class="flex items-center gap-3 mb-6">
            <Icon name="heroicons:list-bullet" class="w-6 h-6 text-cyan" />
            <h2 class="text-xl font-bold text-white">Rules Used ({{ usedRules.length }})</h2>
          </div>
          
          <div v-if="usedRules.length === 0" class="text-center py-8 text-gray-400">
            No stored rules were found for this run
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="rule in usedRules"
              :key="rule.id"
              class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 hover:border-cyan/40 transition-all"
            >
              <div class="flex items-start gap-3">
                <Icon
                  :name="getRuleIconName(rule)"
                  class="w-5 h-5 flex-shrink-0 mt-1"
                  :class="getRuleIconClass(rule)"
                />
                <div>
                  <h3 class="font-bold text-white mb-1">{{ rule.name }}</h3>
                  <p class="text-sm text-gray-400">{{ rule.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
          <div class="flex items-center gap-3 mb-6">
            <Icon name="heroicons:clock" class="w-6 h-6 text-cyan" />
            <h2 class="text-xl font-bold text-white">Rule History ({{ ruleHistory.length }})</h2>
          </div>

          <div v-if="ruleHistory.length === 0" class="text-center py-8 text-gray-400">
            No called-rule history was recorded for this run
          </div>

          <div v-else class="space-y-4">
            <article
              v-for="entry in ruleHistory"
              :key="`${entry.ruleId}-${entry.startedAt || entry.createdAt || entry.name}`"
              class="bg-gray-900/50 border border-gray-700 rounded-lg p-4"
            >
              <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex items-start gap-3">
                  <Icon
                    :name="getRuleIconName(entry)"
                    class="w-5 h-5 flex-shrink-0 mt-1"
                    :class="getRuleIconClass(entry)"
                  />
                  <div>
                    <h3 class="font-bold text-white mb-1">{{ entry.name }}</h3>
                    <p v-if="entry.description" class="text-sm text-gray-400">{{ entry.description }}</p>
                  </div>
                </div>
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
                  :class="entry.completed ? 'bg-green-500/20 text-green-300' : entry.isActive ? 'bg-cyan/20 text-cyan-200' : 'bg-gray-700 text-gray-300'"
                >
                  {{ entry.completed ? 'Completed' : entry.isActive ? 'Active at end' : 'Inactive' }}
                </span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div>
                  <p class="text-gray-500 uppercase tracking-wide text-xs mb-1">Started</p>
                  <p class="text-white">{{ formatHistoryDate(entry.startedAt || entry.createdAt) }}</p>
                </div>
                <div>
                  <p class="text-gray-500 uppercase tracking-wide text-xs mb-1">Completed</p>
                  <p class="text-white">{{ formatHistoryDate(entry.completedAt) }}</p>
                </div>
                <div v-if="entry.currentAmount !== null">
                  <p class="text-gray-500 uppercase tracking-wide text-xs mb-1">Counter</p>
                  <p class="text-white">{{ entry.currentAmount }}</p>
                </div>
              </div>
            </article>
          </div>
        </div>

        <!-- Footer CTA -->
        <div class="mt-12 text-center py-12 border-t border-gray-800">
          <h2 class="text-3xl font-bold text-white mb-4">Ready for Your Own Challenge?</h2>
          <p class="text-gray-300 mb-6">Join the community and start your challenge run today!</p>
          <NuxtLink
            to="/"
            class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all"
          >
            <Icon name="heroicons:play" class="w-6 h-6" />
            Get Started
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
