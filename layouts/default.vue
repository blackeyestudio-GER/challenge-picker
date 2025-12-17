<template>
  <div>
    <NuxtLoadingIndicator :throttle="0" :height="3" color="#8b5cf6" />
    <slot />
  </div>
</template>

<script setup lang="ts">
useHead({
  titleTemplate: (title) => title ? `${title} - Challenge Picker` : 'Challenge Picker',
})
</script>