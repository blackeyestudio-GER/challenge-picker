<script setup lang="ts">
import { STATUS_DESIGNS, DEFAULT_STATUS_DESIGN, isValidStatusDesign, STATUS_DESIGN_LABELS, type StatusDesign } from '~/types/obs-designs'
import StatusWord from '~/components/obs/status/StatusWord.vue'
import StatusSymbols from '~/components/obs/status/StatusSymbols.vue'
import StatusButtons from '~/components/obs/status/StatusButtons.vue'

/**
 * Status Overlay Page - Strategy Pattern
 * Fetches data and renders the appropriate status design component
 */

const route = useRoute()

const { fetchActivePlaythrough, activePlaythrough } = usePlaythrough()
const { preferences, fetchPreferences } = useObsPreferences()

// Design state and validation
const design = ref<StatusDesign>(DEFAULT_STATUS_DESIGN)
const invalidDesign = ref<string | null>(null)

let pollingInterval: NodeJS.Timeout | null = null

// Component Strategy Mapping
const componentMap: Record<StatusDesign, any> = {
  'word': StatusWord,
  'symbols': StatusSymbols,
  'buttons': StatusButtons
}

// Selected component based on design
const selectedComponent = computed(() => componentMap[design.value])

// Load design preferences
const loadDesign = async () => {
  if (route.query.design) {
    const requestedDesign = route.query.design as string
    if (isValidStatusDesign(requestedDesign)) {
      design.value = requestedDesign
      invalidDesign.value = null
      return
    } else {
      invalidDesign.value = requestedDesign
      design.value = DEFAULT_STATUS_DESIGN
      return
    }
  }

  try {
    await fetchPreferences()
    if (preferences.value?.statusDesign && isValidStatusDesign(preferences.value.statusDesign)) {
      design.value = preferences.value.statusDesign
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
    case 'setup': return preferences.value.showStatusInSetup
    case 'active': return preferences.value.showStatusInActive
    case 'paused': return preferences.value.showStatusInPaused
    case 'completed': return preferences.value.showStatusInCompleted
    default: return true
  }
})

// Supported designs list for error message
const supportedDesigns = computed(() => 
  STATUS_DESIGNS.map(d => `"${d}" (${STATUS_DESIGN_LABELS[d]})`).join(', ')
)

onMounted(async () => {
  await loadDesign()
  await fetchActivePlaythrough()
  
  pollingInterval = setInterval(async () => {
    try {
      await fetchActivePlaythrough()
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
  <div class="min-h-screen flex items-center justify-center p-4">
    <!-- Invalid Design Error -->
    <div v-if="invalidDesign" class="text-center max-w-2xl">
      <div class="text-red-600 text-3xl font-bold mb-4">
        Invalid Status Design
      </div>
      <div class="text-gray-800 text-xl mb-4">
        Design "{{ invalidDesign }}" is not supported.
      </div>
      <div class="text-gray-700 text-lg">
        Supported designs: {{ supportedDesigns }}
      </div>
      <div class="mt-6 text-gray-600 text-base">
        Using default: {{ DEFAULT_STATUS_DESIGN }}
      </div>
    </div>

    <!-- No Active Playthrough -->
    <div v-else-if="!activePlaythrough" class="text-gray-600 text-2xl font-bold px-6 py-4">
      No active session
    </div>

    <!-- Status Hidden by User Preference -->
    <div v-else-if="!shouldShow" class="text-gray-400 text-xl px-6 py-4">
      <!-- Hidden -->
    </div>

    <!-- Render Selected Status Design Component -->
    <component
      v-else-if="activePlaythrough && selectedComponent"
      :is="selectedComponent"
      :status="activePlaythrough.status"
    />
  </div>
</template>

<style scoped>
body {
  margin: 0;
  padding: 0;
}
</style>
