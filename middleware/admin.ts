export default defineNuxtRouteMiddleware((_to, _from) => {
  // On server-side, allow navigation (will be checked on client)
  if (import.meta.server) {
    return
  }
  
  // On client-side, check localStorage directly for immediate access
  if (import.meta.client) {
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
    } catch {
      return navigateTo('/dashboard')
    }
  }
})
