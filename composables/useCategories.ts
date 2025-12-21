export interface Category {
  id: number
  name: string
  description: string | null
  slug: string
  gameCount: number
}

export const useCategories = () => {
  const categories = ref<Category[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchCategories = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await $fetch<{ success: boolean; data: Category[] }>('/api/categories')
      
      if (response.success) {
        categories.value = response.data
      } else {
        throw new Error('Failed to fetch categories')
      }
    } catch (err: any) {
      console.error('Failed to fetch categories:', err)
      error.value = err.message || 'Failed to fetch categories'
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

