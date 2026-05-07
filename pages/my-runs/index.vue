<script setup lang="ts">
import { Icon } from '#components'
import { useMyRunsPage as useMyRunsPageComposable } from '~/composables/pages/useMyRunsPage'

definePageMeta({
  middleware: 'auth'
})

const {
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
} = useMyRunsPageComposable()

const runsWithVideos = computed(() => sortedRuns.value.filter(run => Boolean(run.videoUrl)).length)
const runsWithFeedback = computed(() =>
  sortedRuns.value.filter(run => run.finishedRun !== null || run.recommended !== null).length
)

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="runs-page">
    <section class="runs-page__hero">
      <article class="runs-page__hero-card runs-page__hero-card--primary">
        <p class="runs-page__eyebrow">Run Archive</p>
        <h1 class="runs-page__hero-title">My Completed Runs</h1>
        <p class="runs-page__hero-copy">
          Vote directly from the list, then open a run only when you want to manage the details.
        </p>
      </article>

      <article class="runs-page__hero-card runs-page__hero-card--status">
        <span class="runs-page__hero-label">Archive status</span>
        <strong class="runs-page__hero-value">{{ sortedRuns.length }} saved</strong>
        <div class="runs-page__hero-pills">
          <span class="runs-page__hero-pill">{{ runsWithVideos }} with video</span>
          <span class="runs-page__hero-pill">{{ runsWithFeedback }} reviewed</span>
        </div>
      </article>
    </section>

    <div v-if="loading" class="runs-page__loading">
      <div class="runs-page__loading-spinner" />
      <p class="runs-page__loading-text">Loading your runs...</p>
    </div>

    <div v-else-if="sortedRuns.length === 0" class="runs-page__empty">
      <Icon name="heroicons:trophy" class="runs-page__empty-icon" />
      <p class="runs-page__empty-message">You haven't completed any challenge runs yet</p>
      <NuxtLink to="/playthrough/new" class="runs-page__empty-button">
        <Icon name="heroicons:play" class="runs-page__empty-icon-small" />
        Start Your First Run
      </NuxtLink>
    </div>

    <div v-else class="my-runs-list">
      <article
        v-for="run in sortedRuns"
        :key="run.id"
        class="runs-page__run-card my-runs-list__row"
      >
        <div class="my-runs-list__summary">
          <div class="my-runs-list__title-row">
            <Icon name="heroicons:trophy" class="runs-page__run-icon" />
            <div class="my-runs-list__text">
              <h2 class="my-runs-list__game">{{ run.gameName }}</h2>
              <p class="my-runs-list__meta">
                <span>{{ run.rulesetName }}</span>
                <span>{{ formatDate(run.endedAt) }}</span>
                <span>{{ formatDuration(run.totalDuration) }}</span>
                <span v-if="run.videoUrl">Video</span>
                <span
                  v-if="getFinishedStatusLabel(run)"
                  :class="[
                    'my-runs-list__status-chip',
                    run.finishedRun ? 'my-runs-list__status-chip--yes' : 'my-runs-list__status-chip--no'
                  ]"
                >
                  {{ getFinishedStatusLabel(run) }}
                </span>
                <span
                  v-if="getRecommendationStatusLabel(run)"
                  :class="[
                    'my-runs-list__status-chip',
                    run.recommended === 1
                      ? 'my-runs-list__status-chip--yes'
                      : run.recommended === 0
                        ? 'my-runs-list__status-chip--neutral'
                        : 'my-runs-list__status-chip--no'
                  ]"
                >
                  {{ getRecommendationStatusLabel(run) }}
                </span>
              </p>
            </div>
          </div>
        </div>

        <div class="my-runs-list__controls">
          <div class="my-runs-list__group">
            <span
              :class="[
                'my-runs-list__label',
                run.finishedRun === true ? 'my-runs-list__label--yes' : run.finishedRun === false ? 'my-runs-list__label--no' : ''
              ]"
            >
              Finished
            </span>
            <div class="my-runs-list__buttons">
              <button
                :disabled="isUpdating(run, 'finishedRun')"
                :class="[
                  'my-runs-list__button',
                  run.finishedRun === true ? 'my-runs-list__button--yes my-runs-list__button--active' : ''
                ]"
                :style="getFinishedButtonStyle(run, true)"
                type="button"
                aria-label="Mark run as finished"
                title="Finished"
                @click="updateFeedback(run, 'finishedRun', true)"
              >
                <Icon
                  name="heroicons:check-circle"
                  :class="['my-runs-list__button-icon', getFinishedIconClass(run, true)]"
                />
              </button>
              <button
                :disabled="isUpdating(run, 'finishedRun')"
                :class="[
                  'my-runs-list__button',
                  run.finishedRun === false ? 'my-runs-list__button--no my-runs-list__button--active' : ''
                ]"
                :style="getFinishedButtonStyle(run, false)"
                type="button"
                aria-label="Mark run as not finished"
                title="Not finished"
                @click="updateFeedback(run, 'finishedRun', false)"
              >
                <Icon
                  name="heroicons:x-circle"
                  :class="['my-runs-list__button-icon', getFinishedIconClass(run, false)]"
                />
              </button>
            </div>
          </div>

          <div class="my-runs-list__group">
            <span
              :class="[
                'my-runs-list__label',
                run.recommended === 1
                  ? 'my-runs-list__label--yes'
                  : run.recommended === 0
                    ? 'my-runs-list__label--neutral'
                    : run.recommended === -1
                      ? 'my-runs-list__label--no'
                      : ''
              ]"
            >
              Recommend
            </span>
            <div class="my-runs-list__buttons">
              <button
                :disabled="isUpdating(run, 'recommended')"
                :class="[
                  'my-runs-list__button',
                  run.recommended === 1 ? 'my-runs-list__button--yes my-runs-list__button--active' : ''
                ]"
                :style="getRecommendationButtonStyle(run, 1)"
                type="button"
                aria-label="Recommend this run"
                title="Recommend"
                @click="updateFeedback(run, 'recommended', 1)"
              >
                <Icon
                  name="heroicons:hand-thumb-up"
                  :class="['my-runs-list__button-icon', getRecommendationIconClass(run, 1)]"
                />
              </button>
              <button
                :disabled="isUpdating(run, 'recommended')"
                :class="[
                  'my-runs-list__button',
                  run.recommended === 0 ? 'my-runs-list__button--neutral my-runs-list__button--active' : ''
                ]"
                :style="getRecommendationButtonStyle(run, 0)"
                type="button"
                aria-label="Set recommendation to neutral"
                title="Neutral"
                @click="updateFeedback(run, 'recommended', 0)"
              >
                <Icon
                  name="heroicons:minus-circle"
                  :class="['my-runs-list__button-icon', getRecommendationIconClass(run, 0)]"
                />
              </button>
              <button
                :disabled="isUpdating(run, 'recommended')"
                :class="[
                  'my-runs-list__button',
                  run.recommended === -1 ? 'my-runs-list__button--no my-runs-list__button--active' : ''
                ]"
                :style="getRecommendationButtonStyle(run, -1)"
                type="button"
                aria-label="Do not recommend this run"
                title="Do not recommend"
                @click="updateFeedback(run, 'recommended', -1)"
              >
                <Icon
                  name="heroicons:hand-thumb-down"
                  :class="['my-runs-list__button-icon', getRecommendationIconClass(run, -1)]"
                />
              </button>
            </div>
          </div>

          <div class="my-runs-list__buttons my-runs-list__buttons--actions">
            <a
              v-if="run.videoUrl"
              :href="run.videoUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="my-runs-list__button my-runs-list__button--video"
              aria-label="Open saved video in new tab"
              title="Open saved video"
            >
              <Icon :name="getVideoPlatformIcon(run.videoUrl)" class="my-runs-list__button-icon" />
            </a>
            <NuxtLink
              :to="`/runs/${run.uuid}`"
              class="my-runs-list__button my-runs-list__button--action"
              aria-label="Open public run page"
              title="Open public run page"
            >
              <Icon name="heroicons:share" class="my-runs-list__button-icon" />
            </NuxtLink>
            <NuxtLink
              :to="`/my-runs/${run.uuid}`"
              class="my-runs-list__button my-runs-list__button--action"
              aria-label="Manage completed run"
              title="Manage completed run"
            >
              <Icon name="heroicons:pencil-square" class="my-runs-list__button-icon" />
            </NuxtLink>
            <button
              v-if="canDeleteRun(run)"
              :disabled="isDeletingRun(run)"
              type="button"
              class="my-runs-list__button my-runs-list__button--delete"
              aria-label="Delete short run"
              title="Delete short run"
              @click="deleteShortRun(run)"
            >
              <Icon
                :name="isDeletingRun(run) ? 'heroicons:arrow-path' : 'heroicons:trash'"
                class="my-runs-list__button-icon"
                :class="{ 'my-runs-list__button-icon--spinning': isDeletingRun(run) }"
              />
            </button>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<style scoped>
.my-runs-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.my-runs-list__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.35rem;
}

.my-runs-list__summary {
  min-width: 0;
  flex: 1;
}

.my-runs-list__title-row {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.my-runs-list__title-row :deep(.runs-page__run-icon) {
  width: 2.4rem;
  height: 2.4rem;
  padding: 0.6rem;
  border-radius: 9999px;
  background: color-mix(in srgb, var(--color-accent-primary) 10%, transparent);
  border: 1px solid color-mix(in srgb, var(--color-border-secondary) 78%, rgba(255, 255, 255, 0.04));
  color: var(--color-accent-primary);
}

.my-runs-list__text {
  min-width: 0;
}

.my-runs-list__game {
  margin: 0 0 0.25rem;
  color: var(--color-text-primary);
  font-size: 1.05rem;
  font-weight: 700;
}

.my-runs-list__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem 0.75rem;
  margin: 0;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}

.my-runs-list__controls {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
}

.my-runs-list__group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0.55rem;
  border-radius: 0.9rem;
  background: var(--color-bg-overlay);
  border: 1px solid var(--color-border-secondary);
}

.my-runs-list__label {
  color: var(--color-text-muted);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.my-runs-list__label--yes {
  color: #34d399;
}

.my-runs-list__label--neutral {
  color: #facc15;
}

.my-runs-list__label--no {
  color: #f87171;
}

.my-runs-list__buttons {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.my-runs-list__button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.7rem;
  border: 1px solid var(--color-border-secondary);
  background-color: var(--color-bg-tertiary);
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.my-runs-list__button:hover:not(:disabled) {
  border-color: var(--color-accent-primary);
  color: var(--color-text-primary);
  background-color: var(--color-bg-card-hover);
}

.my-runs-list__button--active {
  box-shadow: inset 0 0 0 1px currentColor, 0 0 0 1px rgba(255, 255, 255, 0.04);
}

.my-runs-list__button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.my-runs-list__button--yes {
  border-color: #10b981;
  background-color: rgba(16, 185, 129, 0.24);
  color: #34d399;
}

.my-runs-list__button--neutral {
  border-color: #eab308;
  background-color: rgba(234, 179, 8, 0.22);
  color: #facc15;
}

.my-runs-list__button--no {
  border-color: #ef4444;
  background-color: rgba(239, 68, 68, 0.24);
  color: #f87171;
}

.my-runs-list__button--action {
  color: var(--color-text-primary);
  background: var(--color-bg-overlay);
}

.my-runs-list__button--video {
  color: #ef4444;
}

.my-runs-list__button--video:hover:not(:disabled) {
  border-color: #ef4444;
  color: #f87171;
}

.my-runs-list__button--delete {
  color: #f87171;
}

.my-runs-list__button--delete:hover:not(:disabled) {
  border-color: #ef4444;
  background-color: rgba(239, 68, 68, 0.16);
  color: #fca5a5;
}

.my-runs-list__button-icon {
  width: 1.1rem;
  height: 1.1rem;
}

.my-runs-list__button-icon--spinning {
  animation: my-runs-list-spin 1s linear infinite;
}

.my-runs-list__button-icon--yes {
  color: #34d399;
}

.my-runs-list__button-icon--neutral {
  color: #facc15;
}

.my-runs-list__button-icon--no {
  color: #f87171;
}

.my-runs-list__status-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.55rem;
  border-radius: 9999px;
  font-size: 0.72rem;
  font-weight: 700;
  border: 1px solid transparent;
}

.my-runs-list__status-chip--yes {
  color: #34d399;
  background-color: rgba(16, 185, 129, 0.16);
  border-color: rgba(16, 185, 129, 0.3);
}

.my-runs-list__status-chip--neutral {
  color: #facc15;
  background-color: rgba(234, 179, 8, 0.18);
  border-color: rgba(234, 179, 8, 0.32);
}

.my-runs-list__status-chip--no {
  color: #f87171;
  background-color: rgba(239, 68, 68, 0.14);
  border-color: rgba(239, 68, 68, 0.3);
}

@keyframes my-runs-list-spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 960px) {
  .my-runs-list__row {
    align-items: flex-start;
    flex-direction: column;
  }

  .my-runs-list__controls {
    width: 100%;
    justify-content: space-between;
  }
}

@media (max-width: 640px) {
  .my-runs-list__controls {
    gap: 0.75rem;
  }

  .my-runs-list__group {
    width: 100%;
    justify-content: space-between;
  }

  .my-runs-list__buttons--actions {
    margin-left: auto;
  }
}
</style>
