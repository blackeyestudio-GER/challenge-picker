export default defineNuxtPlugin(() => {
  const { loadAuth } = useAuth()
  
  // Load auth state from localStorage on app init
  loadAuth()
})

