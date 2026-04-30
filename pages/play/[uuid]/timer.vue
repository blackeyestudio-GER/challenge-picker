<script setup lang="ts">
import { DEFAULT_TIMER_DESIGN, isValidTimerDesign, type TimerDesign } from '~/types/obs-designs'

definePageMeta({
  layout: 'obs'
})

// Public overlay page - just shows the timer for a user's active game
const route = useRoute()
const userUuid = route.params.uuid as string // Now expects user UUID, not playthrough UUID

const { fetchPlayScreenByUserUuid, startPlayScreenPollingByUserUuid, playScreenData, loading } = usePlaythrough()

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

  // 2. Fetch user's saved preference
  try {
    const config = useRuntimeConfig()
    const response = await $fetch<{ success: boolean; data: { timerDesign: string } }>(
      `${config.public.apiBase}/user/${userUuid}/obs-preferences`
    )
    if (response.success) {
      if (isValidTimerDesign(response.data.timerDesign)) {
        design.value = response.data.timerDesign
        invalidDesign.value = null
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

const elapsedSeconds = ref(0)
let timerInterval: number | null = null
let stopPolling: (() => void) | null = null

// Calculate elapsed time from startedAt, accounting for paused time
const updateElapsedTime = () => {
  if (!playScreenData.value?.startedAt) {
    elapsedSeconds.value = 0
    return
  }

  const startTime = new Date(playScreenData.value.startedAt).getTime()
  const now = Date.now()
  const totalPausedDuration = (playScreenData.value.totalPausedDuration || 0) * 1000 // Convert seconds to ms
  
  // If paused, calculate elapsed time up to pausedAt
  if (playScreenData.value.status === 'paused' && playScreenData.value.pausedAt) {
    const pausedAt = new Date(playScreenData.value.pausedAt).getTime()
    const elapsed = Math.floor((pausedAt - startTime - totalPausedDuration) / 1000)
    elapsedSeconds.value = Math.max(0, elapsed)
  } else if (playScreenData.value.status === 'active') {
    // Active: calculate elapsed time excluding paused time
    const elapsed = Math.floor((now - startTime - totalPausedDuration) / 1000)
    elapsedSeconds.value = Math.max(0, elapsed)
  } else {
    // Setup or completed: use backend totalDuration if available, otherwise 0
    if (playScreenData.value.totalDuration) {
      elapsedSeconds.value = playScreenData.value.totalDuration
    } else {
      elapsedSeconds.value = 0
    }
  }
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

  // Update immediately
  updateElapsedTime()

  // Run timer interval when active or paused (to handle resume correctly)
  if (status === 'active' || status === 'paused') {
    timerInterval = setInterval(updateElapsedTime, 1000) as unknown as number
  }
}, { immediate: true })

// Watch for startedAt, pausedAt, and totalPausedDuration changes
watch(() => [
  playScreenData.value?.startedAt,
  playScreenData.value?.pausedAt,
  playScreenData.value?.totalPausedDuration
], () => {
  updateElapsedTime()
}, { deep: true })

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
  <div
:style="{ 
    backgroundColor: 'transparent',
    minHeight: '100vh',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    margin: 0,
    padding: 0
  }">
    <!-- Loading Spinner -->
    <div
v-if="!playScreenData || loading" :style="{ 
      width: '40px',
      height: '40px',
      border: '4px solid rgba(255,255,255,0.3)',
      borderTop: '4px solid white',
      borderRadius: '50%',
      animation: 'spin 1s linear infinite'
    }"/>

    <!-- Timer Display -->
    <div v-else-if="showTimer && design === 'numbers'" :style="{ textAlign: 'center' }">
      <div
:style="{ 
        fontSize: '120px',
        fontWeight: 'bold',
        color: '#111',
        fontFamily: 'monospace',
        lineHeight: 1
      }">
        {{ formattedElapsedTime }}
      </div>
      <div
v-if="playScreenData?.status === 'paused'" :style="{ 
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
