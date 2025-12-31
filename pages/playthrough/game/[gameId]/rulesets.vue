<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePlaythrough } from '~/composables/usePlaythrough'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const { fetchGames, fetchRulesets, createPlaythrough, games, rulesets, loading, error } = usePlaythrough()

const gameId = computed(() => parseInt(route.params.gameId as string))

const game = computed(() => games.value.find(g => g.id === gameId.value))

// Group rulesets by type
const gameSpecificRulesets = computed(() => 
  rulesets.value.filter(r => r.isGameSpecific !== false)
)

const categoryBasedRulesets = computed(() => {
  const grouped = new Map<string, typeof rulesets.value>()
  rulesets.value
    .filter(r => r.isGameSpecific === false && r.categoryName)
    .forEach(ruleset => {
      const categoryName = ruleset.categoryName!
      if (!grouped.has(categoryName)) {
        grouped.set(categoryName, [])
      }
      grouped.get(categoryName)!.push(ruleset)
    })
  return grouped
})

const rulesetCounts = computed(() => {
  const gameSpecific = gameSpecificRulesets.value.length
  const categoryBased = rulesets.value.length - gameSpecific
  return {
    total: rulesets.value.length,
    gameSpecific,
    categoryBased
  }
})

onMounted(async () => {
  // Fetch games to get game details
  if (games.value.length === 0) {
    await fetchGames()
  }
  
  // Verify game exists
  if (!game.value) {
    // Game not found, redirect back to game selection
    router.push('/playthrough/new')
    return
  }
  
  // Fetch rulesets for this game
  await fetchRulesets(gameId.value)
})

const back = () => {
  router.push('/playthrough/new')
}

const viewRuleset = (rulesetId: number) => {
  router.push(`/playthrough/game/${gameId.value}/ruleset/${rulesetId}`)
}
</script>

<template>
  <div class="playthrough-rulesets-page">
    <!-- Header -->
    <div class="playthrough-rulesets-page__header">
      <button
        @click="back"
        class="playthrough-rulesets-page__back-button"
      >
        <Icon name="heroicons:arrow-left" class="playthrough-rulesets-page__back-icon" />
        <span>Back to games</span>
      </button>

      <div v-if="game" class="playthrough-rulesets-page__game-card">
        <div class="playthrough-rulesets-page__game-content">
          <div v-if="game.image" class="playthrough-rulesets-page__game-image-wrapper">
            <img :src="game.image" :alt="game.name" class="playthrough-rulesets-page__game-image" />
          </div>
          <div v-else class="playthrough-rulesets-page__game-image-wrapper">
            <span class="playthrough-rulesets-page__game-emoji">🎮</span>
          </div>
          
          <div class="playthrough-rulesets-page__game-info">
            <h1 class="playthrough-rulesets-page__game-title">{{ game.name }}</h1>
            <p v-if="game.description" class="playthrough-rulesets-page__game-description">{{ game.description }}</p>
            <p v-if="rulesets.length > 0" class="playthrough-rulesets-page__game-ruleset-count">
              {{ rulesetCounts.gameSpecific }} / {{ rulesetCounts.total }} rulesets
              <span class="playthrough-rulesets-page__game-ruleset-count-hint">
                ({{ rulesetCounts.gameSpecific }} game-specific, {{ rulesetCounts.categoryBased }} from categories)
              </span>
            </p>
            <p class="playthrough-rulesets-page__game-hint">Click on a ruleset to view details and start your playthrough</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="playthrough-rulesets-page__loading">
      <div class="playthrough-rulesets-page__loading-spinner"></div>
      <p class="playthrough-rulesets-page__loading-text">Loading rulesets...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="playthrough-rulesets-page__error">
      <p class="playthrough-rulesets-page__error-text">{{ error }}</p>
      <button @click="back" class="playthrough-rulesets-page__error-button">
        Go Back
      </button>
    </div>

    <!-- Rulesets Display -->
    <div v-else-if="rulesets.length > 0" class="playthrough-rulesets-page__rulesets-container">
      <!-- Game-Specific Rulesets -->
      <div v-if="gameSpecificRulesets.length > 0" class="playthrough-rulesets-page__ruleset-group">
        <h2 class="playthrough-rulesets-page__ruleset-group-title">
          Game-Specific Rulesets
        </h2>
        <div class="playthrough-rulesets-page__grid">
          <button
            v-for="ruleset in gameSpecificRulesets"
            :key="ruleset.id"
            @click="viewRuleset(ruleset.id)"
            class="playthrough-rulesets-page__ruleset-card"
          >
            <div class="playthrough-rulesets-page__ruleset-header">
              <h3 class="playthrough-rulesets-page__ruleset-title">{{ ruleset.name }}</h3>
              <Icon name="heroicons:arrow-right" class="playthrough-rulesets-page__ruleset-arrow-icon" />
            </div>
            
            <p v-if="ruleset.description" class="playthrough-rulesets-page__ruleset-description">{{ ruleset.description }}</p>
            
            <div class="playthrough-rulesets-page__ruleset-footer">
              <div class="playthrough-rulesets-page__ruleset-rules-count">
                <Icon name="heroicons:list-bullet" class="playthrough-rulesets-page__ruleset-rules-icon" />
                <span>{{ ruleset.ruleCount || 0 }} rule{{ (ruleset.ruleCount || 0) !== 1 ? 's' : '' }}</span>
              </div>
            </div>
          </button>
        </div>
      </div>

      <!-- Category-Based Rulesets -->
      <template v-for="[categoryName, categoryRulesets] in categoryBasedRulesets" :key="categoryName">
        <div class="playthrough-rulesets-page__ruleset-group">
          <h2 class="playthrough-rulesets-page__ruleset-group-title">
            {{ categoryName }} Rulesets
            <span class="playthrough-rulesets-page__ruleset-group-badge">Category</span>
          </h2>
          <div class="playthrough-rulesets-page__grid">
            <button
              v-for="ruleset in categoryRulesets"
              :key="ruleset.id"
              @click="viewRuleset(ruleset.id)"
              :class="[
                'playthrough-rulesets-page__ruleset-card',
                'playthrough-rulesets-page__ruleset-card--category'
              ]"
            >
              <div class="playthrough-rulesets-page__ruleset-header">
                <h3 class="playthrough-rulesets-page__ruleset-title">{{ ruleset.name }}</h3>
                <Icon name="heroicons:arrow-right" class="playthrough-rulesets-page__ruleset-arrow-icon" />
              </div>
              
              <p v-if="ruleset.description" class="playthrough-rulesets-page__ruleset-description">{{ ruleset.description }}</p>
              
              <div class="playthrough-rulesets-page__ruleset-footer">
                <div class="playthrough-rulesets-page__ruleset-rules-count">
                  <Icon name="heroicons:list-bullet" class="playthrough-rulesets-page__ruleset-rules-icon" />
                  <span>{{ ruleset.ruleCount || 0 }} rule{{ (ruleset.ruleCount || 0) !== 1 ? 's' : '' }}</span>
                </div>
              </div>
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- No Rulesets State -->
    <div v-else class="playthrough-rulesets-page__empty">
      <Icon name="heroicons:exclamation-triangle" class="playthrough-rulesets-page__empty-icon" />
      <h3 class="playthrough-rulesets-page__empty-title">No Rulesets Available</h3>
      <p class="playthrough-rulesets-page__empty-message">This game doesn't have any rulesets yet.</p>
      <button @click="back" class="playthrough-rulesets-page__empty-button">
        Choose Another Game
      </button>
    </div>

  </div>
</template>

