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
        'moonstone': '#3AA1BD',
        'dim-gray': '#869698',
        'eerie-black': '#1C1E1E',
        'night': '#111212',
        'dark-slate-gray': '#1E4545',
        // Streamer theme colors
        'cyan': {
          DEFAULT: '#06b6d4',
          muted: '#0891b2',
          dark: '#0e7490',
        },
        'magenta': {
          DEFAULT: '#d946ef',
          muted: '#c026d3',
          dark: '#a21caf',
        },
      },
      saturate: {
        25: '.25',
        75: '.75',
      },
    },
  },
  plugins: [],
}