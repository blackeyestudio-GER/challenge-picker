<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

const { currentTheme, toggleTheme } = useThemeSwitcher()

const isDark = computed(() => currentTheme.value === 'default')
</script>

<template>
  <ClientOnly>
    <button
      @click="toggleTheme"
      :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
      class="theme-toggle"
      type="button"
    >
      <Icon 
        :name="isDark ? 'heroicons:sun' : 'heroicons:moon'" 
        class="theme-toggle__icon"
      />
    </button>
    <template #fallback>
      <!-- Fallback during SSR - render a placeholder to avoid layout shift -->
      <button
        class="theme-toggle"
        type="button"
        aria-label="Theme toggle"
      >
        <Icon 
          name="heroicons:moon" 
          class="theme-toggle__icon"
        />
      </button>
    </template>
  </ClientOnly>
</template>

<style scoped>
.theme-toggle {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 50;
  width: 3rem;
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 9999px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-toggle:hover {
  background-color: rgba(0, 0, 0, 0.7);
  border-color: rgba(255, 255, 255, 0.4);
  transform: scale(1.1);
}

.theme-toggle__icon {
  width: 1.5rem;
  height: 1.5rem;
  color: white;
}
</style>

