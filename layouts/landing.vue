<template>
  <!-- Landing page layout - no header, no footer, just the page content -->
  <div>
    <ThemeToggle />
    <slot />
  </div>
</template>

<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})
</script>

