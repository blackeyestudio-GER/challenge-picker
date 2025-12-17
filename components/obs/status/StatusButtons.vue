<script setup lang="ts">
/**
 * Status Design: Buttons
 * Displays status as colored button with symbol
 */

interface Props {
  status: 'setup' | 'active' | 'paused' | 'completed'
}

const props = defineProps<Props>()

const statusSymbol = computed(() => {
  switch (props.status) {
    case 'setup': return '⚙️'
    case 'active': return '▶️'
    case 'paused': return '⏸️'
    case 'completed': return '⏹️'
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
  <div class="text-center">
    <div class="rounded-2xl px-12 py-8 inline-flex items-center justify-center bg-gray-800">
      <span :class="['text-8xl', statusColor]">{{ statusSymbol }}</span>
    </div>
  </div>
</template>

