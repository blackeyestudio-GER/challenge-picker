<script setup lang="ts">
/**
 * Status Design: Symbols
 * Displays status as emoji icons
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
</script>

<template>
  <div class="text-9xl">
    {{ statusSymbol }}
  </div>
</template>

