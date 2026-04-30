<script setup lang="ts">
const routeLoading = useState('app-route-loading', () => false)
</script>

<template>
  <div
    class="app-route-progress"
    role="progressbar"
    :aria-hidden="!routeLoading"
    :aria-busy="routeLoading"
    :class="{ 'app-route-progress--active': routeLoading }"
  />
</template>

<style scoped>
.app-route-progress {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  z-index: 9999;
  pointer-events: none;
  background: transparent;
  transform: scaleX(0);
  transform-origin: left center;
  transition: transform 0.2s ease, opacity 0.3s ease;
  opacity: 0;
}

.app-route-progress--active {
  opacity: 1;
  transform: scaleX(1);
  background: linear-gradient(
    90deg,
    var(--color-accent-cyan, #22d3ee),
    var(--color-accent-magenta, #e879f9)
  );
}

@media (prefers-reduced-motion: reduce) {
  .app-route-progress {
    transition: none;
  }

  .app-route-progress--active {
    transform: scaleX(1);
  }
}
</style>
