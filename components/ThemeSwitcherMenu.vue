<script setup lang="ts">
import type { ThemeName } from '~/composables/useThemeSwitcher'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { Icon } from '#components'

interface Emits {
  (e: 'close'): void
}

const emit = defineEmits<Emits>()
const { currentTheme, availableThemes, switchTheme } = useThemeSwitcher()

const handleThemeSwitch = (themeName: ThemeName) => {
  switchTheme(themeName)
  emit('close')
}
</script>

<template>
  <div class="theme-switcher-menu">
    <!-- Divider -->
    <div class="theme-switcher-menu__divider"/>
    
    <!-- Theme Section Header -->
    <div class="theme-switcher-menu__header">
      Themes
    </div>
    
    <!-- Theme Options -->
    <button
      v-for="theme in availableThemes"
      :key="theme.name"
      :class="[
        'theme-switcher-menu__option',
        currentTheme === theme.name && 'theme-switcher-menu__option--active'
      ]"
      @click="handleThemeSwitch(theme.name)"
    >
      <div class="theme-switcher-menu__option-main">
        <Icon 
          :name="theme.name === 'default' ? 'heroicons:moon' : 'heroicons:sun'" 
          class="theme-switcher-menu__option-icon"
        />
        <div class="theme-switcher-menu__option-copy">
          <div class="theme-switcher-menu__option-label">{{ theme.label }}</div>
          <div class="theme-switcher-menu__option-description">{{ theme.description }}</div>
        </div>
      </div>
      <Icon 
        v-if="currentTheme === theme.name"
        name="heroicons:check-circle" 
        class="theme-switcher-menu__check"
      />
    </button>
  </div>
</template>

<style scoped>
.theme-switcher-menu__divider {
  margin: 0.5rem 0;
  border-top: 1px solid var(--color-border-primary);
}

.theme-switcher-menu__header {
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--color-text-muted);
}

.theme-switcher-menu__option {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: var(--color-text-secondary);
  transition: background-color 0.2s ease, color 0.2s ease;
}

.theme-switcher-menu__option:hover,
.theme-switcher-menu__option--active {
  background-color: var(--color-bg-tertiary);
  color: var(--color-text-primary);
}

.theme-switcher-menu__option-main {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.theme-switcher-menu__option-icon,
.theme-switcher-menu__check {
  width: 1.25rem;
  height: 1.25rem;
}

.theme-switcher-menu__option-copy {
  text-align: left;
}

.theme-switcher-menu__option-label {
  font-weight: 600;
}

.theme-switcher-menu__option-description {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.theme-switcher-menu__check {
  color: var(--color-icon-primary);
}
</style>
