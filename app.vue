<template>
  <div id="app">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
const { loadAuth } = useAuth()

// Load authentication state from localStorage immediately on app initialization
// This must run before any page components mount
loadAuth()
</script>
