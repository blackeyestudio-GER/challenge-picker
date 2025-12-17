<script setup lang="ts">
import { TIMER_DESIGNS, DEFAULT_TIMER_DESIGN, isValidTimerDesign, TIMER_DESIGN_LABELS, type TimerDesign } from '~/types/obs-designs'

// Public overlay page - just shows the timer
const route = useRoute()
const uuid = route.params.uuid as string

const { fetchPlayScreen, startPlayScreenPolling, playScreenData, loading, error } = usePlaythrough()

// Design state and validation
const design = ref<TimerDesign>(DEFAULT_TIMER_DESIGN)
const invalidDesign = ref<string | null>(null)

// Fetch user's preferences if no query param provided
const loadDesign = async () => {
  // 1. Check query param first (explicit override)
  if (route.query.design) {
    const requestedDesign = route.query.design as string
    if (isValidTimerDesign(requestedDesign)) {
      design.value = requestedDesign
      invalidDesign.value = null
      return
    } else {
      // Invalid design requested
      invalidDesign.value = requestedDesign
      design.value = DEFAULT_TIMER_DESIGN
      return
    }
  }

  // 2. Fetch user's saved preference from their playthrough
  if (playScreenData.value?.id) {
    try {
      const config = useRuntimeConfig()
      const response = await $fetch<{ success: boolean; data: { timerDesign: string } }>(
        `${config.public.apiBase}/api/play/${uuid}/preferences`
      )
      if (response.success && isValidTimerDesign(response.data.timerDesign)) {
        design.value = response.data.timerDesign
        invalidDesign.value = null
      }
    } catch (err) {
      console.error('Failed to load user preferences, using default', err)
    }
  }
}

// Load design after play screen data is fetched
watch(() => playScreenData.value, (data) => {
  if (data) {
    loadDesign()
  }
}, { immediate: true })

// Supported designs list for error message
const supportedDesigns = computed(() => 
  TIMER_DESIGNS.map(d => `"${d}" (${TIMER_DESIGN_LABELS[d]})`).join(', ')
)

const elapsedSeconds = ref(0)
let timerInterval: NodeJS.Timeout | null = null
let stopPolling: (() => void) | null = null

// Calculate elapsed time from startedAt
const updateElapsedTime = () => {
  if (!playScreenData.value?.startedAt) {
    elapsedSeconds.value = 0
    return
  }

  const startTime = new Date(playScreenData.value.startedAt).getTime()
  const now = Date.now()
  elapsedSeconds.value = Math.floor((now - startTime) / 1000)
}

// Format seconds to HH:MM:SS or MM:SS
const formatTime = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  if (hours > 0) {
    return `${hours}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const formattedElapsedTime = computed(() => formatTime(elapsedSeconds.value))

// Watch for status changes to start/stop timer
watch(() => playScreenData.value?.status, (status) => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }

  // Only run timer when active
  if (status === 'active') {
    updateElapsedTime()
    timerInterval = setInterval(updateElapsedTime, 1000)
  }
}, { immediate: true })

onMounted(async () => {
  await fetchPlayScreen(uuid)
  stopPolling = startPlayScreenPolling(uuid, 2000)
})

onUnmounted(() => {
  if (stopPolling) stopPolling()
  if (timerInterval) clearInterval(timerInterval)
})

// Show timer if active or paused
const showTimer = computed(() => 
  playScreenData.value?.status === 'active' || playScreenData.value?.status === 'paused'
)
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4">
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

    <!-- Error State -->
    <div v-else-if="error" class="text-red-500 text-2xl font-bold px-6 py-4">
      Error
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="text-gray-800 text-2xl font-bold px-6 py-4">
      ...
    </div>

    <!-- Timer Display - Numbers Design -->
    <div v-else-if="showTimer && design === 'numbers'" class="text-center">
      <div class="text-9xl font-bold text-gray-900 font-mono tracking-tight">
        {{ formattedElapsedTime }}
      </div>
      <div v-if="playScreenData?.status === 'paused'" class="mt-4 text-3xl text-orange-500 font-bold">
        PAUSED
      </div>
    </div>

    <!-- Not Started Yet -->
    <div v-else class="text-gray-800 text-2xl font-bold px-6 py-4">
      ...
    </div>
  </div>
</template>

<style scoped>
/* Clean, minimal styling for OBS */
body {
  margin: 0;
  padding: 0;
}
</style>
