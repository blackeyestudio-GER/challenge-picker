/**
 * Theme Switcher Composable
 * 
 * Handles dynamic theme switching by loading/unloading CSS files
 */

import { nextTick } from 'vue'

export type ThemeName = 'default' | 'light'

export interface Theme {
  name: ThemeName
  label: string
  description: string
}

export const useThemeSwitcher = () => {
  // Initialize currentTheme from localStorage or DOM state
  const getInitialTheme = (): ThemeName => {
    if (import.meta.client) {
      // First check localStorage
      const savedTheme = localStorage.getItem('theme') as ThemeName | null
      if (savedTheme && (savedTheme === 'default' || savedTheme === 'light')) {
        return savedTheme
      }
      
      // Fallback: check DOM for theme class
      const html = document.documentElement
      if (html.classList.contains('theme-light')) {
        return 'light'
      }
    }
    return 'default'
  }
  
  const currentTheme = ref<ThemeName>(getInitialTheme())

  const availableThemes: Theme[] = [
    {
      name: 'default',
      label: 'Dark',
      description: 'Default dark theme with cyan/magenta accents'
    },
    {
      name: 'light',
      label: 'Light',
      description: 'Light theme with softer colors'
    }
  ]

  /**
   * Apply theme class to html element
   */
  const applyTheme = (themeName: ThemeName) => {
    if (import.meta.client) {
      const html = document.documentElement
      const body = document.body
      
      // Remove all theme classes
      availableThemes.forEach(theme => {
        html.classList.remove(`theme-${theme.name}`)
      })
      
      // Add new theme class (except default which is on :root)
      if (themeName !== 'default') {
        html.classList.add(`theme-${themeName}`)
      } else {
        // Ensure default theme has no class (uses :root)
        html.classList.remove('theme-default')
      }
      
      currentTheme.value = themeName
      localStorage.setItem('theme', themeName)
      
      // Force style recalculation
      const computedStyle = getComputedStyle(html)
      const bgColor = computedStyle.getPropertyValue('--color-bg-primary').trim()
      
      // Apply background directly to body and html as fallback
      if (bgColor) {
        html.style.setProperty('background-color', bgColor, 'important')
        body.style.setProperty('background-color', bgColor, 'important')
        const app = document.getElementById('app')
        if (app) {
          app.style.setProperty('background-color', bgColor, 'important')
        }
      }
      
      // Debug (only in dev)
      if (import.meta.dev) {
        console.log('Theme applied:', {
          themeName,
          htmlClasses: html.className,
          bgColor,
          cssVar: computedStyle.getPropertyValue('--color-bg-primary')
        })
      }
    }
  }

  /**
   * Initialize theme from localStorage or default
   */
  const initTheme = () => {
    if (import.meta.client) {
      const savedTheme = localStorage.getItem('theme') as ThemeName | null
      const theme = savedTheme && availableThemes.find(t => t.name === savedTheme)
        ? savedTheme
        : 'default'
      
      // Update the ref before applying to ensure sync
      currentTheme.value = theme
      applyTheme(theme)
    }
  }
  
  /**
   * Sync currentTheme ref with actual DOM state
   */
  const syncTheme = () => {
    if (import.meta.client) {
      const html = document.documentElement
      if (html.classList.contains('theme-light')) {
        currentTheme.value = 'light'
      } else {
        currentTheme.value = 'default'
      }
    }
  }

  /**
   * Switch to a different theme
   */
  const switchTheme = (themeName: ThemeName) => {
    if (availableThemes.find(t => t.name === themeName)) {
      applyTheme(themeName)
      // Debug log (only in dev)
      if (import.meta.dev && import.meta.client) {
        console.log(`Theme switched to: ${themeName}`, {
          htmlClasses: document.documentElement.className,
          currentTheme: currentTheme.value
        })
      }
    } else {
      console.warn(`Theme "${themeName}" not found`)
    }
  }

  /**
   * Toggle between light and dark
   */
  const toggleTheme = () => {
    const nextTheme = currentTheme.value === 'default' ? 'light' : 'default'
    switchTheme(nextTheme)
  }

  /**
   * Get current theme info
   */
  const getCurrentTheme = (): Theme | undefined => {
    return availableThemes.find(t => t.name === currentTheme.value)
  }

  // Don't auto-initialize here - let app.vue handle it
  // This prevents multiple initializations

  return {
    currentTheme: readonly(currentTheme),
    availableThemes,
    switchTheme,
    toggleTheme,
    initTheme,
    syncTheme,
    getCurrentTheme
  }
}

