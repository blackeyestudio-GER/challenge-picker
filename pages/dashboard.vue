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

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="dashboard-page">
    <ErrorState v-if="dashboardError" :message="dashboardError" />

    <!-- Welcome Section -->
    <div class="dashboard-page__welcome">
      <ClientOnly>
        <h2 class="dashboard-page__welcome-title">Welcome back, {{ user?.username || 'Guest' }}! 👋</h2>
        <template #fallback>
          <h2 class="dashboard-page__welcome-title">Welcome back! 👋</h2>
        </template>
      </ClientOnly>
      <p class="dashboard-page__welcome-subtitle">Your streaming dashboard is ready to go!</p>
    </div>

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

    <!-- Active Game Alert -->
    <div v-if="!loading && activePlaythrough" class="dashboard-page__active-alert">
      <div class="dashboard-page__active-card">
        <div class="dashboard-page__active-content">
          <div class="dashboard-page__active-left">
            <div class="dashboard-page__active-icon-wrapper">
              <Icon name="heroicons:play" class="dashboard-page__active-icon" />
            </div>
            <div class="dashboard-page__active-text">
              <h3 class="dashboard-page__active-title">Game in Progress!</h3>
              <p class="dashboard-page__active-subtitle">You have an active game session running</p>
            </div>
          </div>
          <NuxtLink
            :to="`/play/${activePlaythrough.uuid}`"
            class="dashboard-page__active-button"
          >
            Resume Game →
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- User Statistics -->
    <div v-if="!statsLoading && stats" class="dashboard-page__stats">
      <div class="dashboard-page__stats-grid">
        <div class="dashboard-page__stat-card dashboard-page__stat-card--cyan">
          <div class="dashboard-page__stat-row">
            <div class="dashboard-page__stat-icon-wrapper dashboard-page__stat-icon-wrapper--cyan">
              <Icon name="heroicons:trophy" class="dashboard-page__stat-icon dashboard-page__stat-icon--cyan" />
            </div>
            <div>
              <div class="dashboard-page__stat-value">{{ stats.completedPlaythroughs }}</div>
              <div class="dashboard-page__stat-label dashboard-page__stat-label--cyan">Completed Runs</div>
            </div>
          </div>
        </div>
        
        <div class="dashboard-page__stat-card dashboard-page__stat-card--purple">
          <div class="dashboard-page__stat-row">
            <div class="dashboard-page__stat-icon-wrapper dashboard-page__stat-icon-wrapper--purple">
              <Icon name="heroicons:sparkles" class="dashboard-page__stat-icon dashboard-page__stat-icon--purple" />
            </div>
            <div>
              <div class="dashboard-page__stat-value">{{ stats.rulesPlayed }}</div>
              <div class="dashboard-page__stat-label dashboard-page__stat-label--purple">Rules Played</div>
            </div>
          </div>
        </div>
        
        <div class="dashboard-page__stat-card dashboard-page__stat-card--yellow">
          <div class="dashboard-page__stat-row">
            <div class="dashboard-page__stat-icon-wrapper dashboard-page__stat-icon-wrapper--yellow">
              <Icon name="heroicons:star" class="dashboard-page__stat-icon dashboard-page__stat-icon--yellow" />
            </div>
            <div>
              <div class="dashboard-page__stat-value">{{ stats.totalActiveRules }}</div>
              <div class="dashboard-page__stat-label dashboard-page__stat-label--yellow">Total Rules</div>
            </div>
          </div>
        </div>
        
        <div class="dashboard-page__stat-card dashboard-page__stat-card--green">
          <div class="dashboard-page__stat-row">
            <div class="dashboard-page__stat-icon-wrapper dashboard-page__stat-icon-wrapper--green">
              <Icon name="heroicons:hand-thumb-up" class="dashboard-page__stat-icon dashboard-page__stat-icon--green" />
            </div>
            <div>
              <div class="dashboard-page__stat-value">{{ stats.totalVotes }}</div>
              <div class="dashboard-page__stat-label dashboard-page__stat-label--green">Votes Cast</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-page__actions">
      <NuxtLink
        v-if="!activePlaythrough"
        to="/playthrough/new"
        class="dashboard-page__action-card dashboard-page__action-card--cyan"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:play-circle" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">New Game Session</h3>
          <p class="dashboard-page__action-description">Start a new playthrough</p>
        </div>
      </NuxtLink>
      
      <div
        v-else
        class="dashboard-page__action-card dashboard-page__action-card--disabled"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:play-circle" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">New Game Session</h3>
          <p class="dashboard-page__action-description">Finish current game first</p>
        </div>
      </div>

      <NuxtLink
        to="/my-runs"
        class="dashboard-page__action-card dashboard-page__action-card--yellow"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:trophy" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">My Completed Runs</h3>
          <p class="dashboard-page__action-description">View & share your videos</p>
        </div>
      </NuxtLink>

      <NuxtLink
        v-if="browseRunsAvailable"
        to="/runs"
        class="dashboard-page__action-card dashboard-page__action-card--purple"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:film" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">Browse Community Runs</h3>
          <p class="dashboard-page__action-description">Watch challenge videos</p>
        </div>
      </NuxtLink>

      <NuxtLink
        to="/profile"
        class="dashboard-page__action-card dashboard-page__action-card--gray"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:user-circle" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">Edit Profile</h3>
          <p class="dashboard-page__action-description">Update your info and avatar</p>
        </div>
      </NuxtLink>

      <NuxtLink
        to="/preferences"
        class="dashboard-page__action-card dashboard-page__action-card--magenta"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:cog-6-tooth" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">Preferences</h3>
          <p class="dashboard-page__action-description">Card designs & OBS overlays</p>
        </div>
      </NuxtLink>

      <!-- Artist Dashboard Link (if user is artist) -->
      <NuxtLink
        v-if="user?.isArtist"
        to="/artist/dashboard"
        class="dashboard-page__action-card dashboard-page__action-card--yellow"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:paint-brush" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">Artist Dashboard</h3>
          <p class="dashboard-page__action-description">View earnings & manage payouts</p>
        </div>
      </NuxtLink>

      <!-- Card Design Shop -->
      <NuxtLink
        to="/shop"
        class="dashboard-page__action-card dashboard-page__action-card--yellow"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:shopping-bag" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">Card Design Shop</h3>
          <p class="dashboard-page__action-description">Browse premium designs</p>
        </div>
      </NuxtLink>

      <!-- Admin Only: Manage Games -->
      <NuxtLink
        v-if="isAdmin"
        to="/games/manage"
        class="dashboard-page__action-card dashboard-page__action-card--gray"
      >
        <div class="dashboard-page__action-icon-wrapper">
          <Icon name="heroicons:puzzle-piece" class="dashboard-page__action-icon" />
        </div>
        <div class="dashboard-page__action-content">
          <h3 class="dashboard-page__action-title">Manage Games</h3>
          <p class="dashboard-page__action-description">Add and edit game library</p>
        </div>
      </NuxtLink>
    </div>

    <!-- Sent Challenges Section -->
    <div v-if="sentChallenges.length > 0" class="dashboard-page__challenges">
      <h2 class="dashboard-page__section-title">
        <Icon name="heroicons:trophy" class="dashboard-page__section-title-icon icon-warning" />
        My Challenges
      </h2>
      
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
            📊 View Comparison
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
