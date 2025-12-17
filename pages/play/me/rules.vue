<script setup lang="ts">
import { RULES_DESIGNS, DEFAULT_RULES_DESIGN, isValidRulesDesign, RULES_DESIGN_LABELS, type RulesDesign } from '~/types/obs-designs'
import RulesList from '~/components/obs/rules/RulesList.vue'

/**
 * Rules Overlay Page - Strategy Pattern
 * Fetches data and renders the appropriate rules design component
 */

const route = useRoute()

const { fetchActivePlaythrough, activePlaythrough, fetchPlayScreen, playScreenData } = usePlaythrough()
const { preferences, fetchPreferences } = useObsPreferences()

// Design state and validation
const design = ref<RulesDesign>(DEFAULT_RULES_DESIGN)
const invalidDesign = ref<string | null>(null)

let pollingInterval: NodeJS.Timeout | null = null

// Component Strategy Mapping
const componentMap: Record<RulesDesign, any> = {
  'list': RulesList
  // Future: 'grid': RulesGrid, 'cards': RulesCards, etc.
}

// Selected component based on design
const selectedComponent = computed(() => componentMap[design.value])

// Load design preferences
const loadDesign = async () => {
  if (route.query.design) {
    const requestedDesign = route.query.design as string
    if (isValidRulesDesign(requestedDesign)) {
      design.value = requestedDesign
      invalidDesign.value = null
      return
    } else {
      invalidDesign.value = requestedDesign
      design.value = DEFAULT_RULES_DESIGN
      return
    }
  }

  try {
    await fetchPreferences()
    if (preferences.value?.rulesDesign && isValidRulesDesign(preferences.value.rulesDesign)) {
      design.value = preferences.value.rulesDesign
      invalidDesign.value = null
    }
  } catch (err) {
    console.error('Failed to load user preferences, using default', err)
  }
}

// Show visibility based on user preferences
const shouldShow = computed(() => {
  if (!preferences.value || !activePlaythrough.value) return true
  
  const status = activePlaythrough.value.status
  switch (status) {
    case 'setup': return preferences.value.showRulesInSetup
    case 'active': return preferences.value.showRulesInActive
    case 'paused': return preferences.value.showRulesInPaused
    case 'completed': return preferences.value.showRulesInCompleted
    default: return true
  }
})

// Only show during active/paused states
const isActiveSession = computed(() => 
  activePlaythrough.value?.status === 'active' || activePlaythrough.value?.status === 'paused'
)

// Supported designs list for error message
const supportedDesigns = computed(() => 
  RULES_DESIGNS.map(d => `"${d}" (${RULES_DESIGN_LABELS[d]})`).join(', ')
)

onMounted(async () => {
  await loadDesign()
  await fetchActivePlaythrough()
  
  if (activePlaythrough.value?.uuid) {
    await fetchPlayScreen(activePlaythrough.value.uuid)
  }
  
  pollingInterval = setInterval(async () => {
    try {
      await fetchActivePlaythrough()
      if (activePlaythrough.value?.uuid) {
        await fetchPlayScreen(activePlaythrough.value.uuid)
      }
    } catch (err) {
      console.error('Polling error:', err)
    }
  }, 2000)
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
})
</script>

<template>
  <div class="min-h-screen p-8">
    <!-- Invalid Design Error -->
    <div v-if="invalidDesign" class="text-center max-w-2xl mx-auto">
      <div class="text-red-600 text-3xl font-bold mb-4">
        Invalid Rules Design
      </div>
      <div class="text-gray-800 text-xl mb-4">
        Design "{{ invalidDesign }}" is not supported.
      </div>
      <div class="text-gray-700 text-lg">
        Supported designs: {{ supportedDesigns }}
      </div>
      <div class="mt-6 text-gray-600 text-base">
        Using default: {{ DEFAULT_RULES_DESIGN }}
      </div>
    </div>

    <!-- No Active Playthrough -->
    <div v-else-if="!activePlaythrough" class="text-center text-gray-600 text-2xl font-bold py-12">
      No active session
    </div>

    <!-- Rules Hidden by User Preference -->
    <div v-else-if="!shouldShow" class="text-center text-gray-400 text-xl py-12">
      <!-- Hidden -->
    </div>

    <!-- Render Selected Rules Design Component -->
    <component
      v-else-if="isActiveSession && playScreenData && selectedComponent"
      :is="selectedComponent"
      :active-rules="playScreenData.activeRules"
    />

    <!-- Not in active/paused state -->
    <div v-else-if="activePlaythrough" class="text-center py-12">
      <div class="text-3xl font-bold text-gray-600">
        {{ activePlaythrough.status === 'setup' ? 'Setting up...' : 'Session ended' }}
      </div>
    </div>
  </div>
</template>

<style scoped>
body {
  margin: 0;
  padding: 0;
}
</style>
