<script setup lang="ts">
import { RULES_DESIGNS, DEFAULT_RULES_DESIGN, isValidRulesDesign, RULES_DESIGN_LABELS, type RulesDesign } from '~/types/obs-designs'

// Public overlay page - shows active rules with countdown
const route = useRoute()
const uuid = route.params.uuid as string

const { fetchPlayScreen, startPlayScreenPolling, playScreenData, loading, error } = usePlaythrough()

// Design state and validation
const design = ref<RulesDesign>(DEFAULT_RULES_DESIGN)
const invalidDesign = ref<string | null>(null)

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

  // 2. Fetch user's saved preference from their playthrough
  if (playScreenData.value?.id) {
    try {
      const config = useRuntimeConfig()
      const response = await $fetch<{ success: boolean; data: { rulesDesign: string } }>(
        `${config.public.apiBase}/api/play/${uuid}/preferences`
      )
      if (response.success && isValidRulesDesign(response.data.rulesDesign)) {
        design.value = response.data.rulesDesign
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

// Update countdown for all rules
const updateCountdowns = () => {
  if (!playScreenData.value?.activeRules) return

  const now = Date.now()
  
  rulesWithCountdown.value = playScreenData.value.activeRules
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
      const elapsed = Math.floor((now - startTime) / 1000)
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
let countdownInterval: NodeJS.Timeout | null = null

watch(() => playScreenData.value, () => {
  updateCountdowns()
}, { deep: true, immediate: true })

onMounted(async () => {
  await fetchPlayScreen(uuid)
  stopPolling = startPlayScreenPolling(uuid, 2000)
  
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

    <!-- Error State -->
    <div v-else-if="error" class="text-red-500 text-2xl font-bold">
      Error loading rules
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="text-gray-800 text-2xl font-bold">
      Loading...
    </div>

    <!-- List Design -->
    <div v-else-if="shouldShow && design === 'list'" class="space-y-4">
      <div
        v-for="rule in rulesWithCountdown"
        :key="rule.id"
        class="flex items-center justify-between bg-white border-2 border-gray-900 rounded-lg px-6 py-4 shadow-lg"
      >
        <div class="text-2xl font-semibold text-gray-900 flex-1 pr-6">
          {{ rule.text }}
        </div>
        <div class="text-4xl font-bold text-gray-900 font-mono min-w-[120px] text-right">
          {{ formatTime(rule.remainingSeconds) }}
        </div>
      </div>

      <!-- No active rules -->
      <div v-if="rulesWithCountdown.length === 0" class="text-center py-12">
        <div class="text-3xl font-bold text-gray-600">
          No active rules
        </div>
      </div>
    </div>

    <!-- Not in active/paused state -->
    <div v-else-if="playScreenData" class="text-center py-12">
      <div class="text-3xl font-bold text-gray-600">
        {{ playScreenData.status === 'setup' ? 'Setting up...' : 'Session ended' }}
      </div>
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
