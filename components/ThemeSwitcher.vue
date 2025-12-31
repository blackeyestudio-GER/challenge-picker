<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { Icon } from '#components'

const { currentTheme, availableThemes, switchTheme, toggleTheme } = useThemeSwitcher()

const showDropdown = ref(false)

const currentThemeInfo = computed(() => {
  return availableThemes.find(t => t.name === currentTheme.value)
})
</script>

<template>
  <div class="relative">
    <!-- Theme Toggle Button -->
    <button
      @click="toggleTheme"
      class="p-2 rounded-lg bg-theme-card border border-theme-primary hover:border-theme-accent transition-colors"
      :title="`Switch to ${currentTheme === 'default' ? 'Light' : 'Dark'} theme`"
    >
      <Icon 
        :name="currentTheme === 'default' ? 'heroicons:moon' : 'heroicons:sun'" 
        class="w-5 h-5 icon-primary"
      />
    </button>

    <!-- Dropdown (Optional - for future expansion) -->
    <!--
    <div v-if="showDropdown" class="absolute right-0 mt-2 w-48 bg-theme-card border border-theme-primary rounded-lg shadow-lg z-50">
      <div
        v-for="theme in availableThemes"
        :key="theme.name"
        @click="switchTheme(theme.name); showDropdown = false"
        :class="[
          'px-4 py-2 cursor-pointer hover:bg-theme-secondary transition-colors',
          currentTheme === theme.name && 'bg-theme-secondary'
        ]"
      >
        <div class="font-semibold text-theme-primary">{{ theme.label }}</div>
        <div class="text-sm text-theme-tertiary">{{ theme.description }}</div>
      </div>
    </div>
    -->
  </div>
</template>

