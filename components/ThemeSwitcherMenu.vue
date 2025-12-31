<script setup lang="ts">
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'
import { Icon } from '#components'

interface Emits {
  (e: 'close'): void
}

const emit = defineEmits<Emits>()
const { currentTheme, availableThemes, switchTheme } = useThemeSwitcher()

const handleThemeSwitch = (themeName: string) => {
  switchTheme(themeName as any)
  emit('close')
}
</script>

<template>
  <div>
    <!-- Divider -->
    <div class="my-2 border-t border-gray-700"></div>
    
    <!-- Theme Section Header -->
    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">
      Themes
    </div>
    
    <!-- Theme Options -->
    <button
      v-for="theme in availableThemes"
      :key="theme.name"
      @click="handleThemeSwitch(theme.name)"
      :class="[
        'w-full flex items-center justify-between gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition',
        currentTheme === theme.name && 'bg-gray-700 text-white'
      ]"
    >
      <div class="flex items-center gap-3">
        <Icon 
          :name="theme.name === 'default' ? 'heroicons:moon' : 'heroicons:sun'" 
          class="w-5 h-5"
        />
        <div class="text-left">
          <div class="font-semibold">{{ theme.label }}</div>
          <div class="text-xs text-gray-400">{{ theme.description }}</div>
        </div>
      </div>
      <Icon 
        v-if="currentTheme === theme.name"
        name="heroicons:check-circle" 
        class="w-5 h-5 text-cyan"
      />
    </button>
  </div>
</template>

