<script setup lang="ts">
/**
 * Timer Design: Numbers
 * Displays elapsed time in HH:MM:SS or MM:SS format
 */

interface Props {
  elapsedSeconds: number
  isPaused: boolean
}

const props = defineProps<Props>()

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

const formattedTime = computed(() => formatTime(props.elapsedSeconds))
</script>

<template>
  <div class="text-center">
    <div class="text-9xl font-bold text-gray-900 font-mono tracking-tight">
      {{ formattedTime }}
    </div>
    <div v-if="isPaused" class="mt-4 text-3xl text-orange-500 font-bold">
      PAUSED
    </div>
  </div>
</template>

