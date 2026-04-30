import { ref } from 'vue'

export interface AsyncActionOptions {
  /** Shown via toast on failure */
  errorFallback?: string
  /** If set, toast on success */
  successMessage?: string
}

/**
 * Wraps an async operation with loading state and optional toasts.
 */
export function useAsyncAction() {
  const loading = ref(false)
  const { success, notifyApiError } = useNotify()

  const run = async <T>(
    fn: () => Promise<T>,
    options?: AsyncActionOptions
  ): Promise<T | undefined> => {
    loading.value = true
    try {
      const result = await fn()
      if (options?.successMessage) {
        success(options.successMessage)
      }
      return result
    } catch (err) {
      notifyApiError(err, options?.errorFallback)
      return undefined
    } finally {
      loading.value = false
    }
  }

  return { loading, run }
}
