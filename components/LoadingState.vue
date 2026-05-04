<script setup lang="ts">
withDefaults(defineProps<{
  message?: string
}>(), {
  message: 'Loading...'
})
</script>

<template>
  <div class="loading-state">
    <div class="loading-state__spinner" />
    <p class="loading-state__message">{{ message }}</p>
  </div>
</template>

<style scoped>
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 3rem 1rem;
  text-align: center;
}

.loading-state__spinner {
  width: 3rem;
  height: 3rem;
  border-radius: 9999px;
  border-top: 2px solid var(--color-border-accent);
  border-bottom: 2px solid var(--color-border-accent);
  animation: loading-state-spin 0.8s linear infinite;
}

.loading-state__message {
  color: var(--color-text-secondary);
}

@keyframes loading-state-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
