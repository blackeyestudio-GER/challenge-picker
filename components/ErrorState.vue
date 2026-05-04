<script setup lang="ts">
defineProps<{
  message: string
}>()
</script>

<template>
  <div class="error-state" role="alert">
    <Icon name="heroicons:exclamation-triangle" class="error-state__icon" />
    <p class="error-state__message">{{ message }}</p>
  </div>
</template>

<style scoped>
.error-state {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 0.75rem;
  border: 1px solid var(--color-btn-danger-border);
  background: color-mix(in srgb, var(--color-btn-danger-bg) 35%, transparent);
  color: var(--color-btn-danger-text);
}

.error-state__icon {
  flex-shrink: 0;
  width: 1.5rem;
  height: 1.5rem;
}

.error-state__message {
  margin: 0;
}
</style>
