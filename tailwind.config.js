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
    colors: {
      'moonstone': '#3AA1BD',
      'dim-gray': '#869698',
      //'dim-gray': '#586667',
      'eerie-black': '#1C1E1E',
      'night': '#111212',
      'dark-slate-gray': '#1E4545',
    },
    extend: {
      saturate: {
        25: '.25',
        75: '.75',
      },
    },
  },
  plugins: [],
}