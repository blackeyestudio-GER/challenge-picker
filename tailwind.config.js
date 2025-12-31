/** @type {import('tailwindcss').Config} */
/** https://coolors.co/3aa1bd-586667-1c1e1e-111212-1e4545 */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./error.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Theme-aware colors using CSS variables
        'theme': {
          'bg-primary': 'var(--color-bg-primary)',
          'bg-secondary': 'var(--color-bg-secondary)',
          'bg-tertiary': 'var(--color-bg-tertiary)',
          'bg-card': 'var(--color-bg-card)',
          'text-primary': 'var(--color-text-primary)',
          'text-secondary': 'var(--color-text-secondary)',
          'text-tertiary': 'var(--color-text-tertiary)',
          'text-muted': 'var(--color-text-muted)',
          'border-primary': 'var(--color-border-primary)',
          'border-secondary': 'var(--color-border-secondary)',
          'border-accent': 'var(--color-border-accent)',
        },
        // Legacy colors (mapped to theme variables for compatibility)
        'moonstone': '#3AA1BD',
        'dim-gray': 'var(--color-text-secondary)',
        'eerie-black': 'var(--color-bg-secondary)',
        'night': 'var(--color-bg-primary)',
        'dark-slate-gray': 'var(--color-bg-tertiary)',
        // Streamer theme colors
        'cyan': {
          DEFAULT: 'var(--color-accent-primary)',
          muted: 'var(--color-accent-primary-hover)',
          dark: 'var(--color-accent-primary-dark)',
        },
        'magenta': {
          DEFAULT: 'var(--color-accent-secondary)',
          muted: 'var(--color-accent-secondary-hover)',
          dark: 'var(--color-accent-secondary-dark)',
        },
        // Keep gray scale as-is for compatibility
        // Components should use theme-* classes or CSS variables directly
      },
      backgroundColor: {
        'theme-primary': 'var(--color-bg-primary)',
        'theme-secondary': 'var(--color-bg-secondary)',
        'theme-card': 'var(--color-bg-card)',
      },
      textColor: {
        'theme-primary': 'var(--color-text-primary)',
        'theme-secondary': 'var(--color-text-secondary)',
        'theme-tertiary': 'var(--color-text-tertiary)',
      },
      borderColor: {
        'theme-primary': 'var(--color-border-primary)',
        'theme-accent': 'var(--color-border-accent)',
      },
      saturate: {
        25: '.25',
        75: '.75',
      },
    },
  },
  plugins: [],
}