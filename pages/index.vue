<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

definePageMeta({
  layout: 'landing'
})

const { isAuthenticated, loadAuth } = useAuth()
const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
  loadAuth()

  if (isAuthenticated.value) {
    navigateTo('/dashboard')
  }
})

const featureCards = [
  {
    title: 'Rule Decks For Any Game',
    copy: 'Build challenge decks around your actual content instead of forcing a one-size-fits-all gimmick.',
    icon: 'heroicons:puzzle-piece'
  },
  {
    title: 'Viewer Participation That Stays Clear',
    copy: 'Let chat draw, compare and pressure your run without the overlay becoming noisy or amateurish.',
    icon: 'heroicons:users'
  },
  {
    title: 'Browser-Based Stream Control',
    copy: 'Configure runs, overlays and completed-run feedback without installing extra tools on every device.',
    icon: 'heroicons:computer-desktop'
  }
]

const pillars = [
  'Create a playthrough around a game and ruleset',
  'Let viewers interact with challenges and comparisons',
  'Track completed runs, public links and video VODs',
  'Keep OBS sources and stream-facing views synchronized'
]
</script>

<template>
  <div class="landing-page">
    <div class="landing-page__veil" />

    <section class="landing-hero">
      <div class="landing-hero__copy">
        <p class="landing-hero__eyebrow">Challenge Picker</p>
        <h1 class="landing-hero__title">
          Turn viewer chaos into a
          <span>premium challenge format</span>
        </h1>
        <p class="landing-hero__lead">
          A streamer tool for rule decks, live challenge pressure, public run pages and OBS-ready overlays,
          designed to feel curated instead of improvised.
        </p>

        <div class="landing-hero__actions">
          <NuxtLink to="/register" class="btn btn-primary btn-lg">
            Create Account
          </NuxtLink>
          <NuxtLink to="/login" class="btn btn-secondary btn-lg">
            Sign In
          </NuxtLink>
        </div>

        <ul class="landing-hero__pillars">
          <li v-for="pillar in pillars" :key="pillar">
            {{ pillar }}
          </li>
        </ul>
      </div>

      <div class="landing-showcase card">
        <div class="landing-showcase__head">
          <div>
            <p class="landing-showcase__kicker">Live Session Preview</p>
            <h2>Elden Ring · Chaos Deck</h2>
          </div>
          <span class="landing-showcase__badge">Run Active</span>
        </div>

        <div class="landing-showcase__stats">
          <article class="landing-showcase__stat">
            <span>Drawn Rules</span>
            <strong>12</strong>
          </article>
          <article class="landing-showcase__stat">
            <span>Viewer Votes</span>
            <strong>1,587</strong>
          </article>
          <article class="landing-showcase__stat">
            <span>Completed Runs</span>
            <strong>28</strong>
          </article>
        </div>

        <div class="landing-showcase__panel">
          <div class="landing-showcase__panel-head">
            <h3>Current pressure on stream</h3>
            <span>Deck state</span>
          </div>
          <div class="landing-showcase__rules">
            <div class="landing-showcase__rule">
              <strong>Inventory Lock</strong>
              <p>No item menu for 10 minutes</p>
            </div>
            <div class="landing-showcase__rule">
              <strong>Boss Tax</strong>
              <p>Chat decides the penalty if the attempt fails</p>
            </div>
            <div class="landing-showcase__rule">
              <strong>Controller Swap</strong>
              <p>Viewer comparison challenge now pending</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="landing-features">
      <article
        v-for="feature in featureCards"
        :key="feature.title"
        class="landing-feature card"
      >
        <div class="landing-feature__icon">
          <Icon :name="feature.icon" />
        </div>
        <h3>{{ feature.title }}</h3>
        <p>{{ feature.copy }}</p>
      </article>
    </section>
  </div>
</template>

<style scoped>
.landing-page {
  position: relative;
  min-height: 100vh;
  padding: 40px 24px 72px;
  color: var(--color-text-primary);
}

.landing-page__veil {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent 26%),
    radial-gradient(circle at top, color-mix(in srgb, var(--color-accent-primary) 12%, transparent), transparent 24%),
    var(--theme-background-art);
  pointer-events: none;
}

.landing-hero,
.landing-features {
  position: relative;
  z-index: 1;
}

.landing-hero {
  max-width: 1240px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
  gap: 24px;
  align-items: stretch;
}

.landing-hero__copy,
.landing-showcase,
.landing-feature {
  border-radius: 30px;
}

.landing-hero__copy {
  padding: 36px 12px 12px 0;
}

.landing-hero__eyebrow,
.landing-showcase__kicker {
  margin: 0 0 10px;
  font-size: 0.78rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--color-accent-primary);
}

.landing-hero__title {
  margin: 0 0 18px;
  font-size: clamp(3rem, 7vw, 5.8rem);
  line-height: 0.92;
  font-weight: 800;
  letter-spacing: -0.04em;
}

.landing-hero__title span {
  display: block;
  color: var(--color-accent-primary);
}

.landing-hero__lead {
  max-width: 720px;
  margin: 0 0 26px;
  font-size: 1.08rem;
  line-height: 1.7;
  color: var(--color-text-secondary);
}

.landing-hero__actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}

.landing-hero__pillars {
  margin: 0;
  padding-left: 20px;
  color: var(--color-text-secondary);
  display: grid;
  gap: 10px;
}

.landing-showcase {
  padding: 24px;
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-bg-card) 97%, rgba(255, 255, 255, 0.02)), color-mix(in srgb, var(--color-bg-card) 92%, rgba(0, 0, 0, 0.03)));
  border: 1px solid var(--color-border-secondary);
  box-shadow: var(--shadow-card-hover);
}

.landing-showcase__head,
.landing-showcase__panel-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.landing-showcase__head h2,
.landing-showcase__panel-head h3,
.landing-feature h3 {
  margin: 0;
  color: var(--color-text-primary);
}

.landing-showcase__badge {
  align-self: flex-start;
  display: inline-flex;
  padding: 8px 12px;
  border-radius: 999px;
  background: var(--status-active-bg);
  border: 1px solid var(--status-active-border);
  color: var(--status-active-text);
}

.landing-showcase__stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin: 18px 0;
}

.landing-showcase__stat {
  padding: 16px;
  border-radius: 22px;
  background: var(--color-bg-overlay);
  border: 1px solid var(--color-border-secondary);
}

.landing-showcase__stat span,
.landing-showcase__panel-head span,
.landing-showcase__rule p,
.landing-feature p {
  color: var(--color-text-muted);
}

.landing-showcase__stat strong {
  display: block;
  margin-top: 10px;
  font-size: 1.8rem;
}

.landing-showcase__panel {
  padding: 18px;
  border-radius: 24px;
  background: var(--color-bg-overlay);
  border: 1px solid var(--color-border-secondary);
}

.landing-showcase__rules {
  display: grid;
  gap: 10px;
  margin-top: 14px;
}

.landing-showcase__rule {
  padding: 14px 0;
  border-top: 1px solid var(--color-border-primary);
}

.landing-showcase__rule:first-child {
  border-top: 0;
  padding-top: 0;
}

.landing-features {
  max-width: 1240px;
  margin: 28px auto 0;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.landing-feature {
  padding: 22px;
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-bg-card) 97%, rgba(255, 255, 255, 0.02)), color-mix(in srgb, var(--color-bg-card) 92%, rgba(0, 0, 0, 0.03)));
  border: 1px solid var(--color-border-secondary);
  box-shadow: var(--shadow-card);
}

.landing-feature__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  margin-bottom: 14px;
  border-radius: 16px;
  background: color-mix(in srgb, var(--color-accent-primary) 10%, transparent);
  border: 1px solid color-mix(in srgb, var(--color-border-secondary) 78%, rgba(255, 255, 255, 0.04));
  color: var(--color-accent-primary);
}

.landing-feature__icon :deep(.iconify) {
  width: 22px;
  height: 22px;
}

@media (max-width: 980px) {
  .landing-hero,
  .landing-features,
  .landing-showcase__stats {
    grid-template-columns: 1fr;
  }

  .landing-hero__copy {
    padding-right: 0;
  }
}
</style>
