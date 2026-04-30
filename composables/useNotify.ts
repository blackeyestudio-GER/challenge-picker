import { getApiErrorMessage } from '~/composables/useApiError'

/**
 * Toasts + shared API error parsing (use with existing NotificationToast in app.vue).
 */
export function useNotify() {
  const { success, error, info, warning, removeNotification } = useNotifications()

  const notifyApiError = (err: unknown, fallback?: string) => {
    error(getApiErrorMessage(err, fallback ?? 'Something went wrong. Please try again.'))
  }

  return {
    success,
    error,
    info,
    warning,
    removeNotification,
    notifyApiError,
    getApiErrorMessage,
  }
}
