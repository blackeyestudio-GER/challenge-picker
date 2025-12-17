<script setup lang="ts">
/**
 * Rules Design: List
 * Displays rules as a full-screen list with countdown timers
 */

interface Rule {
  id: number
  text: string
  durationMinutes: number
  startedAt: string | null
}

interface Props {
  activeRules: Rule[]
}

const props = defineProps<Props>()

// Individual rule countdown timers
interface RuleWithCountdown {
  id: number
  text: string
  remainingSeconds: number
}

const rulesWithCountdown = ref<RuleWithCountdown[]>([])

// Update countdown for all rules
const updateCountdowns = () => {
  if (!props.activeRules) return

  const now = Date.now()
  
  rulesWithCountdown.value = props.activeRules
    .map(rule => {
      if (!rule.startedAt) {
        return {
          id: rule.id,
          text: rule.text,
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

// Update countdowns every second
let countdownInterval: NodeJS.Timeout | null = null

// Watch for active rules changes
watch(() => props.activeRules, () => {
  updateCountdowns()
}, { deep: true, immediate: true })

onMounted(() => {
  updateCountdowns()
  countdownInterval = setInterval(updateCountdowns, 1000)
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<template>
  <div class="space-y-4">
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
</template>

