<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePlaythrough } from '~/composables/usePlaythrough'
import { useAuth } from '~/composables/useAuth'
import { useTheme } from '~/composables/useTheme'
import { Icon } from '#components'
import RuleCard from '~/components/RuleCard.vue'
import ActivePlaythroughWarning from '~/components/playthrough/ActivePlaythroughWarning.vue'

const { activePlaythrough, fetchActivePlaythrough } = usePlaythrough()

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
const requireAuth = ref(false)
const allowViewerPicks = ref(false)
const creating = ref(false)
const loading = ref(true)
const error = ref<string | null>(null)

// Track difficulty level states only (no rule-level toggle)
// Structure: { ruleId: { difficultyLevels: { level: boolean } } }
const ruleStates = ref<Map<number, { difficultyLevels: Map<number, boolean> }>>(new Map())

// Card designs cache: { tarotCardIdentifier: imageBase64 }
const cardDesigns = ref<Map<string, string | null>>(new Map())

// User-editable card type pick chances (0-100% each, independent)
const cardTypePickChances = ref({
  legendary: 10,  // 10% pick chance
  court: 30,      // 30% pick chance
  basic: 60       // 60% pick chance
})

// Use pick chances directly as weights (they're already 0-100%)
const cardTypeWeights = computed(() => {
  return {
    legendary: cardTypePickChances.value.legendary || 0,
    court: cardTypePickChances.value.court || 0,
    basic: cardTypePickChances.value.basic || 0
  }
})

const loadingCardDesigns = ref(false)

// Active design set info
const activeDesignSet = ref<{
  id: number
  name: string
  type: 'full' | 'template'
  isPremium: boolean
} | null>(null)

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
  // Check for active playthrough first
  await fetchActivePlaythrough()
  
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
        tarotCardIdentifier: level.tarotCardIdentifier,
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
        iconIdentifier: rule.iconIdentifier,
        iconColor: rule.iconColor,
        iconBrightness: rule.iconBrightness,
        iconOpacity: rule.iconOpacity,
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
      cardTypePickChances: { ...cardTypePickChances.value }, // Save user's pick chance preferences
      rules // Complete rules with difficulty levels and enabled states
    }
    
    const playthroughComposable = usePlaythrough()
    const playthrough = await playthroughComposable.createPlaythrough(
      gameId.value,
      rulesetId.value,
      maxConcurrentRules.value,
      requireAuth.value,
      allowViewerPicks.value,
      configuration
    )
    
    // Redirect directly to play screen (no separate setup page needed)
    router.push(`/play/${playthrough.uuid}`)
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
    
    // Fetch active design set info first
    try {
      const designSetResponse = await $fetch<{
        success: boolean
        data: {
          id: number
          name: string
          type: 'full' | 'template'
          isPremium: boolean
        }
      }>(
        `${config.public.apiBase}/users/me/active-design-set`,
        {
          headers: getAuthHeader()
        }
      )
      
      if (designSetResponse.success && designSetResponse.data) {
        activeDesignSet.value = designSetResponse.data
      }
    } catch (err) {
      console.error('Failed to load active design set:', err)
      // Continue without design set info
    }
    
    // Fetch card designs
    const identifiersParam = Array.from(cardIdentifiers).join(',')
    const response = await $fetch<{ 
      success: boolean
      data: { 
        designSetId: number
        designSetName: string
        cardDesigns: Record<string, { 
          imageBase64: string | null
          isTemplate: boolean
          templateType: string | null
        } | null>
      }
    }>(
      `${config.public.apiBase}/design/card-designs?identifiers=${identifiersParam}`,
      {
        headers: getAuthHeader()
      }
    )
    
    if (response.success && response.data?.cardDesigns) {
      // If we didn't get design set info earlier, infer from card designs
      if (!activeDesignSet.value) {
        const cardDesignsData = response.data.cardDesigns
        const allTemplates = Object.values(cardDesignsData).every(
          design => design && design.isTemplate
        )
        activeDesignSet.value = {
          id: response.data.designSetId,
          name: response.data.designSetName,
          type: allTemplates ? 'template' : 'full',
          isPremium: false
        }
      }
      
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

// Check if a difficulty level can be toggled
const canToggleDifficultyLevel = (ruleId: number) => {
  if (!ruleset.value?.allRules) return false
  const rule = ruleset.value.allRules.find(r => r.id === ruleId)
  if (!rule) return false
  // All rules can be toggled (including default rules)
  // Users can disable default rules if they want
  return true
}

// Toggle all difficulty levels for a rule
const toggleAllDifficultyLevels = (ruleId: number) => {
  if (!ruleset.value?.allRules) return
  const rule = ruleset.value.allRules.find(r => r.id === ruleId)
  if (!rule) return
  
  const state = ruleStates.value.get(ruleId)
  if (!state) return
  
  // Check if all are enabled
  const allEnabled = rule.difficultyLevels.every(level => 
    state.difficultyLevels.get(level.difficultyLevel) !== false
  )
  
  // Toggle all to the opposite state
  const newState = !allEnabled
  rule.difficultyLevels.forEach(level => {
    state.difficultyLevels.set(level.difficultyLevel, newState)
  })
  
  // Force reactivity
  ruleStates.value = new Map(ruleStates.value)
}

// Check if all difficulty levels for a rule are enabled
const areAllDifficultyLevelsEnabled = (ruleId: number): boolean => {
  if (!ruleset.value?.allRules) return false
  const rule = ruleset.value.allRules.find(r => r.id === ruleId)
  if (!rule) return false
  
  const state = ruleStates.value.get(ruleId)
  if (!state) return true // Default to enabled if no state
  
  return rule.difficultyLevels.every(level => 
    state.difficultyLevels.get(level.difficultyLevel) !== false
  )
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
  pickrate: number // Percentage chance of drawing this card
}

// Group cards by rule for display with separators
interface RuleGroup {
  ruleId: number
  ruleName: string
  ruleType: string
  ruleDescription: string | null
  isDefault: boolean
  cards: DifficultyLevelCard[]
  pickrate: number // Total pickrate for this rule (sum of all enabled cards)
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
          canToggle,
          pickrate: 0 // Default rules are always active, not part of random draw
        })
      })
      
      // Sort cards by difficulty level
      cards.sort((a, b) => a.difficultyLevel - b.difficultyLevel)
      
      // Default rules don't have pickrates (always active, not drawn)
      const rulePickrate = 0
      
      groups.set(rule.id, {
        ruleId: rule.id,
        ruleName: rule.name,
        ruleType: rule.ruleType,
        ruleDescription: rule.description,
        isDefault: rule.isDefault,
        cards,
        pickrate: rulePickrate
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
          canToggle,
          pickrate: isEnabled ? getCardPickrate(rule.ruleType, level.amount) : 0
        })
      })
      
      // Sort cards by difficulty level
      cards.sort((a, b) => a.difficultyLevel - b.difficultyLevel)
      
      // Calculate total pickrate for this rule based on rule's total weight
      // Each rule can only be drawn once, so we calculate based on the rule's total weight
      const ruleType = rule.ruleType.toLowerCase() as keyof typeof cardTypeWeights.value
      const typeWeight = cardTypeWeights.value[ruleType] || 1
      
      // Calculate total weight for this rule (sum of all enabled difficulty levels)
      let ruleWeight = 0
      cards
        .filter(card => card.isEnabled)
        .forEach(card => {
          const cardCount = card.amount || 1
          ruleWeight += cardCount * typeWeight
        })
      
      // Rule pickrate = (ruleWeight / totalDeckWeight) * 100
      const rulePickrate = totalDeckWeight.value > 0 
        ? (ruleWeight / totalDeckWeight.value) * 100 
        : 0
      
      groups.set(rule.id, {
        ruleId: rule.id,
        ruleName: rule.name,
        ruleType: rule.ruleType,
        ruleDescription: rule.description,
        isDefault: rule.isDefault,
        cards,
        pickrate: rulePickrate
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

// Calculate total weighted value of all active cards in the deck
// Excludes default rules since they're always active and not part of the random draw
const totalDeckWeight = computed(() => {
  if (!ruleset.value?.allRules) return 0
  
  let totalWeight = 0
  ruleset.value.allRules
    .filter(rule => !rule.isDefault) // Only count optional rules (default rules are always active)
    .forEach(rule => {
      const ruleType = rule.ruleType.toLowerCase() as keyof typeof cardTypeWeights.value
      const typeWeight = cardTypeWeights.value[ruleType] || 1
      
      rule.difficultyLevels.forEach(level => {
        // Only count enabled difficulty levels
        if (isDifficultyLevelEnabled(rule.id, level.difficultyLevel)) {
          // For counter rules: amount determines card count
          // For timer rules: each level = 1 card
          const cardCount = level.amount || 1
          // Each card contributes: cardCount * typeWeight
          totalWeight += cardCount * typeWeight
        }
      })
    })
  
  return totalWeight
})

// Calculate pickrate for a specific difficulty level card
const getCardPickrate = (ruleType: string, amount: number | null): number => {
  if (totalDeckWeight.value === 0) return 0
  
  const type = ruleType.toLowerCase() as keyof typeof cardTypeWeights.value
  const typeWeight = cardTypeWeights.value[type] || 1
  const cardCount = amount || 1 // Timer rules = 1 card, counter rules = amount
  
  // Weight of this specific card(s)
  const cardWeight = cardCount * typeWeight
  
  // Pickrate = (cardWeight / totalDeckWeight) * 100
  return (cardWeight / totalDeckWeight.value) * 100
}

// Calculate recommended pickrate for a rule type (based on weights and enabled rules)
const getRecommendedPickrate = (ruleType: string): number => {
  if (!ruleset.value?.allRules || totalDeckWeight.value === 0) return 0
  
  const type = ruleType.toLowerCase() as keyof typeof cardTypeWeights.value
  const typeWeight = cardTypeWeights.value[type] || 1
  
  // Count total weight for this rule type
  let typeTotalWeight = 0
  ruleset.value.allRules
    .filter(rule => !rule.isDefault && rule.ruleType.toLowerCase() === type)
    .forEach(rule => {
      rule.difficultyLevels.forEach(level => {
        if (isDifficultyLevelEnabled(rule.id, level.difficultyLevel)) {
          const cardCount = level.amount || 1
          typeTotalWeight += cardCount * typeWeight
        }
      })
    })
  
  return (typeTotalWeight / totalDeckWeight.value) * 100
}

// Get the pick chance for a rule type (user-set value)
const getPickChance = (ruleType: string): number => {
  const type = ruleType.toLowerCase() as keyof typeof cardTypePickChances.value
  return cardTypePickChances.value[type] || 0
}

// Get hover hint text for pickrate
const getPickrateHint = (pickrate: number, ruleType: string): string => {
  if (pickrate === 0) return 'This rule will not be drawn'
  
  // Calculate how many cards need to be drawn for one of this type
  const cardsNeeded = Math.round(100 / pickrate)
  
  const typeLabel = ruleType === 'legendary' ? 'legendary' : ruleType === 'court' ? 'court' : 'basic'
  
  if (cardsNeeded <= 1) {
    return `Very likely to be drawn (${pickrate.toFixed(1)}% chance)`
  } else if (cardsNeeded <= 5) {
    return `When ${cardsNeeded} cards are drawn, about 1 might be ${typeLabel}`
  } else {
    return `When ${cardsNeeded} cards are drawn, about 1 might be ${typeLabel} (${pickrate.toFixed(1)}% chance)`
  }
}

// Calculate how many cards of a type would be drawn when 10 cards are drawn
const getCardsDrawnWhen10 = (pickChance: number): string => {
  if (pickChance === 0) return '0 cards'
  
  // Expected value: pickChance% of 10 cards
  const expectedCards = (pickChance / 100) * 10
  
  if (expectedCards < 0.1) {
    return '< 1 card'
  } else if (expectedCards < 1) {
    return '< 1 card'
  } else if (expectedCards >= 9.9) {
    return '~10 cards'
  } else {
    // Round to 1 decimal place, but show as integer if close
    const rounded = Math.round(expectedCards * 10) / 10
    if (rounded % 1 === 0) {
      return `~${rounded.toFixed(0)} cards`
    } else {
      return `~${rounded.toFixed(1)} cards`
    }
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

    <!-- Active Playthrough Warning -->
    <ActivePlaythroughWarning />

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

      <!-- Step 1: Ruleset Title -->
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
      </div>

      <!-- Step 2: Numerical Setup -->
      <div class="ruleset-detail-page__ruleset-card">
        <h2 class="ruleset-detail-page__section-title">Session Settings</h2>
        
        <!-- Privacy Setting -->
        <div class="flex items-center gap-4 py-4 border-b border-gray-700">
          <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
            <input
              v-model="requireAuth"
              type="checkbox"
              class="sr-only peer"
            />
            <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
          <div class="flex-1">
            <div class="text-base font-medium text-gray-200">Require login to view</div>
            <div class="text-sm text-gray-400 mt-1">When enabled, viewers must be logged in to watch your session</div>
          </div>
        </div>

        <!-- Max Concurrent Rules -->
        <div class="flex items-center gap-4 py-4 border-b border-gray-700">
          <input
            v-model.number="maxConcurrentRules"
            type="number"
            min="1"
            max="10"
            class="w-20 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-center font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 flex-shrink-0"
          />
          <div class="flex-1">
            <div class="text-base font-medium text-gray-200">Max concurrent rules</div>
            <div class="text-sm text-gray-400 mt-1">
              Maximum number of timed/counter rules active at once
              <span class="block mt-1">
                (Permanent rules don't count toward this limit)
              </span>
              <span v-if="defaultRulesCount > 0" class="block mt-1 text-xs text-gray-500">
                {{ defaultRulesCount }} permanent rule{{ defaultRulesCount > 1 ? 's' : '' }} always active + up to {{ maxConcurrentRules }} timed/counter rules
              </span>
            </div>
          </div>
        </div>

        <!-- Card Type Pick Chances -->
        <div class="py-4">
          <div class="text-base font-medium text-gray-200 mb-3">Card Draw Pick Chances</div>
          <div class="text-sm text-gray-400 mb-4">
            Set the pick chance (0-100%) for each card type. Each type has an independent chance and doesn't need to sum to 100%.
          </div>
          
          <div class="space-y-3">
            <!-- Legendary -->
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2 w-32">
                <input
                  v-model.number="cardTypePickChances.legendary"
                  type="number"
                  min="0"
                  max="100"
                  class="w-16 px-2 py-1.5 bg-gray-700 border border-gray-600 rounded text-white text-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                />
                <span class="text-gray-400 text-sm">%</span>
              </div>
              <div class="flex-1">
                <span class="text-gray-200 font-medium">Legendary </span>
                <span class="text-gray-400 text-sm">(~{{ getCardsDrawnWhen10(getPickChance('legendary')) }})</span>
              </div>
            </div>

            <!-- Court -->
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2 w-32">
                <input
                  v-model.number="cardTypePickChances.court"
                  type="number"
                  min="0"
                  max="100"
                  class="w-16 px-2 py-1.5 bg-gray-700 border border-gray-600 rounded text-white text-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span class="text-gray-400 text-sm">%</span>
              </div>
              <div class="flex-1">
                <span class="text-gray-200 font-medium">Court </span>
                <span class="text-gray-400 text-sm">(~{{ getCardsDrawnWhen10(getPickChance('court')) }})</span>
              </div>
            </div>

            <!-- Basic -->
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2 w-32">
                <input
                  v-model.number="cardTypePickChances.basic"
                  type="number"
                  min="0"
                  max="100"
                  class="w-16 px-2 py-1.5 bg-gray-700 border border-gray-600 rounded text-white text-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
                <span class="text-gray-400 text-sm">%</span>
              </div>
              <div class="flex-1">
                <span class="text-gray-200 font-medium">Basic </span>
                <span class="text-gray-400 text-sm">(~{{ getCardsDrawnWhen10(getPickChance('basic')) }})</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Card Selection -->
      <div class="ruleset-detail-page__ruleset-card" style="margin-top: 2rem;">
        <h2 class="ruleset-detail-page__section-title">Enable/Disable Cards</h2>

        <!-- Default Rules as Cards -->
        <div v-if="defaultRuleGroups.length > 0" class="ruleset-detail-page__rules-cards">
          <h3 class="ruleset-detail-page__rules-cards-title">Default Rules (Can Be Disabled)</h3>
          <p class="ruleset-detail-page__rules-cards-hint">
            These rules are normally always active, but you can disable difficulty levels to customize your challenge.
            Click cards to toggle difficulty levels on/off.
          </p>
          <div class="ruleset-detail-page__rules-info-box">
            <Icon name="heroicons:information-circle" class="ruleset-detail-page__rules-info-icon" />
            <p class="ruleset-detail-page__rules-info-text">
              <strong>Important:</strong> Cards can be drawn multiple times, but only one difficulty level of each rule can be active at the same time. If multiple difficulty levels of the same rule are enabled, only one will be randomly selected when drawn.
            </p>
          </div>
          
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
                  <button
                    v-if="group.cards.length > 1"
                    @click="toggleAllDifficultyLevels(group.ruleId)"
                    class="ruleset-detail-page__rule-separator-toggle-all"
                    type="button"
                    :title="areAllDifficultyLevelsEnabled(group.ruleId) ? 'Disable all difficulty levels' : 'Enable all difficulty levels'"
                  >
                    <Icon 
                      :name="areAllDifficultyLevelsEnabled(group.ruleId) ? 'heroicons:check-circle' : 'heroicons:circle'"
                      class="ruleset-detail-page__rule-separator-toggle-all-icon"
                    />
                    <span class="ruleset-detail-page__rule-separator-toggle-all-text">
                      {{ areAllDifficultyLevelsEnabled(group.ruleId) ? 'All Enabled' : 'All Disabled' }}
                    </span>
                  </button>
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
                  :pickrate="card.pickrate"
                  :is-premium-design="activeDesignSet?.type === 'full'"
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
          <div class="ruleset-detail-page__rules-info-box">
            <Icon name="heroicons:information-circle" class="ruleset-detail-page__rules-info-icon" />
            <p class="ruleset-detail-page__rules-info-text">
              <strong>Important:</strong> Cards can be drawn multiple times, but only one difficulty level of each rule can be active at the same time. If multiple difficulty levels of the same rule are enabled, only one will be randomly selected when drawn.
            </p>
          </div>
          
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
                  <button
                    v-if="group.cards.length > 1"
                    @click="toggleAllDifficultyLevels(group.ruleId)"
                    class="ruleset-detail-page__rule-separator-toggle-all"
                    type="button"
                    :title="areAllDifficultyLevelsEnabled(group.ruleId) ? 'Disable all difficulty levels' : 'Enable all difficulty levels'"
                  >
                    <Icon 
                      :name="areAllDifficultyLevelsEnabled(group.ruleId) ? 'heroicons:check-circle' : 'heroicons:circle'"
                      class="ruleset-detail-page__rule-separator-toggle-all-icon"
                    />
                    <span class="ruleset-detail-page__rule-separator-toggle-all-text">
                      {{ areAllDifficultyLevelsEnabled(group.ruleId) ? 'All Enabled' : 'All Disabled' }}
                    </span>
                  </button>
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
                  :pickrate="card.pickrate"
                  :is-premium-design="activeDesignSet?.type === 'full'"
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
        <div class="ruleset-detail-page__info-box" style="margin-top: 1.5rem;">
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

      <!-- Start Playthrough Button -->
      <div class="ruleset-detail-page__start-section">
        <button
          @click="startPlaythrough"
          :disabled="creating || !!activePlaythrough"
          class="ruleset-detail-page__start-button"
          :class="{ 'ruleset-detail-page__start-button--disabled': !!activePlaythrough }"
        >
          <span v-if="creating">Creating Playthrough...</span>
          <span v-else-if="activePlaythrough">Cannot Start - Active Playthrough Exists</span>
          <span v-else>Start Playthrough</span>
        </button>
      </div>
    </div>
  </div>
</template>


