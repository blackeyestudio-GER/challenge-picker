<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { Icon } from '#components'
import { useAuth } from '~/composables/useAuth'
import { extractErrorMessage } from '~/utils/errorHandler'
import type { CompletedPlaythroughsResponse, Playthrough } from '~/composables/usePlaythrough'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const config = useRuntimeConfig()
const { token } = useAuth()
const { addVideoUrl } = usePlaythrough()
const { success, notifyApiError } = useNotify()

const uuid = route.params.uuid as string
const run = ref<Playthrough | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const editingVideoUrl = ref(false)
const videoUrlInput = ref('')
const savingVideoUrl = ref(false)
const copied = ref(false)

onMounted(async () => {
  await loadRun()
})

const loadRun = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $fetch<CompletedPlaythroughsResponse>(
      `${config.public.apiBase}/playthrough/completed`,
      {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json',
        },
      }
    )

    const matchedRun = response.data.playthroughs.find(item => item.uuid === uuid) ?? null
    if (!matchedRun) {
      error.value = 'Completed run not found'
      return
    }

    run.value = matchedRun
    videoUrlInput.value = matchedRun.videoUrl || ''
  } catch (err: unknown) {
    error.value = extractErrorMessage(err, 'Failed to load completed run')
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

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Unknown'

  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const usedRules = computed(() => run.value?.usedRules.filter(rule => rule.isEnabled) ?? [])
const ruleHistory = computed(() => run.value?.ruleHistory ?? [])

const shareUrl = computed(() => {
  if (!import.meta.client) return ''
  return `${window.location.origin}/runs/${uuid}`
})

const copyShareLink = async () => {
  if (!import.meta.client) return

  try {
    await navigator.clipboard.writeText(shareUrl.value)
    copied.value = true
    success('Share link copied')
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to copy share link')
  }
}

const extractVideoId = (url: string | null): { platform: 'youtube' | 'external' | null } => {
  if (!url) return { platform: null }

  const youtubePatterns = [
    /youtube\.com\/watch\?v=([\w-]+)/,
    /youtu\.be\/([\w-]+)/,
    /youtube\.com\/embed\/([\w-]+)/,
    /youtube\.com\/v\/([\w-]+)/
  ]

  for (const pattern of youtubePatterns) {
    if (pattern.test(url)) {
      return { platform: 'youtube' }
    }
  }

  return { platform: 'external' }
}

const saveVideo = async () => {
  if (!run.value) return

  savingVideoUrl.value = true
  try {
    const response = await addVideoUrl(run.value.uuid, videoUrlInput.value.trim())
    run.value.videoUrl = response.videoUrl
    editingVideoUrl.value = false
    success(response.message)
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to save video link')
  } finally {
    savingVideoUrl.value = false
  }
}

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
</script>

<template>
  <div class="runs-page">
    <div v-if="loading" class="runs-page__loading">
      <div class="runs-page__loading-spinner" />
      <p class="runs-page__loading-text">Loading completed run...</p>
    </div>

    <div v-else-if="error || !run" class="runs-page__empty">
      <Icon name="heroicons:exclamation-triangle" class="runs-page__empty-icon" />
      <p class="runs-page__empty-message">{{ error || 'Run not found' }}</p>
      <NuxtLink to="/my-runs" class="runs-page__empty-button">
        <Icon name="heroicons:arrow-left" class="runs-page__empty-icon-small" />
        Back to my runs
      </NuxtLink>
    </div>

    <div v-else class="my-run-detail">
      <div class="runs-page__header my-run-detail__header">
        <div>
          <NuxtLink to="/my-runs" class="my-run-detail__back">
            <Icon name="heroicons:arrow-left" class="w-4 h-4" />
            Back to my runs
          </NuxtLink>
          <h1 class="runs-page__title">{{ run.gameName }}</h1>
          <p class="runs-page__description">{{ run.rulesetName }}</p>
        </div>

        <div class="my-run-detail__header-actions">
          <NuxtLink :to="`/runs/${run.uuid}`" class="runs-page__run-button my-run-detail__ghost">
            <Icon name="heroicons:eye" class="runs-page__run-button-icon" />
            View public page
          </NuxtLink>
          <button class="runs-page__run-button runs-page__run-button--share" @click="copyShareLink">
            <Icon :name="copied ? 'heroicons:check' : 'heroicons:link'" class="runs-page__run-button-icon" />
            {{ copied ? 'Copied' : 'Copy share link' }}
          </button>
        </div>
      </div>

      <div class="my-run-detail__grid">
        <div class="runs-page__run-card">
          <h2 class="my-run-detail__section-title">Run overview</h2>
          <dl class="my-run-detail__stats">
            <div>
              <dt>Completed</dt>
              <dd>{{ formatDate(run.endedAt) }}</dd>
            </div>
            <div>
              <dt>Duration</dt>
              <dd>{{ formatDuration(run.totalDuration) }}</dd>
            </div>
            <div>
              <dt>Finished</dt>
              <dd>{{ run.finishedRun === null ? 'Unrated' : run.finishedRun ? 'Yes' : 'No' }}</dd>
            </div>
            <div>
              <dt>Recommendation</dt>
              <dd>
                {{
                  run.recommended === 1
                    ? 'Yes'
                    : run.recommended === 0
                      ? 'Neutral'
                      : run.recommended === -1
                        ? 'No'
                        : 'Unrated'
                }}
              </dd>
            </div>
          </dl>
        </div>

        <div class="runs-page__run-card">
          <h2 class="my-run-detail__section-title">Video link</h2>

          <div v-if="run.videoUrl && !editingVideoUrl" class="runs-page__video-content">
            <div class="runs-page__video-link-wrapper">
              <Icon
                :name="extractVideoId(run.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:arrow-top-right-on-square'"
                class="runs-page__video-icon"
                :class="extractVideoId(run.videoUrl).platform === 'youtube' ? 'runs-page__video-icon--youtube' : 'runs-page__video-icon--external'"
              />
              <a :href="run.videoUrl" target="_blank" rel="noopener noreferrer" class="runs-page__video-link">
                {{ run.videoUrl }}
              </a>
            </div>
            <button class="runs-page__video-edit-button" @click="editingVideoUrl = true">
              <Icon name="heroicons:pencil" class="w-4 h-4" />
              Edit
            </button>
          </div>

          <div v-else-if="!editingVideoUrl">
            <button class="runs-page__video-add-button" @click="editingVideoUrl = true">
              <Icon name="heroicons:plus" class="w-5 h-5" />
              Add video link
            </button>
          </div>

          <div v-else class="runs-page__video-form">
            <div>
              <label class="runs-page__video-label">Video URL</label>
              <input
                v-model="videoUrlInput"
                type="url"
                class="runs-page__video-input"
                placeholder="https://youtube.com/watch?v=... or another public video URL"
                :disabled="savingVideoUrl"
              >
              <p class="runs-page__video-hint">Leave the field empty to remove the current link.</p>
            </div>

            <div class="runs-page__video-actions">
              <button
                class="runs-page__video-save-button"
                :disabled="savingVideoUrl"
                @click="saveVideo"
              >
                <Icon
                  :name="savingVideoUrl ? 'heroicons:arrow-path' : 'heroicons:check'"
                  class="runs-page__video-icon-small"
                  :class="{ 'runs-page__video-icon-small--spinning': savingVideoUrl }"
                />
                {{ savingVideoUrl ? 'Saving...' : 'Save' }}
              </button>
              <button
                class="runs-page__video-cancel-button"
                :disabled="savingVideoUrl"
                @click="editingVideoUrl = false"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="runs-page__run-card">
        <h2 class="my-run-detail__section-title">Rules used</h2>
        <div v-if="usedRules.length > 0" class="my-run-detail__rules">
          <span
            v-for="rule in usedRules"
            :key="rule.id"
            class="my-run-detail__rule-chip"
          >
            {{ rule.name }}
          </span>
        </div>
        <p v-else class="runs-page__description">No stored rules were found for this run.</p>
      </div>

      <div class="runs-page__run-card">
        <h2 class="my-run-detail__section-title">Rule history</h2>
        <div v-if="ruleHistory.length > 0" class="my-run-detail__history">
          <article
            v-for="entry in ruleHistory"
            :key="`${entry.ruleId}-${entry.startedAt || entry.createdAt || entry.name}`"
            class="my-run-detail__history-item"
          >
            <div class="my-run-detail__history-header">
              <div>
                <h3 class="my-run-detail__history-name">{{ entry.name }}</h3>
                <p v-if="entry.description" class="my-run-detail__history-description">{{ entry.description }}</p>
              </div>
              <span
                :class="[
                  'my-run-detail__history-badge',
                  entry.completed ? 'my-run-detail__history-badge--completed' : entry.isActive ? 'my-run-detail__history-badge--active' : 'my-run-detail__history-badge--inactive'
                ]"
              >
                {{ entry.completed ? 'Completed' : entry.isActive ? 'Active at end' : 'Inactive' }}
              </span>
            </div>

            <dl class="my-run-detail__history-meta">
              <div>
                <dt>Started</dt>
                <dd>{{ formatHistoryDate(entry.startedAt || entry.createdAt) }}</dd>
              </div>
              <div>
                <dt>Completed</dt>
                <dd>{{ formatHistoryDate(entry.completedAt) }}</dd>
              </div>
              <div v-if="entry.currentAmount !== null">
                <dt>Counter</dt>
                <dd>{{ entry.currentAmount }}</dd>
              </div>
            </dl>
          </article>
        </div>
        <p v-else class="runs-page__description">No called-rule history was recorded for this run.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.my-run-detail {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.my-run-detail__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1.5rem;
}

.my-run-detail__back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  color: var(--color-accent-primary);
  text-decoration: none;
}

.my-run-detail__header-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.my-run-detail__ghost {
  background-color: var(--color-bg-tertiary);
  color: var(--color-text-primary);
  border: 1px solid var(--color-border-secondary);
}

.my-run-detail__ghost:hover {
  border-color: var(--color-accent-primary);
  background-color: var(--color-bg-card);
}

.my-run-detail__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
}

.my-run-detail__section-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 1rem;
}

.my-run-detail__stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.my-run-detail__stats dt {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--color-text-muted);
  margin-bottom: 0.35rem;
}

.my-run-detail__stats dd {
  margin: 0;
  color: var(--color-text-primary);
  font-weight: 600;
}

.my-run-detail__rules {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.my-run-detail__rule-chip {
  padding: 0.45rem 0.8rem;
  border-radius: 9999px;
  background-color: var(--color-bg-tertiary);
  border: 1px solid var(--color-border-secondary);
  color: var(--color-text-primary);
  font-size: 0.875rem;
}

.my-run-detail__history {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.my-run-detail__history-item {
  padding: 1rem;
  border: 1px solid var(--color-border-secondary);
  border-radius: 0.75rem;
  background-color: var(--color-bg-tertiary);
}

.my-run-detail__history-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.my-run-detail__history-name {
  margin: 0 0 0.2rem;
  color: var(--color-text-primary);
  font-size: 1rem;
  font-weight: 700;
}

.my-run-detail__history-description {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: 0.9rem;
}

.my-run-detail__history-badge {
  white-space: nowrap;
  padding: 0.3rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
}

.my-run-detail__history-badge--completed {
  background-color: rgba(16, 185, 129, 0.16);
  color: #34d399;
}

.my-run-detail__history-badge--active {
  background-color: rgba(34, 211, 238, 0.14);
  color: var(--color-accent-primary);
}

.my-run-detail__history-badge--inactive {
  background-color: rgba(148, 163, 184, 0.14);
  color: var(--color-text-secondary);
}

.my-run-detail__history-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
}

.my-run-detail__history-meta dt {
  margin-bottom: 0.25rem;
  color: var(--color-text-muted);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.my-run-detail__history-meta dd {
  margin: 0;
  color: var(--color-text-primary);
  font-weight: 600;
}

@media (max-width: 900px) {
  .my-run-detail__grid {
    grid-template-columns: 1fr;
  }

  .my-run-detail__header {
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .my-run-detail__stats {
    grid-template-columns: 1fr;
  }

  .my-run-detail__history-header {
    flex-direction: column;
  }

  .my-run-detail__history-meta {
    grid-template-columns: 1fr;
  }
}
</style>
