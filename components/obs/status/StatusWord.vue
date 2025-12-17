<script setup lang="ts">
/**
 * Status Design: Word
 * Displays status as text (LIVE, PAUSED, SETUP, ENDED)
 */

interface Props {
  status: 'setup' | 'active' | 'paused' | 'completed'
}

const props = defineProps<Props>()

const statusWord = computed(() => {
  switch (props.status) {
    case 'setup': return 'SETUP'
    case 'active': return 'LIVE'
    case 'paused': return 'PAUSED'
    case 'completed': return 'ENDED'
    default: return ''
  }
})

const statusColor = computed(() => {
  switch (props.status) {
    case 'setup': return 'text-yellow-600'
    case 'active': return 'text-green-600'
    case 'paused': return 'text-orange-600'
    case 'completed': return 'text-gray-600'
    default: return 'text-gray-900'
  }
})
</script>

<template>
  <div :class="['text-8xl font-black tracking-wider', statusColor]">
    {{ statusWord }}
  </div>
</template>

