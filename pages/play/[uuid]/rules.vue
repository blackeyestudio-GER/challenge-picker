<script setup lang="ts">
import { RULES_DESIGNS, DEFAULT_RULES_DESIGN, isValidRulesDesign, RULES_DESIGN_LABELS, type RulesDesign } from '~/types/obs-designs'

definePageMeta({
  layout: false
})

// Public overlay page - shows active rules with countdown for a user's active game
const route = useRoute()
const userUuid = route.params.uuid as string // Now expects user UUID, not playthrough UUID

const { fetchPlayScreenByUserUuid, startPlayScreenPollingByUserUuid, playScreenData, loading, error } = usePlaythrough()

// Design state and validation
const design = ref<RulesDesign>(DEFAULT_RULES_DESIGN)
const invalidDesign = ref<string | null>(null)
const chromaKeyColor = ref('#00FF00') // Default chroma green

// Fetch user's preferences if no query param provided
const loadDesign = async () => {
  // 1. Check query param first (explicit override)
  if (route.query.design) {
    const requestedDesign = route.query.design as string
    if (isValidRulesDesign(requestedDesign)) {
      design.value = requestedDesign
      invalidDesign.value = null
      return
    } else {
      // Invalid design requested
      invalidDesign.value = requestedDesign
      design.value = DEFAULT_RULES_DESIGN
      return
    }
  }

  // 2. Fetch user's saved preference
  try {
    const config = useRuntimeConfig()
    const response = await $fetch<{ success: boolean; data: { rulesDesign: string; chromaKeyColor: string } }>(
      `${config.public.apiBase}/user/${userUuid}/obs-preferences`
    )
    if (response.success) {
      if (isValidRulesDesign(response.data.rulesDesign)) {
        design.value = response.data.rulesDesign
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
  RULES_DESIGNS.map(d => `"${d}" (${RULES_DESIGN_LABELS[d]})`).join(', ')
)

let stopPolling: (() => void) | null = null

// Individual rule countdown timers
interface RuleWithCountdown {
  id: number
  text: string
  durationMinutes: number
  startedAt: string | null
  remainingSeconds: number
}

const rulesWithCountdown = ref<RuleWithCountdown[]>([])

// Track when game was paused to freeze timers
const pausedAt = ref<number | null>(null)
const totalPausedTime = ref(0)

// Update countdown for all rules
const updateCountdowns = () => {
  const playScreen = playScreenData.value
  if (!playScreen || !playScreen.activeRules) return

  const now = Date.now()
  
  // Handle pause state changes
  if (playScreen.status === 'paused') {
    if (pausedAt.value === null) {
      // Game just paused, record when
      pausedAt.value = now
    }
    // When paused, freeze the timers - don't update elapsed time
  } else {
    if (pausedAt.value !== null) {
      // Game just resumed, add paused duration to total
      totalPausedTime.value += now - pausedAt.value
      pausedAt.value = null
    }
  }
  
  rulesWithCountdown.value = playScreen.activeRules
    .map(rule => {
      if (!rule.startedAt) {
        return {
          id: rule.id,
          text: rule.text,
          durationMinutes: rule.durationMinutes,
          startedAt: rule.startedAt,
          remainingSeconds: rule.durationMinutes * 60
        }
      }

      const startTime = new Date(rule.startedAt).getTime()
      
      // Calculate elapsed time, excluding paused time
      let elapsed: number
      if (playScreen.status === 'paused' && pausedAt.value) {
        // Game is paused - freeze at the moment it was paused
        elapsed = Math.floor((pausedAt.value - startTime - totalPausedTime.value) / 1000)
      } else {
        // Game is active - count elapsed time excluding total paused time
        elapsed = Math.floor((now - startTime - totalPausedTime.value) / 1000)
      }
      
      const total = rule.durationMinutes * 60
      const remaining = Math.max(0, total - elapsed)

      return {
        id: rule.id,
        text: rule.text,
        durationMinutes: rule.durationMinutes,
        startedAt: rule.startedAt,
        remainingSeconds: remaining
      }
    })
    .filter(rule => rule.remainingSeconds > 0) // Hide rules that reached 0
}

// Format seconds to MM:SS
const formatTime = (seconds: number): string => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

// Start countdown interval
let countdownInterval: number | null = null

watch(() => playScreenData.value, () => {
  updateCountdowns()
}, { deep: true, immediate: true })

onMounted(async () => {
  await fetchPlayScreenByUserUuid(userUuid)
  stopPolling = startPlayScreenPollingByUserUuid(userUuid, 2000)
  
  // Update countdowns every second
  countdownInterval = setInterval(updateCountdowns, 1000)
})

onUnmounted(() => {
  if (stopPolling) stopPolling()
  if (countdownInterval) clearInterval(countdownInterval)
})

// Only show during active/paused states
const shouldShow = computed(() => 
  playScreenData.value?.status === 'active' || playScreenData.value?.status === 'paused'
)
</script>

<template>
  <div :style="{ 
    backgroundColor: chromaKeyColor,
    minHeight: '100vh',
    padding: '32px',
    margin: 0
  }">
    <!-- Loading Spinner -->
    <div v-if="!playScreenData || loading" :style="{ 
      position: 'fixed',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
      width: '40px',
      height: '40px',
      border: '4px solid rgba(255,255,255,0.3)',
      borderTop: '4px solid white',
      borderRadius: '50%',
      animation: 'spin 1s linear infinite'
    }"></div>

    <!-- List Design -->
    <div v-else-if="shouldShow && design === 'list'">
      <div
        v-for="rule in rulesWithCountdown"
        :key="rule.id"
        :style="{ 
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          backgroundColor: 'white',
          border: '2px solid #111',
          borderRadius: '8px',
          padding: '16px 24px',
          marginBottom: '16px'
        }"
      >
        <div :style="{ 
          fontSize: '28px',
          fontWeight: '600',
          color: '#111',
          flex: 1,
          paddingRight: '24px'
        }">
          {{ rule.text }}
        </div>
        <div :style="{ 
          fontSize: '48px',
          fontWeight: 'bold',
          color: '#111',
          fontFamily: 'monospace',
          minWidth: '120px',
          textAlign: 'right'
        }">
          {{ formatTime(rule.remainingSeconds) }}
        </div>
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
