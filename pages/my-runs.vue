<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import type { Playthrough } from '~/composables/usePlaythrough'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { token } = useAuth()
const { addVideoUrl } = usePlaythrough()

const completedRuns = ref<Playthrough[]>([])
const loading = ref(true)
const editingVideoUrl = ref<number | null>(null)
const videoUrlInput = ref('')
const savingVideoUrl = ref(false)

onMounted(async () => {
  await loadCompletedRuns()
})

const loadCompletedRuns = async () => {
  loading.value = true
  try {
    const response = await $fetch<{ success: boolean; data: { playthroughs: Playthrough[] } }>(
      'http://localhost:8090/api/playthrough/completed',
      {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json',
        },
      }
    )
    completedRuns.value = response.data.playthroughs
  } catch (err) {
    console.error('Failed to load completed runs:', err)
  } finally {
    loading.value = false
  }
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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const startEditingVideoUrl = (playthrough: Playthrough) => {
  editingVideoUrl.value = playthrough.id
  videoUrlInput.value = playthrough.videoUrl || ''
}

const cancelEditingVideoUrl = () => {
  editingVideoUrl.value = null
  videoUrlInput.value = ''
}

const saveVideoUrl = async (playthrough: Playthrough) => {
  savingVideoUrl.value = true
  try {
    await addVideoUrl(playthrough.uuid, videoUrlInput.value.trim())
    
    // Update local state
    const index = completedRuns.value.findIndex(r => r.id === playthrough.id)
    if (index !== -1) {
      completedRuns.value[index].videoUrl = videoUrlInput.value.trim() || null
    }
    
    editingVideoUrl.value = null
    videoUrlInput.value = ''
  } catch (err: any) {
    alert(err || 'Failed to save video URL')
  } finally {
    savingVideoUrl.value = false
  }
}

const extractVideoId = (url: string | null): { platform: 'youtube' | 'twitch' | null; id: string | null } => {
  if (!url) return { platform: null, id: null }
  
  // YouTube patterns
  const youtubePatterns = [
    /youtube\.com\/watch\?v=([\w-]+)/,
    /youtu\.be\/([\w-]+)/,
    /youtube\.com\/embed\/([\w-]+)/,
    /youtube\.com\/v\/([\w-]+)/
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
    /clips\.twitch\.tv\/([\w-]+)/
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
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
        My Completed Runs
      </h1>
      <p class="text-gray-300">View your completed challenge runs and share your videos</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan"></div>
      <p class="text-white mt-4">Loading your runs...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="completedRuns.length === 0" class="text-center py-12">
      <Icon name="heroicons:trophy" class="w-24 h-24 mx-auto text-gray-600 mb-4" />
      <p class="text-gray-400 text-lg mb-6">You haven't completed any challenge runs yet</p>
      <NuxtLink
        to="/playthrough/new"
        class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center gap-2"
      >
        <Icon name="heroicons:play" class="w-5 h-5" />
        Start Your First Run
      </NuxtLink>
    </div>

    <!-- Completed Runs List -->
    <div v-else class="space-y-6">
      <div
        v-for="run in completedRuns"
        :key="run.id"
        class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-cyan transition-all"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <Icon name="heroicons:trophy" class="w-6 h-6 text-yellow-500" />
              <h2 class="text-2xl font-bold text-white">{{ run.gameName }}</h2>
            </div>
            <p class="text-gray-400 mb-2">{{ run.rulesetName }}</p>
            <div class="flex items-center gap-4 text-sm text-gray-400">
              <span>Completed: {{ formatDate(run.endedAt!) }}</span>
              <span>Duration: {{ formatDuration(run.totalDuration) }}</span>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <NuxtLink
              :to="`/runs/${run.uuid}`"
              class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-all flex items-center gap-2"
            >
              <Icon name="heroicons:share" class="w-5 h-5" />
              Share
            </NuxtLink>
          </div>
        </div>

        <!-- Video URL Section -->
        <div class="mt-4 pt-4 border-t border-gray-700">
          <!-- Has Video URL -->
          <div v-if="run.videoUrl && editingVideoUrl !== run.id" class="flex items-center gap-4">
            <div class="flex items-center gap-2 flex-1">
              <Icon
                :name="extractVideoId(run.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:video-camera'"
                class="w-5 h-5"
                :class="extractVideoId(run.videoUrl).platform === 'youtube' ? 'text-red-500' : 'text-purple-500'"
              />
              <a
                :href="run.videoUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="text-cyan hover:text-cyan-light underline"
              >
                {{ extractVideoId(run.videoUrl).platform === 'youtube' ? 'YouTube' : 'Twitch' }} Video
              </a>
            </div>
            <button
              @click="startEditingVideoUrl(run)"
              class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-all flex items-center gap-2 text-sm"
            >
              <Icon name="heroicons:pencil" class="w-4 h-4" />
              Edit
            </button>
          </div>

          <!-- No Video URL -->
          <div v-else-if="!run.videoUrl && editingVideoUrl !== run.id">
            <button
              @click="startEditingVideoUrl(run)"
              class="px-4 py-2 bg-cyan hover:bg-cyan-dark text-white rounded-lg transition-all flex items-center gap-2"
            >
              <Icon name="heroicons:plus" class="w-5 h-5" />
              Add Video Link
            </button>
          </div>

          <!-- Editing Video URL -->
          <div v-else class="space-y-3">
            <div>
              <label class="block text-sm font-semibold text-white mb-2">
                Video URL (YouTube or Twitch)
              </label>
              <input
                v-model="videoUrlInput"
                type="url"
                placeholder="https://youtube.com/watch?v=... or https://twitch.tv/videos/..."
                class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan"
                :disabled="savingVideoUrl"
              />
              <p class="text-xs text-gray-400 mt-1">
                Supported: YouTube (watch, shorts, embed) and Twitch (videos, clips)
              </p>
            </div>
            <div class="flex gap-3">
              <button
                @click="saveVideoUrl(run)"
                :disabled="savingVideoUrl || !videoUrlInput.trim()"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Icon
                  :name="savingVideoUrl ? 'heroicons:arrow-path' : 'heroicons:check'"
                  class="w-5 h-5"
                  :class="{'animate-spin': savingVideoUrl}"
                />
                {{ savingVideoUrl ? 'Saving...' : 'Save' }}
              </button>
              <button
                @click="cancelEditingVideoUrl"
                :disabled="savingVideoUrl"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-all disabled:opacity-50"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

