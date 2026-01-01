<template>
  <div v-if="activePlaythrough" class="active-playthrough-warning">
    <Icon name="heroicons:exclamation-triangle" class="active-playthrough-warning__icon" />
    <div class="active-playthrough-warning__content">
      <p class="active-playthrough-warning__title">You have an active playthrough</p>
      <p class="active-playthrough-warning__text">
        You cannot create a new playthrough while another one is active. Please end your current session first.
      </p>
      <NuxtLink
        :to="`/play/${activePlaythrough.uuid}`"
        class="active-playthrough-warning__link"
      >
        Go to Active Session →
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePlaythrough } from '~/composables/usePlaythrough'

const { activePlaythrough, fetchActivePlaythrough } = usePlaythrough()

// Fetch active playthrough on mount
onMounted(async () => {
  await fetchActivePlaythrough()
})
</script>

<style scoped>
.active-playthrough-warning {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.1));
  border: 1px solid rgba(251, 191, 36, 0.3);
  border-radius: 0.75rem;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.active-playthrough-warning__icon {
  width: 1.75rem;
  height: 1.75rem;
  color: #f59e0b;
  flex-shrink: 0;
  margin-top: 0.125rem;
}

.active-playthrough-warning__content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.active-playthrough-warning__title {
  font-weight: 600;
  font-size: 1rem;
  color: var(--color-text-primary);
  margin: 0;
}

.active-playthrough-warning__text {
  color: var(--color-text-secondary);
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.5;
}

.active-playthrough-warning__link {
  color: #f59e0b;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.875rem;
  transition: color 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.active-playthrough-warning__link:hover {
  color: #d97706;
}

@media (max-width: 640px) {
  .active-playthrough-warning {
    padding: 1rem;
    gap: 0.75rem;
  }

  .active-playthrough-warning__icon {
    width: 1.5rem;
    height: 1.5rem;
  }
}
</style>

