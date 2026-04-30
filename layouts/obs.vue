<script setup lang="ts">
/* Transparent root so OBS Browser Source can composite without Chroma Key */
useHead({
  style: [
    {
      key: 'obs-overlay-transparent',
      innerHTML: 'html, body { background: transparent !important; margin: 0; min-height: 100%; }',
    },
  ],
})
</script>

<template>
  <!-- Minimal layout for OBS overlays - no header, no footer, no extra styling -->
  <div>
    <slot />
  </div>
</template>
