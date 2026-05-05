<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

const props = defineProps<{
  error: {
    statusCode?: number
    statusMessage?: string
    message?: string
  }
}>()

const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme()
})

const handleError = () => clearError({ redirect: '/' })

const statusCode = computed(() => props.error.statusCode ?? 500)

const isNetworkError = computed(() => {
  const combinedMessage = `${props.error.statusMessage ?? ''} ${props.error.message ?? ''}`.toLowerCase()
  return combinedMessage.includes('fetch') || combinedMessage.includes('network') || combinedMessage.includes('unavailable')
})

const title = computed(() => {
  if (statusCode.value === 404) return 'Page Not Found'
  if (statusCode.value === 403) return 'Access Denied'
  if (statusCode.value === 500) return 'Server Error'
  if (isNetworkError.value) return 'Service Unavailable'
  return 'Something Went Wrong'
})

const description = computed(() => {
  if (statusCode.value === 404) return 'The page you requested does not exist or was moved.'
  if (statusCode.value === 403) return 'You do not have permission to view this page.'
  if (isNetworkError.value) return 'The application could not reach the backend service. Please try again in a moment.'
  if (statusCode.value === 500) return 'The server returned an unexpected error. Please try again later.'
  return props.error.message || props.error.statusMessage || 'An unexpected error occurred.'
})

const iconName = computed(() => {
  if (statusCode.value === 404) return 'heroicons:magnifying-glass'
  if (statusCode.value === 403) return 'heroicons:lock-closed'
  if (isNetworkError.value) return 'heroicons:cloud-slash'
  if (statusCode.value === 500) return 'heroicons:exclamation-triangle'
  return 'heroicons:face-frown'
})
</script>

<template>
  <div class="app-error-page">
    <div class="app-error-page__card">
      <Icon :name="iconName" class="app-error-page__icon" />
      <p v-if="error.statusCode" class="app-error-page__code">
        Error {{ error.statusCode }}
      </p>
      <h1 class="app-error-page__title">{{ title }}</h1>
      <p class="app-error-page__description">{{ description }}</p>

      <div class="app-error-page__actions">
        <button class="btn btn-primary" @click="handleError">
          <Icon name="heroicons:home" class="w-5 h-5" />
          Go Home
        </button>
        <button class="btn btn-secondary" @click="$router.back()">
          <Icon name="heroicons:arrow-left" class="w-5 h-5" />
          Go Back
        </button>
      </div>

      <div class="app-error-page__links">
        <NuxtLink to="/">Home</NuxtLink>
        <NuxtLink to="/dashboard">Dashboard</NuxtLink>
        <NuxtLink to="/runs">Runs</NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-error-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.app-error-page__card {
  width: min(100%, 36rem);
  padding: 2rem;
  border-radius: 1.5rem;
  border: 1px solid var(--color-border-secondary);
  background:
    radial-gradient(circle at top, color-mix(in srgb, var(--color-accent-primary) 14%, transparent), transparent 55%),
    var(--color-bg-card);
  text-align: center;
  box-shadow: var(--shadow-card);
}

.app-error-page__icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1rem;
  color: var(--color-icon-danger);
}

.app-error-page__code {
  margin: 0 0 0.5rem;
  font-size: 0.875rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.app-error-page__title {
  margin: 0 0 0.75rem;
  color: var(--color-text-primary);
  font-size: 2rem;
  font-weight: 800;
}

.app-error-page__description {
  margin: 0 auto 1.5rem;
  max-width: 30rem;
  color: var(--color-text-secondary);
  line-height: 1.6;
}

.app-error-page__actions {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.app-error-page__links {
  margin-top: 1.5rem;
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.app-error-page__links a {
  color: var(--color-accent-primary);
  text-decoration: none;
}

.app-error-page__links a:hover {
  color: var(--color-accent-primary-hover);
}
</style>
