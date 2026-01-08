<template>
  <div id="app">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <NotificationToast />
  </div>
</template>

<script setup lang="ts">
const { loadAuth, user } = useAuth()
const { initTheme, switchTheme } = useThemeSwitcher()

// Load authentication state from localStorage immediately on app initialization
// This must run before any page components mount
loadAuth()

// Initialize theme on app load
onMounted(() => {
  // If user has a saved theme, use it; otherwise use default
  if (user.value?.theme) {
    switchTheme(user.value.theme as any)
  } else {
    initTheme()
  }
})
</script>
