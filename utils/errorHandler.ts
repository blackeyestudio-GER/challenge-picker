/**
 * Centralized error handling utility
 * Provides consistent error message extraction from API responses
 */

export interface ApiError {
  data?: {
    error?: {
      code?: string
      message?: string
      details?: unknown
    }
    message?: string
  }
  message?: string
  statusMessage?: string
  statusCode?: number
}

/**
 * Extract user-friendly error message from API error
 */
export function extractErrorMessage(error: unknown, defaultMessage: string = 'An error occurred'): string {
  if (!error) {
    return defaultMessage
  }

  const apiError = error as ApiError

  // Try different error message locations
  if (apiError.data?.error?.message) {
    return apiError.data.error.message
  }

  if (apiError.data?.message) {
    return apiError.data.message
  }

  if (apiError.message) {
    return apiError.message
  }

  if (apiError.statusMessage) {
    return apiError.statusMessage
  }

  // Handle HTTP status codes
  if (apiError.statusCode) {
    switch (apiError.statusCode) {
      case 400:
        return 'Invalid request. Please check your input.'
      case 401:
        return 'Authentication required. Please log in.'
      case 403:
        return 'You do not have permission to perform this action.'
      case 404:
        return 'Resource not found.'
      case 429:
        return 'Too many requests. Please try again later.'
      case 500:
        return 'Server error. Please try again later.'
      default:
        return defaultMessage
    }
  }

  return defaultMessage
}

/**
 * Extract error code from API error
 */
export function extractErrorCode(error: unknown): string | null {
  if (!error) {
    return null
  }

  const apiError = error as ApiError
  return apiError.data?.error?.code || null
}

/**
 * Check if error is a rate limit error
 */
export function isRateLimitError(error: unknown): boolean {
  const code = extractErrorCode(error)
  return code === 'RATE_LIMIT_EXCEEDED'
}

/**
 * Check if error is an authentication error
 */
export function isAuthError(error: unknown): boolean {
  const apiError = error as ApiError
  return apiError.statusCode === 401 || apiError.statusCode === 403
}

