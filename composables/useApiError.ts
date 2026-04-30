/**
 * Normalizes API / $fetch errors into a single user-facing string.
 * Matches Symfony JSON: { success: false, error: { code, message } }
 */
export function getApiErrorMessage(error: unknown, fallback = 'Something went wrong. Please try again.'): string {
  if (error === null || error === undefined) {
    return fallback
  }

  if (typeof error === 'string') {
    return error.trim() !== '' ? error : fallback
  }

  if (typeof error !== 'object') {
    return fallback
  }

  const err = error as Record<string, unknown>

  const statusMessage = err.statusMessage
  if (typeof statusMessage === 'string' && statusMessage.trim() !== '') {
    return statusMessage
  }

  const message = err.message
  if (typeof message === 'string' && message.trim() !== '' && message !== 'Fetch Error') {
    return message
  }

  const data = err.data
  if (data !== null && typeof data === 'object') {
    const d = data as Record<string, unknown>
    const nested = d.error
    if (nested !== null && typeof nested === 'object') {
      const e = nested as Record<string, unknown>
      const m = e.message
      if (typeof m === 'string' && m.trim() !== '') {
        return m
      }
    }
    if (typeof nested === 'string' && nested.trim() !== '') {
      return nested
    }
  }

  return fallback
}
