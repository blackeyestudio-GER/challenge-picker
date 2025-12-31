<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Icon } from '#components'

const route = useRoute()
const uuid = route.params.uuid as string

interface PlaythroughData {
  uuid: string
  status: string
  startedAt: string
  endedAt: string
  totalDuration: number
  videoUrl: string | null
  game: {
    id: number
    name: string
    imageUrl: string | null
  }
  ruleset: {
    id: number
    name: string
    description: string | null
  }
  user: {
    username: string
    avatarUrl: string | null
  }
  activeRules: Array<{
    id: number
    name: string
    description: string
    type: string
  }>
}

const playthrough = ref<PlaythroughData | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const copied = ref(false)

onMounted(async () => {
  await loadPlaythrough()
})

const loadPlaythrough = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $fetch<{ success: boolean; data: { playthrough: PlaythroughData } }>(
      `http://localhost:8090/api/playthrough/public/${uuid}`
    )
    playthrough.value = response.data.playthrough
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to load playthrough'
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
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-black py-8 px-4">
    <div class="max-w-5xl mx-auto">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan mb-4"></div>
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
              to="/browse-runs"
              class="text-cyan hover:text-cyan-light flex items-center gap-2"
            >
              <Icon name="heroicons:arrow-left" class="w-5 h-5" />
              Back to Browse
            </NuxtLink>
            <button
              @click="copyShareLink"
              class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-cyan text-white rounded-lg transition-all"
            >
              <Icon :name="copied ? 'heroicons:check' : 'heroicons:share'" class="w-5 h-5" />
              {{ copied ? 'Copied!' : 'Share Run' }}
            </button>
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
              />
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
                  <img :src="playthrough.user.avatarUrl" :alt="playthrough.user.username" class="w-full h-full object-cover" />
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

        <!-- Active Rules -->
        <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6">
          <div class="flex items-center gap-3 mb-6">
            <Icon name="heroicons:list-bullet" class="w-6 h-6 text-cyan" />
            <h2 class="text-xl font-bold text-white">Challenge Rules ({{ playthrough.activeRules.length }})</h2>
          </div>
          
          <div v-if="playthrough.activeRules.length === 0" class="text-center py-8 text-gray-400">
            No active rules for this run
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="rule in playthrough.activeRules"
              :key="rule.id"
              class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 hover:border-cyan/40 transition-all"
            >
              <div class="flex items-start gap-3">
                <Icon
                  :name="rule.type === 'legendary' ? 'heroicons:star' : rule.type === 'court' ? 'heroicons:user-circle' : 'heroicons:bolt'"
                  class="w-5 h-5 flex-shrink-0 mt-1"
                  :class="{
                    'text-yellow-500': rule.type === 'legendary',
                    'text-purple-500': rule.type === 'court',
                    'text-cyan': rule.type === 'basic',
                    'text-red-500': rule.type === 'counter'
                  }"
                />
                <div>
                  <h3 class="font-bold text-white mb-1">{{ rule.name }}</h3>
                  <p class="text-sm text-gray-400">{{ rule.description }}</p>
                </div>
              </div>
            </div>
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

