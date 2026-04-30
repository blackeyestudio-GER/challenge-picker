export default defineNuxtRouteMiddleware(() => {
  const authCookie = useCookie<string | null>('auth_token', {
    path: '/',
    maxAge: 60 * 60 * 24 * 30,
    sameSite: 'lax',
  })

  // Token lives in localStorage; only the client can read it. Keep cookie in sync for middleware runs that have cookie access.
  if (!import.meta.client) {
    return
  }

  const ls = localStorage.getItem('auth_token')
  if (ls) {
    authCookie.value = ls
  }

  if (!ls) {
    return navigateTo('/login')
  }
})
