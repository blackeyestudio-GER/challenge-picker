<script setup lang="ts">
import { TIMER_DESIGNS, DEFAULT_TIMER_DESIGN, isValidTimerDesign, TIMER_DESIGN_LABELS, type TimerDesign } from '~/types/obs-designs'

definePageMeta({
  layout: 'obs'
})

// Public overlay page - just shows the timer for a user's active game
const route = useRoute()
const userUuid = route.params.uuid as string // Now expects user UUID, not playthrough UUID

const { fetchPlayScreenByUserUuid, startPlayScreenPollingByUserUuid, playScreenData, loading, error } = usePlaythrough()

// Design state and validation
const design = ref<TimerDesign>(DEFAULT_TIMER_DESIGN)
const invalidDesign = ref<string | null>(null)

// Chroma key color state
const chromaKeyColor = ref('#00FF00') // Default chroma green

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

  // 2. Fetch user's saved preference
  try {
    const config = useRuntimeConfig()
    const response = await $fetch<{ success: boolean; data: { timerDesign: string; chromaKeyColor: string } }>(
      `${config.public.apiBase}/user/${userUuid}/obs-preferences`
    )
    if (response.success) {
      if (isValidTimerDesign(response.data.timerDesign)) {
        design.value = response.data.timerDesign
        invalidDesign.value = null
      }
      if (response.data.chromaKeyColor) {
        chromaKeyColor.value = response.data.chromaKeyColor
      }
    }
  } catch (err) {
    console.error('Failed to load user preferences, using default', err)
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
let timerInterval: number | null = null
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
  await fetchPlayScreenByUserUuid(userUuid)
  stopPolling = startPlayScreenPollingByUserUuid(userUuid, 2000)
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
  <div :style="{ 
    backgroundColor: chromaKeyColor,
    minHeight: '100vh',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    margin: 0,
    padding: 0
  }">
    <!-- Loading Spinner -->
    <div v-if="!playScreenData || loading" :style="{ 
      width: '40px',
      height: '40px',
      border: '4px solid rgba(255,255,255,0.3)',
      borderTop: '4px solid white',
      borderRadius: '50%',
      animation: 'spin 1s linear infinite'
    }"></div>

    <!-- Timer Display -->
    <div v-else-if="showTimer && design === 'numbers'" :style="{ textAlign: 'center' }">
      <div :style="{ 
        fontSize: '120px',
        fontWeight: 'bold',
        color: '#111',
        fontFamily: 'monospace',
        lineHeight: 1
      }">
        {{ formattedElapsedTime }}
      </div>
      <div v-if="playScreenData?.status === 'paused'" :style="{ 
        marginTop: '20px',
        fontSize: '40px',
        color: '#f97316',
        fontWeight: 'bold'
      }">
        PAUSED
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
</style>
