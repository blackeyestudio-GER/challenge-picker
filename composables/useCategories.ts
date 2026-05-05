import type { CategoriesResponse, CategoryItem } from '~/generated/api-contracts'
import { extractErrorMessage } from '~/utils/errorHandler'

export type Category = CategoryItem

export const useCategories = () => {
  const categories = ref<Category[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchCategories = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<CategoriesResponse>('/api/categories')
      
      if (response.success) {
        categories.value = response.data
      } else {
        throw new Error('Failed to fetch categories')
      }
    } catch (err: unknown) {
      error.value = extractErrorMessage(err, 'Failed to fetch categories')
      categories.value = []
    } finally {
      loading.value = false
    }
  }

  return {
    categories: readonly(categories),
    loading: readonly(loading),
    error: readonly(error),
    fetchCategories
  }
}
