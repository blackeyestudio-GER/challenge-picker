<script setup lang="ts">
import { Icon } from '#components'
import { useDashboardPage as useDashboardPageComposable } from '~/composables/pages/useDashboardPage'

definePageMeta({
  middleware: ['auth', 'discord'],
  ssr: false,
})

const {
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
} = useDashboardPageComposable()

type DashboardAction = {
  label: string
  description: string
  to?: string
  icon: string
  disabled?: boolean
}

const dashboardActions = computed<DashboardAction[]>(() => {
  const actions: DashboardAction[] = [
    {
      label: 'New Game Session',
      description: activePlaythrough.value ? 'Finish current game first' : 'Start a new playthrough',
      to: activePlaythrough.value ? undefined : '/playthrough/new',
      icon: 'heroicons:play-circle',
      disabled: Boolean(activePlaythrough.value)
    },
    {
      label: 'My Completed Runs',
      description: 'View and share your videos',
      to: '/my-runs',
      icon: 'heroicons:trophy'
    },
    {
      label: 'Edit Profile',
      description: 'Update your info and avatar',
      to: '/profile',
      icon: 'heroicons:user-circle'
    },
    {
      label: 'Preferences',
      description: 'Card designs and OBS overlays',
      to: '/preferences',
      icon: 'heroicons:cog-6-tooth'
    },
    {
      label: 'Card Design Shop',
      description: 'Browse premium designs',
      to: '/shop',
      icon: 'heroicons:shopping-bag'
    }
  ]

  if (browseRunsAvailable.value) {
    actions.splice(2, 0, {
      label: 'Browse Community Runs',
      description: 'Watch challenge videos',
      to: '/runs',
      icon: 'heroicons:film'
    })
  }

  if (user.value?.isArtist) {
    actions.push({
      label: 'Artist Dashboard',
      description: 'View earnings and manage payouts',
      to: '/artist/dashboard',
      icon: 'heroicons:paint-brush'
    })
  }

  if (isAdmin.value) {
    actions.push({
      label: 'Manage Games',
      description: 'Add and edit game library',
      to: '/games/manage',
      icon: 'heroicons:puzzle-piece'
    })
  }

  return actions
})

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="dashboard-page">
    <ErrorState v-if="dashboardError" :message="dashboardError" />

    <section class="dashboard-page__hero">
      <article class="dashboard-page__hero-card dashboard-page__hero-card--primary">
        <p class="dashboard-page__eyebrow">Challenge Picker</p>
        <ClientOnly>
          <h1 class="dashboard-page__hero-title">Welcome back, {{ user?.username || 'Guest' }}</h1>
          <template #fallback>
            <h1 class="dashboard-page__hero-title">Welcome back</h1>
          </template>
        </ClientOnly>
        <p class="dashboard-page__hero-copy">Run status, core actions and challenge comparison in one clearer surface.</p>
      </article>

      <article class="dashboard-page__hero-card dashboard-page__hero-card--status">
        <span class="dashboard-page__hero-label">Current run</span>
        <strong class="dashboard-page__hero-run">{{ activePlaythrough?.gameName || 'No active run' }}</strong>
        <span class="dashboard-page__hero-pill">
          {{ activePlaythrough ? 'Live' : 'Idle' }}
        </span>
      </article>
    </section>

    <!-- Email Verification Warning -->
    <div v-if="user && !user.emailVerified && user.oauthProvider === null" class="dashboard-page__email-warning">
      <div class="dashboard-page__email-warning-content">
        <Icon name="heroicons:exclamation-triangle" class="dashboard-page__email-warning-icon" />
        <div class="dashboard-page__email-warning-copy">
          <h3 class="dashboard-page__email-warning-title">Verify Your Email</h3>
          <p class="dashboard-page__email-warning-text">Please verify your email address to access all features.</p>
          <NuxtLink
            to="/auth/verify-email"
            class="dashboard-page__email-warning-button"
          >
            Verify Email
          </NuxtLink>
        </div>
      </div>
    </div>

    <div v-if="!loading && activePlaythrough" class="dashboard-page__active-banner">
      <div class="dashboard-page__active-banner-copy">
        <Icon name="heroicons:play" class="dashboard-page__active-banner-icon" />
        <div>
          <h2 class="dashboard-page__active-banner-title">Game in Progress</h2>
          <p class="dashboard-page__active-banner-text">You still have an active playthrough running.</p>
        </div>
      </div>
      <NuxtLink
        :to="`/play/${activePlaythrough.uuid}`"
        class="dashboard-page__active-banner-button"
      >
        Resume Game
      </NuxtLink>
    </div>

    <!-- User Statistics -->
    <div v-if="!statsLoading && stats" class="dashboard-page__stats">
      <div class="dashboard-page__stats-grid">
        <article class="dashboard-page__stat-card">
          <span class="dashboard-page__stat-label">Completed Runs</span>
          <strong class="dashboard-page__stat-value">{{ stats.completedPlaythroughs }}</strong>
        </article>
        <article class="dashboard-page__stat-card">
          <span class="dashboard-page__stat-label">Rules Played</span>
          <strong class="dashboard-page__stat-value">{{ stats.rulesPlayed }}</strong>
        </article>
        <article class="dashboard-page__stat-card">
          <span class="dashboard-page__stat-label">Total Rules</span>
          <strong class="dashboard-page__stat-value">{{ stats.totalActiveRules }}</strong>
        </article>
        <article class="dashboard-page__stat-card">
          <span class="dashboard-page__stat-label">Votes Cast</span>
          <strong class="dashboard-page__stat-value">{{ stats.totalVotes }}</strong>
        </article>
      </div>
    </div>

    <!-- Quick Actions -->
    <section class="dashboard-page__actions">
      <template v-for="action in dashboardActions" :key="action.label">
        <NuxtLink
          v-if="action.to"
          :to="action.to"
          class="dashboard-page__action-tile"
          :class="{ 'dashboard-page__action-tile--disabled': action.disabled }"
        >
          <span class="dashboard-page__action-orb">
            <Icon :name="action.icon" class="dashboard-page__action-icon" />
          </span>
          <strong class="dashboard-page__action-title">{{ action.label }}</strong>
          <span class="dashboard-page__action-description">{{ action.description }}</span>
        </NuxtLink>
        <div
          v-else
          class="dashboard-page__action-tile"
          :class="{ 'dashboard-page__action-tile--disabled': action.disabled }"
        >
          <span class="dashboard-page__action-orb">
            <Icon :name="action.icon" class="dashboard-page__action-icon" />
          </span>
          <strong class="dashboard-page__action-title">{{ action.label }}</strong>
          <span class="dashboard-page__action-description">{{ action.description }}</span>
        </div>
      </template>
    </section>

    <!-- Sent Challenges Section -->
    <div v-if="sentChallenges.length > 0" class="dashboard-page__challenges">
      <div class="dashboard-page__section-head">
        <h2 class="dashboard-page__section-title">
          <Icon name="heroicons:trophy" class="dashboard-page__section-title-icon icon-warning" />
          My Challenges
        </h2>
        <span class="dashboard-page__section-subtitle">Comparison overview</span>
      </div>
      
      <div class="dashboard-page__challenges-grid">
        <div
          v-for="challengeGroup in sentChallenges"
          :key="challengeGroup.playthroughUuid"
          class="dashboard-page__challenge-card"
        >
          <!-- Game Image and Info -->
          <div class="dashboard-page__challenge-card-top">
            <div
              v-if="challengeGroup.game.imageBase64"
              class="dashboard-page__challenge-image-shell"
            >
              <img
                :src="`data:image/jpeg;base64,${challengeGroup.game.imageBase64}`"
                :alt="challengeGroup.game.name"
                class="w-full h-full object-cover"
              >
            </div>
            <div class="dashboard-page__challenge-copy">
              <h3 class="dashboard-page__challenge-title">{{ challengeGroup.game.name }}</h3>
              <p class="dashboard-page__challenge-subtitle">{{ challengeGroup.ruleset.name }}</p>
              <p class="dashboard-page__challenge-date">{{ formatDate(challengeGroup.createdAt) }}</p>
            </div>
          </div>

          <!-- Challenge Stats -->
          <div class="dashboard-page__challenge-stats">
            <div class="dashboard-page__challenge-stat-row">
              <span class="dashboard-page__challenge-stat-label">Total Challenges:</span>
              <span class="dashboard-page__challenge-stat-value">{{ challengeGroup.challenges.length }}</span>
            </div>
            <div class="dashboard-page__challenge-stat-row">
              <span class="dashboard-page__challenge-stat-label">Accepted:</span>
              <span class="dashboard-page__challenge-stat-value dashboard-page__challenge-stat-value--success">{{ getAcceptedCount(challengeGroup.challenges) }}</span>
            </div>
          </div>

          <!-- Participants List -->
          <div class="dashboard-page__challenge-participants">
            <div
              v-for="challenge in challengeGroup.challenges"
              :key="challenge.uuid"
              class="dashboard-page__challenge-participant"
            >
              <span class="dashboard-page__challenge-participant-name">{{ challenge.challengedUser.username }}</span>
              <span
                class="dashboard-page__challenge-participant-status"
                :class="getStatusBadgeClass(challenge.status)"
              >
                {{ challenge.status }}
              </span>
            </div>
          </div>

          <!-- View Comparison Button -->
          <NuxtLink
            :to="`/challenges/comparison/${challengeGroup.playthroughUuid}`"
            class="dashboard-page__challenge-cta"
          >
            View Comparison
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Empty State for Challenges -->
    <div v-else-if="!challengesLoading && !loading" class="dashboard-page__challenges">
      <div class="dashboard-page__empty-state">
        <Icon name="heroicons:trophy" class="dashboard-page__empty-state-icon" />
        <h3 class="dashboard-page__empty-state-title">No Challenges Yet</h3>
        <p class="dashboard-page__empty-state-copy">
          Challenge someone from your playthrough to see comparison results here!
        </p>
      </div>
    </div>
  </div>
</template>
