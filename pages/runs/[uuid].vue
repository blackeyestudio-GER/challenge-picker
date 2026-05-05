<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Icon } from '#components'
import type { PublicRunPlaythrough, PublicRunResponse, PublicRunRule, PublicRunHistoryEntry } from '~/composables/usePlaythrough'
import { extractErrorMessage } from '~/utils/errorHandler'
const { notifyApiError } = useNotify()

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
      notifyApiError(err, 'Failed to copy share link')
    }
  }
}

const extractVideoId = (url: string | null): { platform: 'youtube' | 'external' | null; id: string | null } => {
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
  
  return { platform: 'external', id: null }
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
  'run-detail__rule-icon--legendary': rule.type === 'legendary',
  'icon-secondary': rule.type === 'court',
  'icon-danger': rule.type === 'counter',
  'icon-primary': !rule.type || rule.type === 'basic'
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
  if (!playthrough.value) return 'icon-muted'
  if (playthrough.value.finishedRun === true) return 'icon-success'
  if (playthrough.value.finishedRun === false) return 'icon-danger'
  return 'icon-muted'
})

const recommendedIconClass = computed(() => {
  if (!playthrough.value) return 'icon-muted'
  if (playthrough.value.recommended === 1) return 'icon-success'
  if (playthrough.value.recommended === 0) return 'icon-warning'
  if (playthrough.value.recommended === -1) return 'icon-danger'
  return 'icon-muted'
})

const finishedRunCardClass = computed(() => {
  if (!playthrough.value) return 'run-detail__status-card'
  if (playthrough.value.finishedRun === true) return 'run-detail__status-card run-detail__status-card--success'
  if (playthrough.value.finishedRun === false) return 'run-detail__status-card run-detail__status-card--danger'
  return 'run-detail__status-card'
})

const recommendationCardClass = computed(() => {
  if (!playthrough.value) return 'run-detail__status-card'
  if (playthrough.value.recommended === 1) return 'run-detail__status-card run-detail__status-card--success'
  if (playthrough.value.recommended === 0) return 'run-detail__status-card run-detail__status-card--warning'
  if (playthrough.value.recommended === -1) return 'run-detail__status-card run-detail__status-card--danger'
  return 'run-detail__status-card'
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
  <div class="run-detail">
    <div class="run-detail__shell">
      <!-- Loading State -->
      <div v-if="loading" class="run-detail__state">
        <div class="run-detail__spinner"/>
        <p class="run-detail__state-copy">Loading challenge run...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="run-detail__state">
        <Icon name="heroicons:exclamation-triangle" class="run-detail__error-icon" />
        <h1 class="run-detail__error-title">Playthrough Not Found</h1>
        <p class="run-detail__state-copy">{{ error }}</p>
        <NuxtLink
          to="/"
          class="btn btn-primary"
        >
          <Icon name="heroicons:home" class="w-5 h-5" />
          Go Home
        </NuxtLink>
      </div>

      <!-- Playthrough Details -->
      <div v-else-if="playthrough">
        <!-- Header -->
        <div class="run-detail__header">
          <div class="run-detail__header-row">
            <NuxtLink
              to="/runs"
              class="run-detail__back-link"
            >
              <Icon name="heroicons:arrow-left" class="w-5 h-5" />
              Back to Runs
            </NuxtLink>
            <button
              class="btn btn-secondary btn-sm"
              @click="copyShareLink"
            >
              <Icon :name="copied ? 'heroicons:check' : 'heroicons:share'" class="w-5 h-5" />
              {{ copied ? 'Copied!' : 'Share Run' }}
            </button>
          </div>

          <div v-if="isAuthenticated && playThisChallengeUrl" class="mb-4">
            <NuxtLink
              :to="playThisChallengeUrl"
              class="btn btn-primary btn-sm"
            >
              <Icon name="heroicons:play-circle" class="w-5 h-5" />
              Play This Challenge
            </NuxtLink>
          </div>
          
          <h1 class="run-detail__title">
            {{ playthrough.game.name }}
          </h1>
          <p class="run-detail__subtitle">{{ playthrough.ruleset.name }}</p>
        </div>

        <!-- Main Content Grid -->
        <div class="run-detail__grid">
          <!-- Game Image -->
          <div>
            <div class="run-detail__card run-detail__image-card">
              <img
                v-if="playthrough.game.imageUrl"
                :src="playthrough.game.imageUrl"
                :alt="playthrough.game.name"
                class="run-detail__image"
              >
              <div v-else class="run-detail__image-placeholder">
                <Icon name="heroicons:photo" class="run-detail__image-placeholder-icon" />
              </div>
            </div>
          </div>

          <!-- Run Stats -->
          <div class="run-detail__main">
            <!-- Player Info -->
            <div class="run-detail__card">
              <div class="run-detail__player">
                <div v-if="playthrough.user.avatarUrl" class="run-detail__avatar">
                  <img :src="playthrough.user.avatarUrl" :alt="playthrough.user.username" class="run-detail__avatar-image" >
                </div>
                <div v-else class="run-detail__avatar run-detail__avatar--placeholder">
                  <Icon name="heroicons:user" class="run-detail__avatar-icon" />
                </div>
                <div>
                  <p class="run-detail__label">Completed by</p>
                  <p class="run-detail__value run-detail__value--large">{{ playthrough.user.username }}</p>
                </div>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="run-detail__stats-grid">
              <div class="run-detail__card">
                <div class="run-detail__stat-row">
                  <Icon name="heroicons:clock" class="run-detail__stat-icon icon-primary" />
                  <p class="run-detail__label">Duration</p>
                </div>
                <p class="run-detail__value run-detail__value--large">{{ formatDuration(playthrough.totalDuration) }}</p>
              </div>

              <div class="run-detail__card">
                <div class="run-detail__stat-row">
                  <Icon name="heroicons:calendar" class="run-detail__stat-icon icon-primary" />
                  <p class="run-detail__label">Completed</p>
                </div>
                <p class="run-detail__value">{{ formatDate(playthrough.endedAt) }}</p>
              </div>

              <div :class="finishedRunCardClass">
                <div class="run-detail__stat-row">
                  <Icon name="heroicons:flag" :class="['run-detail__stat-icon', finishedRunIconClass]" />
                  <p class="run-detail__label">Run Result</p>
                </div>
                <p class="run-detail__value">{{ finishedRunLabel }}</p>
              </div>

              <div :class="recommendationCardClass">
                <div class="run-detail__stat-row">
                  <Icon name="heroicons:hand-thumb-up" :class="['run-detail__stat-icon', recommendedIconClass]" />
                  <p class="run-detail__label">Player Feedback</p>
                </div>
                <p class="run-detail__value">{{ recommendedLabel }}</p>
              </div>
            </div>

            <!-- Video Link -->
            <div v-if="playthrough.videoUrl" class="run-detail__video-card">
              <div class="run-detail__video-content">
                <div>
                  <div class="run-detail__stat-row">
                    <Icon
                      :name="extractVideoId(playthrough.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:arrow-top-right-on-square'"
                      class="run-detail__stat-icon"
                      :class="extractVideoId(playthrough.videoUrl).platform === 'youtube' ? 'run-detail__video-icon--youtube' : 'run-detail__video-icon--external'"
                    />
                    <p class="run-detail__label">Watch the full run</p>
                  </div>
                  <p class="run-detail__value">
                    {{ extractVideoId(playthrough.videoUrl).platform === 'youtube' ? 'YouTube recording' : 'External recording' }}
                  </p>
                </div>
                <a
                  :href="playthrough.videoUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn btn-primary"
                >
                  <Icon name="heroicons:play" class="w-5 h-5" />
                  Watch Now
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Ruleset Description -->
        <div v-if="playthrough.ruleset.description" class="run-detail__card run-detail__section">
          <div class="run-detail__section-header">
            <Icon name="heroicons:document-text" class="run-detail__section-icon icon-primary" />
            <h2 class="run-detail__section-title">About This Challenge</h2>
          </div>
          <p class="run-detail__copy">{{ playthrough.ruleset.description }}</p>
        </div>

        <!-- Rules Used -->
        <div class="run-detail__card run-detail__section">
          <div class="run-detail__section-header">
            <Icon name="heroicons:list-bullet" class="run-detail__section-icon icon-primary" />
            <h2 class="run-detail__section-title">Rules Used ({{ usedRules.length }})</h2>
          </div>
          
          <div v-if="usedRules.length === 0" class="run-detail__empty">
            No stored rules were found for this run
          </div>
          
          <div v-else class="run-detail__rules-grid">
            <div
              v-for="rule in usedRules"
              :key="rule.id"
              class="run-detail__rule-card"
            >
              <div class="run-detail__rule-row">
                <Icon
                  :name="getRuleIconName(rule)"
                  class="run-detail__rule-icon"
                  :class="getRuleIconClass(rule)"
                />
                <div>
                  <h3 class="run-detail__rule-title">{{ rule.name }}</h3>
                  <p class="run-detail__rule-copy">{{ rule.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="run-detail__card run-detail__section">
          <div class="run-detail__section-header">
            <Icon name="heroicons:clock" class="run-detail__section-icon icon-primary" />
            <h2 class="run-detail__section-title">Rule History ({{ ruleHistory.length }})</h2>
          </div>

          <div v-if="ruleHistory.length === 0" class="run-detail__empty">
            No called-rule history was recorded for this run
          </div>

          <div v-else class="run-detail__history-list">
            <article
              v-for="entry in ruleHistory"
              :key="`${entry.ruleId}-${entry.startedAt || entry.createdAt || entry.name}`"
              class="run-detail__history-card"
            >
              <div class="run-detail__history-header">
                <div class="run-detail__rule-row">
                  <Icon
                    :name="getRuleIconName(entry)"
                    class="run-detail__rule-icon"
                    :class="getRuleIconClass(entry)"
                  />
                  <div>
                    <h3 class="run-detail__rule-title">{{ entry.name }}</h3>
                    <p v-if="entry.description" class="run-detail__rule-copy">{{ entry.description }}</p>
                  </div>
                </div>
                <span
                  class="run-detail__history-badge"
                  :class="entry.completed ? 'run-detail__history-badge--success' : entry.isActive ? 'run-detail__history-badge--active' : 'run-detail__history-badge--inactive'"
                >
                  {{ entry.completed ? 'Completed' : entry.isActive ? 'Active at end' : 'Inactive' }}
                </span>
              </div>

              <div class="run-detail__history-grid">
                <div>
                  <p class="run-detail__meta-label">Started</p>
                  <p class="run-detail__meta-value">{{ formatHistoryDate(entry.startedAt || entry.createdAt) }}</p>
                </div>
                <div>
                  <p class="run-detail__meta-label">Completed</p>
                  <p class="run-detail__meta-value">{{ formatHistoryDate(entry.completedAt) }}</p>
                </div>
                <div v-if="entry.currentAmount !== null">
                  <p class="run-detail__meta-label">Counter</p>
                  <p class="run-detail__meta-value">{{ entry.currentAmount }}</p>
                </div>
              </div>
            </article>
          </div>
        </div>

        <!-- Footer CTA -->
        <div class="run-detail__footer">
          <h2 class="run-detail__footer-title">Ready for Your Own Challenge?</h2>
          <p class="run-detail__copy run-detail__footer-copy">Join the community and start your challenge run today!</p>
          <NuxtLink
            to="/"
            class="btn btn-primary btn-lg"
          >
            <Icon name="heroicons:play" class="w-6 h-6" />
            Get Started
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.run-detail {
  min-height: 100vh;
  padding: 2rem 1rem;
}

.run-detail__shell {
  max-width: 80rem;
  margin: 0 auto;
}

.run-detail__state {
  text-align: center;
  padding: 5rem 0;
}

.run-detail__spinner {
  display: inline-block;
  width: 4rem;
  height: 4rem;
  margin-bottom: 1rem;
  border-radius: 9999px;
  border-top: 2px solid var(--color-accent-primary);
  border-bottom: 2px solid var(--color-accent-primary);
  animation: spin 1s linear infinite;
}

.run-detail__error-icon {
  width: 6rem;
  height: 6rem;
  margin: 0 auto 1rem;
  color: var(--color-icon-danger);
}

.run-detail__error-title,
.run-detail__section-title,
.run-detail__rule-title,
.run-detail__meta-value,
.run-detail__value,
.run-detail__footer-title {
  color: var(--color-text-primary);
}

.run-detail__error-title {
  font-size: 1.875rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.run-detail__state-copy,
.run-detail__subtitle,
.run-detail__copy,
.run-detail__rule-copy,
.run-detail__meta-label,
.run-detail__label {
  color: var(--color-text-secondary);
}

.run-detail__header {
  margin-bottom: 2rem;
}

.run-detail__header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.run-detail__back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-accent-primary);
}

.run-detail__back-link:hover {
  color: var(--color-accent-primary-hover);
}

.run-detail__title {
  margin-bottom: 0.5rem;
  font-size: 3rem;
  font-weight: 700;
  background: linear-gradient(to right, var(--color-accent-primary), var(--color-accent-secondary));
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.run-detail__subtitle {
  font-size: 1.5rem;
}

.run-detail__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

@media (min-width: 1024px) {
  .run-detail__grid {
    grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
  }
}

.run-detail__main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.run-detail__card,
.run-detail__status-card {
  background-color: var(--color-bg-card);
  border: 1px solid var(--color-border-secondary);
  border-radius: 0.75rem;
  backdrop-filter: blur(4px);
}

.run-detail__card,
.run-detail__status-card,
.run-detail__video-card {
  padding: 1.5rem;
}

.run-detail__image-card {
  overflow: hidden;
  padding: 0;
}

.run-detail__image {
  width: 100%;
  height: 16rem;
  object-fit: cover;
}

.run-detail__image-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 16rem;
  background-color: var(--color-bg-secondary);
}

.run-detail__image-placeholder-icon {
  width: 4rem;
  height: 4rem;
  color: var(--color-text-muted);
}

.run-detail__player,
.run-detail__stat-row,
.run-detail__section-header,
.run-detail__rule-row {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.run-detail__avatar {
  width: 4rem;
  height: 4rem;
  border-radius: 9999px;
  overflow: hidden;
  border: 2px solid var(--color-border-accent);
}

.run-detail__avatar--placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-bg-tertiary);
}

.run-detail__avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.run-detail__avatar-icon {
  width: 2rem;
  height: 2rem;
  color: var(--color-text-muted);
}

.run-detail__stats-grid,
.run-detail__rules-grid,
.run-detail__history-grid {
  display: grid;
  gap: 1rem;
}

.run-detail__stats-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.run-detail__rules-grid {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
  .run-detail__rules-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .run-detail__history-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.run-detail__label,
.run-detail__meta-label {
  margin-bottom: 0.25rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.run-detail__value {
  font-size: 1.125rem;
  font-weight: 700;
}

.run-detail__value--large {
  font-size: 1.5rem;
}

.run-detail__stat-icon,
.run-detail__section-icon,
.run-detail__rule-icon {
  width: 1.5rem;
  height: 1.5rem;
}

.run-detail__status-card--success {
  background-color: var(--status-active-bg);
  border-color: var(--status-active-border);
}

.run-detail__status-card--warning {
  background-color: var(--status-pending-bg);
  border-color: var(--status-pending-border);
}

.run-detail__status-card--danger {
  background-color: var(--status-failed-bg);
  border-color: var(--status-failed-border);
}

.run-detail__video-card {
  background: linear-gradient(to right, var(--color-accent-primary-muted), var(--color-accent-secondary-muted));
  border: 1px solid var(--color-border-accent);
  border-radius: 0.75rem;
}

.run-detail__video-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.run-detail__section {
  margin-bottom: 2rem;
}

.run-detail__empty {
  text-align: center;
  padding: 2rem 0;
  color: var(--color-text-muted);
}

.run-detail__rule-card,
.run-detail__history-card {
  background-color: var(--color-bg-secondary);
  border: 1px solid var(--color-border-primary);
  border-radius: 0.75rem;
  padding: 1rem;
}

.run-detail__rule-card:hover {
  border-color: var(--color-border-accent);
}

.run-detail__rule-title {
  margin-bottom: 0.25rem;
  font-weight: 700;
}

.run-detail__rule-copy {
  font-size: 0.875rem;
}

.run-detail__history-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.run-detail__history-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.run-detail__history-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.run-detail__history-badge--success {
  background-color: var(--status-active-bg);
  color: var(--status-active-text);
}

.run-detail__history-badge--active {
  background-color: var(--status-completed-bg);
  color: var(--status-completed-text);
}

.run-detail__history-badge--inactive {
  background-color: var(--status-inactive-bg);
  color: var(--status-inactive-text);
}

.run-detail__rule-icon--legendary {
  color: #eab308;
}

.run-detail__video-icon--youtube {
  color: #ef4444;
}

.run-detail__video-icon--external {
  color: var(--color-accent-primary);
}

.run-detail__footer {
  margin-top: 3rem;
  padding: 3rem 0;
  text-align: center;
  border-top: 1px solid var(--color-border-primary);
}

.run-detail__footer-title {
  margin-bottom: 1rem;
  font-size: 1.875rem;
  font-weight: 700;
}

.run-detail__footer-copy {
  margin-bottom: 1.5rem;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}
</style>
