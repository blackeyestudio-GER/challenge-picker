<script setup lang="ts">
import { TIMER_DESIGNS, DEFAULT_TIMER_DESIGN, isValidTimerDesign, TIMER_DESIGN_LABELS, type TimerDesign } from '~/types/obs-designs'
import TimerNumbers from '~/components/obs/timer/TimerNumbers.vue'

definePageMeta({
  layout: 'obs'
})

/**
 * Timer Overlay Page - Strategy Pattern
 * Fetches data and renders the appropriate timer design component
 */

const route = useRoute()

const { fetchActivePlaythrough, activePlaythrough } = usePlaythrough()
const { preferences, fetchPreferences } = useObsPreferences()

// Design state and validation
const design = ref<TimerDesign>(DEFAULT_TIMER_DESIGN)
const invalidDesign = ref<string | null>(null)

// Timer state
const elapsedSeconds = ref(0)
let timerInterval: number | null = null
let pollingInterval: number | null = null

// Component Strategy Mapping
const componentMap: Record<TimerDesign, any> = {
  'numbers': TimerNumbers
  // Future: 'analog': TimerAnalog, 'digital': TimerDigital, etc.
}

// Selected component based on design
const selectedComponent = computed(() => componentMap[design.value])

// Load design preferences
const loadDesign = async () => {
  if (route.query.design) {
    const requestedDesign = route.query.design as string
    if (isValidTimerDesign(requestedDesign)) {
      design.value = requestedDesign
      invalidDesign.value = null
      return
    } else {
      invalidDesign.value = requestedDesign
      design.value = DEFAULT_TIMER_DESIGN
      return
    }
  }

  try {
    await fetchPreferences()
    if (preferences.value?.timerDesign && isValidTimerDesign(preferences.value.timerDesign)) {
      design.value = preferences.value.timerDesign
      invalidDesign.value = null
    }
  } catch (err) {
    console.error('Failed to load user preferences, using default', err)
  }
}

// Calculate elapsed time
const updateElapsedTime = () => {
  if (!activePlaythrough.value?.startedAt) {
    elapsedSeconds.value = 0
    return
  }

  const startTime = new Date(activePlaythrough.value.startedAt).getTime()
  const now = Date.now()
  elapsedSeconds.value = Math.floor((now - startTime) / 1000)
}

// Watch for status changes to start/stop timer
watch(() => activePlaythrough.value?.status, (status) => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }

  if (status === 'active') {
    updateElapsedTime()
    timerInterval = setInterval(updateElapsedTime, 1000)
  }
}, { immediate: true })

// Show timer if active or paused
const showTimer = computed(() => 
  activePlaythrough.value?.status === 'active' || activePlaythrough.value?.status === 'paused'
)

// Show visibility based on user preferences
const shouldShow = computed(() => {
  if (!preferences.value || !activePlaythrough.value) return true
  
  const status = activePlaythrough.value.status
  switch (status) {
    case 'setup': return preferences.value.showTimerInSetup
    case 'active': return preferences.value.showTimerInActive
    case 'paused': return preferences.value.showTimerInPaused
    case 'completed': return preferences.value.showTimerInCompleted
    default: return true
  }
})

// Supported designs list for error message
const supportedDesigns = computed(() => 
  TIMER_DESIGNS.map(d => `"${d}" (${TIMER_DESIGN_LABELS[d]})`).join(', ')
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
  if (timerInterval) clearInterval(timerInterval)
  if (pollingInterval) clearInterval(pollingInterval)
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4" :style="{ backgroundColor: preferences?.chromaKeyColor || '#00FF00' }">
    <!-- Invalid Design Error -->
    <div v-if="invalidDesign" class="text-center max-w-2xl">
      <div class="text-red-600 text-3xl font-bold mb-4">
        Invalid Timer Design
      </div>
      <div class="text-gray-800 text-xl mb-4">
        Design "{{ invalidDesign }}" is not supported.
      </div>
      <div class="text-gray-700 text-lg">
        Supported designs: {{ supportedDesigns }}
      </div>
      <div class="mt-6 text-gray-600 text-base">
        Using default: {{ DEFAULT_TIMER_DESIGN }}
      </div>
    </div>

    <!-- No Active Playthrough -->
    <div v-else-if="!activePlaythrough" class="text-gray-600 text-2xl font-bold px-6 py-4">
      No active session
    </div>

    <!-- Timer Hidden by User Preference -->
    <div v-else-if="!shouldShow" class="text-gray-400 text-xl px-6 py-4">
      <!-- Hidden -->
    </div>

    <!-- Render Selected Timer Design Component -->
    <component
      v-else-if="showTimer && selectedComponent"
      :is="selectedComponent"
      :elapsed-seconds="elapsedSeconds"
      :is-paused="activePlaythrough?.status === 'paused'"
    />

    <!-- Not Started Yet -->
    <div v-else class="text-gray-800 text-2xl font-bold px-6 py-4">
      Waiting...
    </div>
  </div>
</template>

<style scoped>
body {
  margin: 0;
  padding: 0;
}
</style>
