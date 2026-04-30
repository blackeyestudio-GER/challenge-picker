/**
 * Requires the logged-in user to have linked Discord (dashboard / Discord-first flows).
 */
export default defineNuxtRouteMiddleware(() => {
  if (!import.meta.client) {
    return
  }

  const userStr = localStorage.getItem('auth_user')
  if (!userStr) {
    return navigateTo('/login')
  }

  try {
    const u = JSON.parse(userStr) as { discordId?: string | null }
    if (!u.discordId) {
      return navigateTo({ path: '/login', query: { needsDiscord: '1' } })
    }
  } catch {
    return navigateTo('/login')
  }
})
