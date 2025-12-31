<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePlaythrough } from '~/composables/usePlaythrough'
import { useAuth } from '~/composables/useAuth'
import { useTheme } from '~/composables/useTheme'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const { getAuthHeader } = useAuth()
const { getRuleTypeBadgeClass } = useTheme()
const config = useRuntimeConfig()

const gameId = computed(() => parseInt(route.params.gameId as string))
const rulesetId = computed(() => parseInt(route.params.rulesetId as string))
const maxConcurrentRules = ref(3)
const creating = ref(false)
const loading = ref(true)
const error = ref<string | null>(null)

interface RulesetDetail {
  id: number
  name: string
  description: string | null
  games: Array<{ id: number; name: string }>
  defaultRules: Array<{ id: number; name: string; ruleType: string }>
  ruleCount: number
  isGameSpecific: boolean
  categoryName: string | null
  categoryId: number | null
}

const ruleset = ref<RulesetDetail | null>(null)
const game = ref<{ id: number; name: string; image: string | null } | null>(null)

onMounted(async () => {
  await Promise.all([
    loadRuleset(),
    loadGame()
  ])
})

const loadRuleset = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $fetch<{ success: boolean; data: RulesetDetail }>(
      `${config.public.apiBase}/rulesets/${rulesetId.value}`,
      {
        headers: getAuthHeader()
      }
    )
    if (response.success) {
      ruleset.value = response.data
    }
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to load ruleset'
    console.error('Failed to load ruleset:', err)
  } finally {
    loading.value = false
  }
}

const loadGame = async () => {
  try {
    const playthroughComposable = usePlaythrough()
    if (playthroughComposable.games.value.length === 0) {
      await playthroughComposable.fetchGames()
    }
    const foundGame = playthroughComposable.games.value.find(g => g.id === gameId.value)
    if (foundGame) {
      game.value = {
        id: foundGame.id,
        name: foundGame.name,
        image: foundGame.image
      }
    }
  } catch (err) {
    console.error('Failed to load game:', err)
  }
}

const back = () => {
  router.push(`/playthrough/game/${gameId.value}/rulesets`)
}

const startPlaythrough = async () => {
  if (!ruleset.value) return
  
  creating.value = true
  error.value = null
  try {
    const playthroughComposable = usePlaythrough()
    const playthrough = await playthroughComposable.createPlaythrough(
      gameId.value,
      rulesetId.value,
      maxConcurrentRules.value
    )
    
    // Redirect to setup page
    router.push(`/playthrough/${playthrough.uuid}/setup`)
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to create playthrough'
    console.error('Failed to create playthrough:', err)
  } finally {
    creating.value = false
  }
}

const getRuleTypeLabel = (ruleType: string) => {
  switch (ruleType) {
    case 'legendary':
      return 'Legendary'
    case 'court':
      return 'Court'
    case 'basic':
      return 'Basic'
    default:
      return ruleType
  }
}
</script>

<template>
  <div class="ruleset-detail-page">
    <!-- Header -->
    <div class="ruleset-detail-page__header">
      <button
        @click="back"
        class="ruleset-detail-page__back-button"
      >
        <Icon name="heroicons:arrow-left" class="ruleset-detail-page__back-icon" />
        <span>Back to rulesets</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="ruleset-detail-page__loading">
      <div class="ruleset-detail-page__loading-spinner"></div>
      <p class="ruleset-detail-page__loading-text">Loading ruleset...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="ruleset-detail-page__error">
      <p class="ruleset-detail-page__error-text">{{ error }}</p>
      <button @click="back" class="ruleset-detail-page__error-button">
        Go Back
      </button>
    </div>

    <!-- Ruleset Details -->
    <div v-else-if="ruleset" class="ruleset-detail-page__content">
      <!-- Game Info Card -->
      <div v-if="game" class="ruleset-detail-page__game-card">
        <div v-if="game.image" class="ruleset-detail-page__game-image-wrapper">
          <img :src="game.image" :alt="game.name" class="ruleset-detail-page__game-image" />
        </div>
        <div v-else class="ruleset-detail-page__game-image-wrapper">
          <span class="ruleset-detail-page__game-emoji">🎮</span>
        </div>
        <div class="ruleset-detail-page__game-info">
          <h2 class="ruleset-detail-page__game-title">{{ game.name }}</h2>
        </div>
      </div>

      <!-- Ruleset Info Card -->
      <div class="ruleset-detail-page__ruleset-card">
        <div class="ruleset-detail-page__ruleset-header">
          <div>
            <h1 class="ruleset-detail-page__ruleset-title">{{ ruleset.name }}</h1>
            <div v-if="ruleset.isGameSpecific === false && ruleset.categoryName" class="ruleset-detail-page__ruleset-badge">
              <Icon name="heroicons:tag" class="ruleset-detail-page__ruleset-badge-icon" />
              <span>{{ ruleset.categoryName }} Ruleset</span>
            </div>
            <div v-else class="ruleset-detail-page__ruleset-badge ruleset-detail-page__ruleset-badge--game-specific">
              <Icon name="heroicons:star" class="ruleset-detail-page__ruleset-badge-icon" />
              <span>Game-Specific</span>
            </div>
          </div>
        </div>

        <p v-if="ruleset.description" class="ruleset-detail-page__ruleset-description">
          {{ ruleset.description }}
        </p>

        <!-- Rules Summary -->
        <div class="ruleset-detail-page__rules-summary">
          <div class="ruleset-detail-page__rules-summary-item">
            <Icon name="heroicons:list-bullet" class="ruleset-detail-page__rules-summary-icon" />
            <span class="ruleset-detail-page__rules-summary-label">Total Rules:</span>
            <span class="ruleset-detail-page__rules-summary-value">{{ ruleset.ruleCount }}</span>
          </div>
          <div v-if="ruleset.defaultRules.length > 0" class="ruleset-detail-page__rules-summary-item">
            <Icon name="heroicons:sparkles" class="ruleset-detail-page__rules-summary-icon" />
            <span class="ruleset-detail-page__rules-summary-label">Default Rules:</span>
            <span class="ruleset-detail-page__rules-summary-value">{{ ruleset.defaultRules.length }}</span>
          </div>
        </div>

        <!-- Default Rules List -->
        <div v-if="ruleset.defaultRules.length > 0" class="ruleset-detail-page__default-rules">
          <h3 class="ruleset-detail-page__default-rules-title">Default Rules (Always Active)</h3>
          <div class="ruleset-detail-page__default-rules-list">
            <div
              v-for="rule in ruleset.defaultRules"
              :key="rule.id"
              class="ruleset-detail-page__default-rule-item"
            >
              <span :class="getRuleTypeBadgeClass(rule.ruleType)" class="ruleset-detail-page__rule-type-badge">
                {{ getRuleTypeLabel(rule.ruleType) }}
              </span>
              <span class="ruleset-detail-page__rule-name">{{ rule.name }}</span>
            </div>
          </div>
        </div>

        <!-- Note about rules -->
        <div class="ruleset-detail-page__info-box">
          <Icon name="heroicons:information-circle" class="ruleset-detail-page__info-icon" />
          <div class="ruleset-detail-page__info-content">
            <p class="ruleset-detail-page__info-text">
              This ruleset contains <strong>{{ ruleset.ruleCount }} rules</strong> total.
              <span v-if="ruleset.defaultRules.length > 0">
                {{ ruleset.defaultRules.length }} are permanent (always active), and the remaining rules can be activated during your playthrough.
              </span>
              <span v-else>
                All rules can be activated during your playthrough.
              </span>
            </p>
            <p v-if="ruleset.isGameSpecific === false && ruleset.categoryName" class="ruleset-detail-page__info-text ruleset-detail-page__info-text--muted">
              This ruleset is designed for the <strong>{{ ruleset.categoryName }}</strong> category and may work with other games in this category.
            </p>
          </div>
        </div>
      </div>

      <!-- Start Playthrough Section -->
      <div class="ruleset-detail-page__start-section">
        <div class="ruleset-detail-page__start-config">
          <label class="ruleset-detail-page__start-label">
            Max concurrent rules:
          </label>
          <input
            v-model.number="maxConcurrentRules"
            type="number"
            min="1"
            max="10"
            class="ruleset-detail-page__start-input"
          />
          <p class="ruleset-detail-page__start-hint">
            Maximum number of rules that can be active at the same time during your playthrough.
          </p>
        </div>

        <button
          @click="startPlaythrough"
          :disabled="creating"
          class="ruleset-detail-page__start-button"
        >
          <span v-if="creating">Creating Playthrough...</span>
          <span v-else>Start Playthrough</span>
        </button>
      </div>
    </div>
  </div>
</template>


