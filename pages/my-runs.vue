<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import type { Playthrough } from '~/composables/usePlaythrough'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { token } = useAuth()
const { addVideoUrl, updatePlaythroughFeedback } = usePlaythrough()

const completedRuns = ref<Playthrough[]>([])
const loading = ref(true)
const editingVideoUrl = ref<number | null>(null)
const videoUrlInput = ref('')
const savingVideoUrl = ref(false)
const updatingFeedback = ref<number | null>(null)

onMounted(async () => {
  await loadCompletedRuns()
})

const loadCompletedRuns = async () => {
  loading.value = true
  try {
    const response = await $fetch<{ success: boolean; data: { playthroughs: Playthrough[] } }>(
      '/api/playthrough/completed',
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

const updateFeedback = async (run: Playthrough, field: 'finishedRun' | 'recommended', value: boolean | number | null) => {
  updatingFeedback.value = run.id
  try {
    let updated: Playthrough
    if (field === 'finishedRun') {
      updated = await updatePlaythroughFeedback(run.uuid, value as boolean | null, null)
    } else {
      updated = await updatePlaythroughFeedback(run.uuid, null, value as number | null)
    }
    
    // Update local state
    const index = completedRuns.value.findIndex(r => r.id === run.id)
    if (index !== -1) {
      completedRuns.value[index].finishedRun = updated.finishedRun
      completedRuns.value[index].recommended = updated.recommended
    }
  } catch (err: any) {
    alert(err || 'Failed to update feedback')
  } finally {
    updatingFeedback.value = null
  }
}

</script>

<template>
  <div class="runs-page">
    <!-- Header -->
    <div class="runs-page__header">
      <h1 class="runs-page__title">
        My Completed Runs
      </h1>
      <p class="runs-page__description">View your completed challenge runs and share your videos</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="runs-page__loading">
      <div class="runs-page__loading-spinner"></div>
      <p class="runs-page__loading-text">Loading your runs...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="completedRuns.length === 0" class="runs-page__empty">
      <Icon name="heroicons:trophy" class="runs-page__empty-icon" />
      <p class="runs-page__empty-message">You haven't completed any challenge runs yet</p>
      <NuxtLink
        to="/playthrough/new"
        class="runs-page__empty-button"
      >
        <Icon name="heroicons:play" class="runs-page__empty-icon-small" />
        Start Your First Run
      </NuxtLink>
    </div>

    <!-- Completed Runs List -->
    <div v-else class="runs-page__list">
      <div
        v-for="run in completedRuns"
        :key="run.id"
        class="runs-page__run-card"
      >
        <div class="runs-page__run-header">
          <div class="runs-page__run-content">
            <div class="runs-page__run-title-row">
              <Icon name="heroicons:trophy" class="runs-page__run-icon" />
              <h2 class="runs-page__run-game">{{ run.gameName }}</h2>
            </div>
            <p class="runs-page__run-ruleset">{{ run.rulesetName }}</p>
            <div class="runs-page__run-meta">
              <span>Completed: {{ formatDate(run.endedAt!) }}</span>
              <span>Duration: {{ formatDuration(run.totalDuration) }}</span>
            </div>
          </div>
          
          <div class="runs-page__run-actions">
            <NuxtLink
              :to="`/runs/${run.uuid}`"
              class="runs-page__run-button runs-page__run-button--share"
            >
              <Icon name="heroicons:share" class="runs-page__run-button-icon" />
              Share
            </NuxtLink>
          </div>
        </div>

        <!-- Video URL Section -->
        <div class="runs-page__video-section">
          <!-- Has Video URL -->
          <div v-if="run.videoUrl && editingVideoUrl !== run.id" class="runs-page__video-content">
            <div class="runs-page__video-link-wrapper">
              <Icon
                :name="extractVideoId(run.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:video-camera'"
                class="runs-page__video-icon"
                :class="extractVideoId(run.videoUrl).platform === 'youtube' ? 'runs-page__video-icon--youtube' : 'runs-page__video-icon--twitch'"
              />
              <a
                :href="run.videoUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="runs-page__video-link"
              >
                {{ extractVideoId(run.videoUrl).platform === 'youtube' ? 'YouTube' : 'Twitch' }} Video
              </a>
            </div>
            <button
              @click="startEditingVideoUrl(run)"
              class="runs-page__video-edit-button"
            >
              <Icon name="heroicons:pencil" class="w-4 h-4" />
              Edit
            </button>
          </div>

          <!-- No Video URL -->
          <div v-else-if="!run.videoUrl && editingVideoUrl !== run.id">
            <button
              @click="startEditingVideoUrl(run)"
              class="runs-page__video-add-button"
            >
              <Icon name="heroicons:plus" class="w-5 h-5" />
              Add Video Link
            </button>
          </div>

          <!-- Editing Video URL -->
          <div v-else class="runs-page__video-form">
            <div>
              <label class="runs-page__video-label">
                Video URL (YouTube or Twitch)
              </label>
              <input
                v-model="videoUrlInput"
                type="url"
                placeholder="https://youtube.com/watch?v=... or https://twitch.tv/videos/..."
                class="runs-page__video-input"
                :disabled="savingVideoUrl"
              />
              <p class="runs-page__video-hint">
                Supported: YouTube (watch, shorts, embed) and Twitch (videos, clips)
              </p>
            </div>
            <div class="runs-page__video-actions">
              <button
                @click="saveVideoUrl(run)"
                :disabled="savingVideoUrl || !videoUrlInput.trim()"
                class="runs-page__video-save-button"
              >
                <Icon
                  :name="savingVideoUrl ? 'heroicons:arrow-path' : 'heroicons:check'"
                  class="runs-page__video-icon-small"
                  :class="{'runs-page__video-icon-small--spinning': savingVideoUrl}"
                />
                {{ savingVideoUrl ? 'Saving...' : 'Save' }}
              </button>
              <button
                @click="cancelEditingVideoUrl"
                :disabled="savingVideoUrl"
                class="runs-page__video-cancel-button"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>

        <!-- Feedback Section -->
        <div class="runs-page__feedback-section">
          <h3 class="runs-page__feedback-title">Run Feedback</h3>
          <div class="runs-page__feedback-grid">
            <!-- Finished Run -->
            <div class="runs-page__feedback-item">
              <label class="runs-page__feedback-label">Did you finish this run?</label>
              <div class="runs-page__feedback-buttons">
                <button
                  @click="updateFeedback(run, 'finishedRun', true)"
                  :disabled="updatingFeedback === run.id"
                  :class="[
                    'runs-page__feedback-button',
                    run.finishedRun === true ? 'runs-page__feedback-button--active' : '',
                    run.finishedRun === true ? 'runs-page__feedback-button--yes' : ''
                  ]"
                  type="button"
                >
                  <Icon name="heroicons:check-circle" class="runs-page__feedback-icon" />
                  Yes
                </button>
                <button
                  @click="updateFeedback(run, 'finishedRun', false)"
                  :disabled="updatingFeedback === run.id"
                  :class="[
                    'runs-page__feedback-button',
                    run.finishedRun === false ? 'runs-page__feedback-button--active' : '',
                    run.finishedRun === false ? 'runs-page__feedback-button--no' : ''
                  ]"
                  type="button"
                >
                  <Icon name="heroicons:x-circle" class="runs-page__feedback-icon" />
                  No
                </button>
              </div>
            </div>

            <!-- Recommended -->
            <div class="runs-page__feedback-item">
              <label class="runs-page__feedback-label">Would you recommend this challenge?</label>
              <div class="runs-page__feedback-buttons">
                <button
                  @click="updateFeedback(run, 'recommended', 1)"
                  :disabled="updatingFeedback === run.id"
                  :class="[
                    'runs-page__feedback-button',
                    run.recommended === 1 ? 'runs-page__feedback-button--active' : '',
                    run.recommended === 1 ? 'runs-page__feedback-button--yes' : ''
                  ]"
                  type="button"
                >
                  <Icon name="heroicons:check-circle" class="runs-page__feedback-icon" />
                  Yes
                </button>
                <button
                  @click="updateFeedback(run, 'recommended', 0)"
                  :disabled="updatingFeedback === run.id"
                  :class="[
                    'runs-page__feedback-button',
                    run.recommended === 0 ? 'runs-page__feedback-button--active' : ''
                  ]"
                  type="button"
                >
                  <Icon name="heroicons:minus-circle" class="runs-page__feedback-icon" />
                  Neutral
                </button>
                <button
                  @click="updateFeedback(run, 'recommended', -1)"
                  :disabled="updatingFeedback === run.id"
                  :class="[
                    'runs-page__feedback-button',
                    run.recommended === -1 ? 'runs-page__feedback-button--active' : '',
                    run.recommended === -1 ? 'runs-page__feedback-button--no' : ''
                  ]"
                  type="button"
                >
                  <Icon name="heroicons:x-circle" class="runs-page__feedback-icon" />
                  No
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

