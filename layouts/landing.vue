<template>
  <!-- Landing page layout - no header, no footer, just the page content -->
  <div>
    <NuxtLink class="skip-link" to="#main-content">
      Skip to main content
    </NuxtLink>
    <ThemeToggle />
    <main id="main-content" tabindex="-1">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})
</script>

