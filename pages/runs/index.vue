<script setup lang="ts">
import { Icon } from '#components'
import { useRunsPage as useRunsPageComposable } from '~/composables/pages/useRunsPage'

const {
  games,
  categories,
  errorMessage,
  loading,
  selectedGameId,
  selectedCategoryId,
  filteredRuns,
  bootstrap,
  clearFilters,
  formatDuration,
  formatDate,
  extractVideoId
} = useRunsPageComposable()

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="runs-page">
    <section class="runs-page__hero">
      <article class="runs-page__hero-card runs-page__hero-card--primary">
        <p class="runs-page__eyebrow">Community Archive</p>
        <h1 class="runs-page__hero-title">Browse Challenge Runs</h1>
        <p class="runs-page__hero-copy">
          Explore the latest completed challenge runs, with or without videos.
        </p>
        <p class="runs-page__legend">
          <span class="runs-page__legend-item">
            <span class="runs-page__legend-dot runs-page__legend-dot--yellow"/>
            Your run
          </span>
          <span class="runs-page__legend-item">
            <span class="runs-page__legend-dot runs-page__legend-dot--cyan"/>
            Games you've played
          </span>
        </p>
      </article>

      <article class="runs-page__hero-card runs-page__hero-card--status">
        <span class="runs-page__hero-label">Visible now</span>
        <strong class="runs-page__hero-value">{{ filteredRuns.length }} runs</strong>
        <div class="runs-page__hero-pills">
          <span class="runs-page__hero-pill">{{ games.length }} games</span>
          <span class="runs-page__hero-pill">{{ categories.length }} categories</span>
        </div>
      </article>
    </section>

    <div class="runs-page__filters">
      <div class="runs-page__filters-header">
        <Icon name="heroicons:funnel" class="runs-page__filters-icon" />
        <h2 class="runs-page__filters-title">Filters</h2>
      </div>

      <div class="runs-page__filters-grid">
        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">Game</label>
          <select
            v-model="selectedGameId"
            class="runs-page__filter-select"
          >
            <option :value="null">All Games</option>
            <option v-for="game in games" :key="game.id" :value="game.id">
              {{ game.name }}
            </option>
          </select>
        </div>

        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">Category</label>
          <select
            v-model="selectedCategoryId"
            class="runs-page__filter-select"
          >
            <option :value="null">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <div class="runs-page__filter-field">
          <label class="runs-page__filter-label">&nbsp;</label>
          <button
            :disabled="!selectedGameId && !selectedCategoryId"
            class="runs-page__filter-clear"
            @click="clearFilters"
          >
            <Icon name="heroicons:x-mark" class="runs-page__filter-clear-icon" />
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <LoadingState v-if="loading" message="Loading runs..." />

    <ErrorState v-else-if="errorMessage" :message="errorMessage" />

    <EmptyState
      v-else-if="filteredRuns.length === 0"
      icon="heroicons:film"
      title="No runs found"
      message="Try adjusting your filters or check back later."
    />

    <div v-else class="runs-page__list">
      <div
        v-for="run in filteredRuns"
        :key="run.id"
        class="runs-page__run-card"
        :class="{
          'runs-page__run-card--highlight-yellow': run.isOwnRun,
          'runs-page__run-card--highlight-cyan': run.hasPlayedGame && !run.isOwnRun
        }"
      >
        <div class="runs-page__run-header">
          <div class="runs-page__run-content">
            <div class="runs-page__run-title-row">
              <Icon
                name="heroicons:trophy"
                class="runs-page__run-icon"
                :class="{
                  'runs-page__run-icon--warning': run.isOwnRun,
                  'runs-page__run-icon--cyan': run.hasPlayedGame && !run.isOwnRun,
                  'runs-page__run-icon--muted': !run.isOwnRun && !run.hasPlayedGame
                }"
              />
              <h3 class="runs-page__run-game">{{ run.gameName }}</h3>

              <span
                v-if="run.isOwnRun"
                class="admin-badge admin-badge--warning"
              >
                YOUR RUN
              </span>

              <span
                v-else-if="run.hasPlayedGame"
                class="runs-page__played-badge"
              >
                <Icon name="heroicons:check-circle" class="runs-page__played-badge-icon" />
                You've played this
              </span>
            </div>

            <p class="runs-page__run-ruleset">{{ run.rulesetName }}</p>

            <div class="runs-page__run-meta">
              <span class="runs-page__run-meta-item">
                <Icon name="heroicons:user" class="runs-page__run-meta-icon" />
                {{ run.username }}
              </span>
              <span class="runs-page__run-meta-item">
                <Icon name="heroicons:calendar" class="runs-page__run-meta-icon" />
                {{ formatDate(run.endedAt!) }}
              </span>
              <span class="runs-page__run-meta-item">
                <Icon name="heroicons:clock" class="runs-page__run-meta-icon" />
                {{ formatDuration(run.totalDuration) }}
              </span>
            </div>

            <a
              v-if="run.videoUrl"
              :href="run.videoUrl!"
              target="_blank"
              rel="noopener noreferrer"
              class="runs-page__video-link-inline"
            >
              <Icon
                :name="extractVideoId(run.videoUrl).platform === 'youtube' ? 'heroicons:play-circle' : 'heroicons:arrow-top-right-on-square'"
                class="runs-page__video-link-icon"
                :class="extractVideoId(run.videoUrl).platform === 'youtube' ? 'runs-page__video-icon--youtube' : 'runs-page__video-icon--external'"
              />
              <span class="underline">Watch on {{ extractVideoId(run.videoUrl).platform === 'youtube' ? 'YouTube' : 'External video' }}</span>
              <Icon name="heroicons:arrow-top-right-on-square" class="runs-page__video-link-external-icon" />
            </a>
            <div v-else class="runs-page__video-link-empty">
              <Icon name="heroicons:film" class="runs-page__video-link-icon" />
              <span>No video attached yet</span>
            </div>
          </div>

          <div class="runs-page__run-actions">
            <NuxtLink
              :to="`/runs/${run.uuid}`"
              :class="[
                'runs-page__run-button',
                run.isOwnRun
                  ? 'runs-page__run-button--warning'
                  : 'runs-page__run-button--share'
              ]"
            >
              <Icon name="heroicons:share" class="runs-page__run-button-icon" />
              View Run
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
