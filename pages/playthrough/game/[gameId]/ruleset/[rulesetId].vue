<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePlaythrough } from '~/composables/usePlaythrough'
import { useAuth } from '~/composables/useAuth'
import { useTheme } from '~/composables/useTheme'
import { Icon } from '#components'
import RuleCard from '~/components/RuleCard.vue'

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

// Track difficulty level states only (no rule-level toggle)
// Structure: { ruleId: { difficultyLevels: { level: boolean } } }
const ruleStates = ref<Map<number, { difficultyLevels: Map<number, boolean> }>>(new Map())

// Card designs cache: { tarotCardIdentifier: imageBase64 }
const cardDesigns = ref<Map<string, string | null>>(new Map())
const loadingCardDesigns = ref(false)

interface DifficultyLevel {
  difficultyLevel: number
  durationSeconds: number | null
  amount: number | null
  tarotCardIdentifier: string | null // Unique card per difficulty level
}

interface RuleDetail {
  id: number
  name: string
  ruleType: string
  isDefault: boolean
  difficultyLevels: DifficultyLevel[]
  description: string | null
  tarotCardIdentifier: string | null
  iconIdentifier: string | null
  iconColor: string | null
  iconBrightness: number | null
  iconOpacity: number | null
}

interface RulesetDetail {
  id: number
  name: string
  description: string | null
  games: Array<{ id: number; name: string }>
  defaultRules: Array<{ id: number; name: string; ruleType: string }>
  allRules: RuleDetail[]
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
      // Initialize all difficulty levels as enabled
      if (ruleset.value.allRules) {
        ruleStates.value = new Map()
        ruleset.value.allRules.forEach(rule => {
          const difficultyLevels = new Map<number, boolean>()
          rule.difficultyLevels.forEach(level => {
            difficultyLevels.set(level.difficultyLevel, true) // All enabled by default
          })
          ruleStates.value.set(rule.id, {
            difficultyLevels
          })
        })
        
        // Load card designs for all tarot cards
        await loadCardDesigns()
      }
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
    // Build configuration snapshot (revision-safe) with difficulty level states only
    const defaultRuleIds = ruleset.value.defaultRules.map(r => r.id)
    const rules = ruleset.value.allRules.map(rule => {
      const state = ruleStates.value.get(rule.id)
      const difficultyLevels = rule.difficultyLevels.map(level => ({
        difficultyLevel: level.difficultyLevel,
        durationSeconds: level.durationSeconds,
        amount: level.amount,
        description: level.description,
        enabled: state?.difficultyLevels.get(level.difficultyLevel) ?? true
      }))
      
      // Rule is considered enabled if at least one difficulty level is enabled
      const hasEnabledLevel = difficultyLevels.some(dl => dl.enabled)
      
      return {
        id: rule.id,
        name: rule.name,
        ruleType: rule.ruleType,
        isDefault: rule.isDefault,
        description: rule.description,
        enabled: hasEnabledLevel, // Derived from difficulty levels
        difficultyLevels
      }
    })
    
    const configuration = {
      version: '1.0',
      rulesetId: ruleset.value.id,
      rulesetName: ruleset.value.name,
      maxConcurrentRules: maxConcurrentRules.value,
      defaultRules: defaultRuleIds,
      rules // Complete rules with difficulty levels and enabled states
    }
    
    const playthroughComposable = usePlaythrough()
    const playthrough = await playthroughComposable.createPlaythrough(
      gameId.value,
      rulesetId.value,
      maxConcurrentRules.value,
      configuration
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

// Load card designs for all tarot cards in the ruleset
const loadCardDesigns = async () => {
  if (!ruleset.value?.allRules) return
  
  loadingCardDesigns.value = true
  try {
    // Collect all unique tarot card identifiers from difficulty levels
    const cardIdentifiers = new Set<string>()
    ruleset.value.allRules.forEach(rule => {
      rule.difficultyLevels.forEach(level => {
        // Use difficulty-level-specific card identifier if available, otherwise fall back to rule-level
        const cardIdentifier = level.tarotCardIdentifier || rule.tarotCardIdentifier
        if (cardIdentifier) {
          cardIdentifiers.add(cardIdentifier)
        }
      })
    })
    
    if (cardIdentifiers.size === 0) {
      loadingCardDesigns.value = false
      return
    }
    
    // Fetch card designs
    const identifiersParam = Array.from(cardIdentifiers).join(',')
    const response = await $fetch<{ success: boolean; data: { cardDesigns: Record<string, { imageBase64: string | null } | null> } }>(
      `${config.public.apiBase}/design/card-designs?identifiers=${identifiersParam}`,
      {
        headers: getAuthHeader()
      }
    )
    
    if (response.success && response.data?.cardDesigns) {
      cardDesigns.value = new Map()
      Object.entries(response.data.cardDesigns).forEach(([identifier, design]) => {
        if (design && design.imageBase64) {
          cardDesigns.value.set(identifier, design.imageBase64)
        } else {
          cardDesigns.value.set(identifier, null)
        }
      })
    }
  } catch (err: any) {
    console.error('Failed to load card designs:', err)
    // Don't show error to user, just use placeholder cards
  } finally {
    loadingCardDesigns.value = false
  }
}

const toggleDifficultyLevel = (ruleId: number, difficultyLevel: number) => {
  const state = ruleStates.value.get(ruleId)
  if (!state) return
  
  const currentEnabled = state.difficultyLevels.get(difficultyLevel) ?? true
  const newEnabled = !currentEnabled
  state.difficultyLevels.set(difficultyLevel, newEnabled)
  
  // Force reactivity
  ruleStates.value = new Map(ruleStates.value)
}

const isDifficultyLevelEnabled = (ruleId: number, difficultyLevel: number) => {
  const state = ruleStates.value.get(ruleId)
  return state?.difficultyLevels.get(difficultyLevel) ?? true
}

// Check if a difficulty level can be toggled (always true now, since we removed rule-level toggle)
const canToggleDifficultyLevel = (ruleId: number) => {
  const state = ruleStates.value.get(ruleId)
  if (!state) return false
  // Can toggle if there's more than one difficulty level
  return state.difficultyLevels.size > 1
}

const formatDuration = (seconds: number | null): string => {
  if (!seconds) return 'N/A'
  if (seconds < 60) return `${seconds}s`
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m`
  return `${Math.floor(seconds / 3600)}h ${Math.floor((seconds % 3600) / 60)}m`
}

// Flatten all rules into difficulty level cards
// Each card represents one difficulty level of one rule
interface DifficultyLevelCard {
  ruleId: number
  ruleName: string
  ruleType: string
  ruleDescription: string | null
  isDefault: boolean
  difficultyLevel: number
  durationSeconds: number | null
  amount: number | null
  tarotCardIdentifier: string | null
  cardImageBase64: string | null
  iconIdentifier: string | null
  iconColor: string | null
  iconBrightness: number | null
  iconOpacity: number | null
  isEnabled: boolean
  canToggle: boolean
}

// Group cards by rule for display with separators
interface RuleGroup {
  ruleId: number
  ruleName: string
  ruleType: string
  ruleDescription: string | null
  isDefault: boolean
  cards: DifficultyLevelCard[]
}

// Get default rules grouped by rule
const defaultRuleGroups = computed<RuleGroup[]>(() => {
  if (!ruleset.value?.allRules) return []
  
  const groups = new Map<number, RuleGroup>()
  
  ruleset.value.allRules
    .filter(rule => rule.isDefault)
    .forEach(rule => {
      const cards: DifficultyLevelCard[] = []
      
      rule.difficultyLevels.forEach(level => {
        const isEnabled = isDifficultyLevelEnabled(rule.id, level.difficultyLevel)
        const canToggle = canToggleDifficultyLevel(rule.id)
        const cardIdentifier = level.tarotCardIdentifier || rule.tarotCardIdentifier
        const cardImageBase64 = cardIdentifier ? cardDesigns.value.get(cardIdentifier) ?? null : null
        
        cards.push({
          ruleId: rule.id,
          ruleName: rule.name,
          ruleType: rule.ruleType,
          ruleDescription: rule.description,
          isDefault: rule.isDefault,
          difficultyLevel: level.difficultyLevel,
          durationSeconds: level.durationSeconds,
          amount: level.amount,
          tarotCardIdentifier: cardIdentifier,
          cardImageBase64,
          iconIdentifier: rule.iconIdentifier,
          iconColor: rule.iconColor,
          iconBrightness: rule.iconBrightness,
          iconOpacity: rule.iconOpacity,
          isEnabled,
          canToggle
        })
      })
      
      // Sort cards by difficulty level
      cards.sort((a, b) => a.difficultyLevel - b.difficultyLevel)
      
      groups.set(rule.id, {
        ruleId: rule.id,
        ruleName: rule.name,
        ruleType: rule.ruleType,
        ruleDescription: rule.description,
        isDefault: rule.isDefault,
        cards
      })
    })
  
  // Sort groups by rule type, then by rule name
  return Array.from(groups.values()).sort((a, b) => {
    const typeOrder = { legendary: 0, court: 1, basic: 2 }
    const typeDiff = (typeOrder[a.ruleType as keyof typeof typeOrder] ?? 3) - (typeOrder[b.ruleType as keyof typeof typeOrder] ?? 3)
    if (typeDiff !== 0) return typeDiff
    return a.ruleName.localeCompare(b.ruleName)
  })
})

// Get optional rules grouped by rule
const optionalRuleGroups = computed<RuleGroup[]>(() => {
  if (!ruleset.value?.allRules) return []
  
  const groups = new Map<number, RuleGroup>()
  
  ruleset.value.allRules
    .filter(rule => !rule.isDefault)
    .forEach(rule => {
      const cards: DifficultyLevelCard[] = []
      
      rule.difficultyLevels.forEach(level => {
        const isEnabled = isDifficultyLevelEnabled(rule.id, level.difficultyLevel)
        const canToggle = canToggleDifficultyLevel(rule.id)
        const cardIdentifier = level.tarotCardIdentifier || rule.tarotCardIdentifier
        const cardImageBase64 = cardIdentifier ? cardDesigns.value.get(cardIdentifier) ?? null : null
        
        cards.push({
          ruleId: rule.id,
          ruleName: rule.name,
          ruleType: rule.ruleType,
          ruleDescription: rule.description,
          isDefault: rule.isDefault,
          difficultyLevel: level.difficultyLevel,
          durationSeconds: level.durationSeconds,
          amount: level.amount,
          tarotCardIdentifier: cardIdentifier,
          cardImageBase64,
          iconIdentifier: rule.iconIdentifier,
          iconColor: rule.iconColor,
          iconBrightness: rule.iconBrightness,
          iconOpacity: rule.iconOpacity,
          isEnabled,
          canToggle
        })
      })
      
      // Sort cards by difficulty level
      cards.sort((a, b) => a.difficultyLevel - b.difficultyLevel)
      
      groups.set(rule.id, {
        ruleId: rule.id,
        ruleName: rule.name,
        ruleType: rule.ruleType,
        ruleDescription: rule.description,
        isDefault: rule.isDefault,
        cards
      })
    })
  
  // Sort groups by rule type, then by rule name
  return Array.from(groups.values()).sort((a, b) => {
    const typeOrder = { legendary: 0, court: 1, basic: 2 }
    const typeDiff = (typeOrder[a.ruleType as keyof typeof typeOrder] ?? 3) - (typeOrder[b.ruleType as keyof typeof typeOrder] ?? 3)
    if (typeDiff !== 0) return typeDiff
    return a.ruleName.localeCompare(b.ruleName)
  })
})

// Get default rules count (for display)
const defaultRulesCount = computed(() => {
  return ruleset.value?.defaultRules.length || 0
})
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

        <!-- Default Rules as Cards -->
        <div v-if="defaultRuleGroups.length > 0" class="ruleset-detail-page__rules-cards">
          <h3 class="ruleset-detail-page__rules-cards-title">Default Rules (Can Be Disabled)</h3>
          <p class="ruleset-detail-page__rules-cards-hint">
            These rules are normally always active, but you can disable difficulty levels to customize your challenge.
            Click cards to toggle difficulty levels on/off.
          </p>
          
          <div v-if="loadingCardDesigns" class="ruleset-detail-page__cards-loading">
            <div class="ruleset-detail-page__loading-spinner"></div>
            <p class="ruleset-detail-page__loading-text">Loading card designs...</p>
          </div>
          
          <div v-else class="ruleset-detail-page__rules-section">
            <div
              v-for="(group, groupIndex) in defaultRuleGroups"
              :key="`default-${group.ruleId}`"
              class="ruleset-detail-page__rule-group"
            >
              <!-- Rule Separator/Header -->
              <div class="ruleset-detail-page__rule-separator">
                <div class="ruleset-detail-page__rule-separator-line"></div>
                <div class="ruleset-detail-page__rule-separator-content">
                  <span :class="getRuleTypeBadgeClass(group.ruleType)" class="ruleset-detail-page__rule-separator-badge">
                    {{ getRuleTypeLabel(group.ruleType) }}
                  </span>
                  <span class="ruleset-detail-page__rule-separator-name">{{ group.ruleName }}</span>
                  <span v-if="group.ruleDescription" class="ruleset-detail-page__rule-separator-description">
                    {{ group.ruleDescription }}
                  </span>
                </div>
                <div class="ruleset-detail-page__rule-separator-line"></div>
              </div>
              
              <!-- Cards for this rule -->
              <div class="ruleset-detail-page__cards-grid">
                <RuleCard
                  v-for="(card, cardIndex) in group.cards"
                  :key="`${card.ruleId}-${card.difficultyLevel}-${cardIndex}`"
                  :rule-id="card.ruleId"
                  :rule-name="card.ruleName"
                  :rule-type="card.ruleType"
                  :rule-description="card.ruleDescription"
                  :difficulty-level="card.difficultyLevel"
                  :duration-seconds="card.durationSeconds"
                  :amount="card.amount"
                  :tarot-card-identifier="card.tarotCardIdentifier"
                  :card-image-base64="card.cardImageBase64"
                  :icon-identifier="card.iconIdentifier"
                  :icon-color="card.iconColor"
                  :icon-brightness="card.iconBrightness"
                  :icon-opacity="card.iconOpacity"
                  :is-enabled="card.isEnabled"
                  :is-default="card.isDefault"
                  :can-toggle="card.canToggle"
                  @toggle="toggleDifficultyLevel"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Optional Rules as Cards -->
        <div v-if="optionalRuleGroups.length > 0" class="ruleset-detail-page__rules-cards">
          <h3 class="ruleset-detail-page__rules-cards-title">Optional Rules (Can Be Activated)</h3>
          <p class="ruleset-detail-page__rules-cards-hint">
            Toggle difficulty levels on/off to customize your challenge. Disabled levels won't appear during your playthrough.
            Click cards to toggle difficulty levels on/off.
          </p>
          
          <div v-if="loadingCardDesigns" class="ruleset-detail-page__cards-loading">
            <div class="ruleset-detail-page__loading-spinner"></div>
            <p class="ruleset-detail-page__loading-text">Loading card designs...</p>
          </div>
          
          <div v-else class="ruleset-detail-page__rules-section">
            <div
              v-for="(group, groupIndex) in optionalRuleGroups"
              :key="`optional-${group.ruleId}`"
              class="ruleset-detail-page__rule-group"
            >
              <!-- Rule Separator/Header -->
              <div class="ruleset-detail-page__rule-separator">
                <div class="ruleset-detail-page__rule-separator-line"></div>
                <div class="ruleset-detail-page__rule-separator-content">
                  <span :class="getRuleTypeBadgeClass(group.ruleType)" class="ruleset-detail-page__rule-separator-badge">
                    {{ getRuleTypeLabel(group.ruleType) }}
                  </span>
                  <span class="ruleset-detail-page__rule-separator-name">{{ group.ruleName }}</span>
                  <span v-if="group.ruleDescription" class="ruleset-detail-page__rule-separator-description">
                    {{ group.ruleDescription }}
                  </span>
                </div>
                <div class="ruleset-detail-page__rule-separator-line"></div>
              </div>
              
              <!-- Cards for this rule -->
              <div class="ruleset-detail-page__cards-grid">
                <RuleCard
                  v-for="(card, cardIndex) in group.cards"
                  :key="`${card.ruleId}-${card.difficultyLevel}-${cardIndex}`"
                  :rule-id="card.ruleId"
                  :rule-name="card.ruleName"
                  :rule-type="card.ruleType"
                  :rule-description="card.ruleDescription"
                  :difficulty-level="card.difficultyLevel"
                  :duration-seconds="card.durationSeconds"
                  :amount="card.amount"
                  :tarot-card-identifier="card.tarotCardIdentifier"
                  :card-image-base64="card.cardImageBase64"
                  :icon-identifier="card.iconIdentifier"
                  :icon-color="card.iconColor"
                  :icon-brightness="card.iconBrightness"
                  :icon-opacity="card.iconOpacity"
                  :is-enabled="card.isEnabled"
                  :is-default="card.isDefault"
                  :can-toggle="card.canToggle"
                  @toggle="toggleDifficultyLevel"
                />
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="defaultRuleGroups.length === 0 && optionalRuleGroups.length === 0 && !loading" class="ruleset-detail-page__no-rules">
          <Icon name="heroicons:exclamation-triangle" class="ruleset-detail-page__no-rules-icon" />
          <p class="ruleset-detail-page__no-rules-text">No rules available for this ruleset.</p>
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
            Maximum number of optional rules that can be active at the same time during your playthrough.
            <span v-if="defaultRulesCount > 0">
              <strong>Note:</strong> This excludes the {{ defaultRulesCount }} permanent rule{{ defaultRulesCount !== 1 ? 's' : '' }}, so you'll have {{ defaultRulesCount }} + {{ maxConcurrentRules }} = {{ defaultRulesCount + maxConcurrentRules }} total active rules.
            </span>
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


