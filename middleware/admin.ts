export default defineNuxtRouteMiddleware((to, from) => {
  // On server-side, allow navigation (will be checked on client)
  if (process.server) {
    return
  }
  
  // On client-side, check localStorage directly for immediate access
  if (process.client) {
    const token = localStorage.getItem('auth_token')
    const userStr = localStorage.getItem('auth_user')
    
    if (!token || !userStr) {
      return navigateTo('/dashboard')
    }
    
    try {
      const user = JSON.parse(userStr)
      if (!user.isAdmin) {
        return navigateTo('/dashboard')
      }
    } catch (e) {
      return navigateTo('/dashboard')
    }
  }
})
