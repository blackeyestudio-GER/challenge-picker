<script setup lang="ts">
withDefaults(defineProps<{
  icon?: string
  title?: string
  message: string
}>(), {
  icon: 'heroicons:face-frown',
  title: undefined
})
</script>

<template>
  <div class="empty-state">
    <Icon :name="icon" class="empty-state__icon" />
    <h2 v-if="title" class="empty-state__title">{{ title }}</h2>
    <p class="empty-state__message">{{ message }}</p>
    <div v-if="$slots.default" class="empty-state__actions">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 3rem 1rem;
  text-align: center;
}

.empty-state__icon {
  width: 4rem;
  height: 4rem;
  color: var(--color-icon-muted);
}

.empty-state__title {
  color: var(--color-text-primary);
  font-size: 1.25rem;
  font-weight: 700;
}

.empty-state__message {
  color: var(--color-text-secondary);
  max-width: 34rem;
}

.empty-state__actions {
  display: flex;
  justify-content: center;
}
</style>
