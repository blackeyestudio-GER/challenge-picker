<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'

definePageMeta({
  middleware: 'admin'
})

interface Feature {
  key: string
  name: string
  description: string
  enabled: boolean
}

const { token } = useAuth()
const features = ref<Feature[]>([])
const loading = ref(true)
const updating = ref<string | null>(null)
const error = ref<string | null>(null)

onMounted(async () => {
  await loadFeatures()
})

const getAuthHeader = () => ({
  'Authorization': `Bearer ${token.value}`,
  'Content-Type': 'application/json'
})

const loadFeatures = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $fetch<{ success: boolean; data: { features: Feature[] } }>(
      '/api/admin/features/settings',
      { headers: getAuthHeader() }
    )
    features.value = response.data.features
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to load features'
    console.error('Failed to load features:', err)
  } finally {
    loading.value = false
  }
}

const toggleFeature = async (feature: Feature) => {
  updating.value = feature.key
  try {
    await $fetch('/api/admin/features/settings', {
      method: 'PUT',
      headers: getAuthHeader(),
      body: {
        featureKey: feature.key,
        enabled: !feature.enabled
      }
    })
    
    // Update local state
    feature.enabled = !feature.enabled
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to update feature'
    console.error('Failed to update feature:', err)
  } finally {
    updating.value = null
  }
}

const getFeatureIcon = (key: string) => {
  switch (key) {
    case 'browse_community_runs':
      return 'heroicons:film'
    case 'shop':
      return 'heroicons:shopping-bag'
    default:
      return 'heroicons:cog-6-tooth'
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header with Back Button -->
    <div class="mb-8">
      <NuxtLink
        to="/admin"
        class="inline-flex items-center gap-2 text-cyan hover:text-cyan-light mb-4 transition-colors"
      >
        <Icon name="heroicons:arrow-left" class="w-5 h-5" />
        Back to Admin Dashboard
      </NuxtLink>
      <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
        Feature Management
      </h1>
      <p class="text-gray-300">Enable or disable features across the platform</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan mb-4"></div>
      <p class="text-white">Loading features...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-600/20 border border-red-500 text-red-300 p-4 rounded-lg flex items-center gap-3">
      <Icon name="heroicons:exclamation-triangle" class="w-6 h-6 flex-shrink-0" />
      <p>{{ error }}</p>
    </div>

    <!-- Features List -->
    <div v-else class="space-y-4">
      <div
        v-for="feature in features"
        :key="feature.key"
        class="bg-gray-800/80 backdrop-blur-sm border rounded-lg p-6 transition-all"
        :class="feature.enabled ? 'border-green-500/40' : 'border-gray-700'"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-4 flex-1">
            <!-- Icon -->
            <div
              class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center transition-all"
              :class="feature.enabled ? 'bg-green-500/20' : 'bg-gray-700'"
            >
              <Icon
                :name="getFeatureIcon(feature.key)"
                class="w-6 h-6"
                :class="feature.enabled ? 'text-green-500' : 'text-gray-400'"
              />
            </div>

            <!-- Info -->
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-bold text-white">{{ feature.name }}</h3>
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="feature.enabled 
                    ? 'bg-green-500/20 text-green-500' 
                    : 'bg-gray-700 text-gray-400'"
                >
                  {{ feature.enabled ? 'ENABLED' : 'DISABLED' }}
                </span>
              </div>
              <p class="text-gray-400">{{ feature.description }}</p>

              <!-- Feature-specific info -->
              <div v-if="feature.key === 'browse_community_runs'" class="mt-3 text-sm text-gray-500">
                <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
                Note: Browse button will still only show if completed runs with videos exist
              </div>
              <div v-if="feature.key === 'shop'" class="mt-3 text-sm text-gray-500">
                <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
                Configure shop settings in the <NuxtLink to="/admin/shop" class="text-cyan hover:underline">Shop tab</NuxtLink>
              </div>
            </div>
          </div>

          <!-- Toggle Button -->
          <button
            @click="toggleFeature(feature)"
            :disabled="updating === feature.key"
            class="ml-4 relative inline-flex h-10 w-20 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-cyan focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="feature.enabled ? 'bg-green-600' : 'bg-gray-700'"
          >
            <span class="sr-only">Toggle {{ feature.name }}</span>
            <span
              class="inline-block h-8 w-8 transform rounded-full bg-white transition-transform flex items-center justify-center"
              :class="feature.enabled ? 'translate-x-10' : 'translate-x-1'"
            >
              <Icon
                v-if="updating === feature.key"
                name="heroicons:arrow-path"
                class="w-4 h-4 text-gray-600 animate-spin"
              />
              <Icon
                v-else
                :name="feature.enabled ? 'heroicons:check' : 'heroicons:x-mark'"
                class="w-4 h-4"
                :class="feature.enabled ? 'text-green-600' : 'text-gray-600'"
              />
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Info Box -->
    <div class="mt-8 bg-blue-600/20 border border-blue-500 text-blue-300 p-6 rounded-lg">
      <div class="flex items-start gap-3">
        <Icon name="heroicons:light-bulb" class="w-6 h-6 flex-shrink-0 mt-1" />
        <div>
          <h3 class="font-bold mb-2">Feature Toggle System</h3>
          <ul class="list-disc list-inside space-y-1 text-sm">
            <li>Features can be enabled/disabled instantly across the entire platform</li>
            <li>Users will see/hide features based on these settings</li>
            <li>Perfect for maintenance, testing, or controlled rollouts</li>
            <li>Changes take effect immediately (users may need to refresh)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

