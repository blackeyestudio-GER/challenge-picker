<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { FeatureSettingItem } from '~/generated/api-contracts'
import { useAdmin } from '~/composables/useAdmin'
import { Icon } from '#components'
import AdminHeader from '~/components/admin/AdminHeader.vue'

definePageMeta({
  middleware: 'admin'
})

type Feature = FeatureSettingItem

const { fetchFeatureSettings, updateFeatureSetting } = useAdmin()
const { success, notifyApiError } = useNotify()
const features = ref<Feature[]>([])
const loading = ref(true)
const updating = ref<string | null>(null)
const error = ref<string | null>(null)

onMounted(async () => {
  await loadFeatures()
})

const loadFeatures = async () => {
  loading.value = true
  error.value = null
  try {
    features.value = await fetchFeatureSettings()
  } catch (err: unknown) {
    error.value = 'Failed to load features'
    notifyApiError(err, 'Failed to load features')
  } finally {
    loading.value = false
  }
}

const toggleFeature = async (feature: Feature) => {
  updating.value = feature.key
  try {
    const updatedFeature = await updateFeatureSetting(feature.key, !feature.enabled)
    
    // Update local state
    feature.enabled = updatedFeature.enabled
    success(`${feature.name} ${feature.enabled ? 'enabled' : 'disabled'}`)
  } catch (err: unknown) {
    error.value = 'Failed to update feature'
    notifyApiError(err, 'Failed to update feature')
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
    <AdminHeader
      title="Feature Management"
      description="Enable or disable features across the platform"
      back-to="/admin"
      back-label="Back to Admin Dashboard"
    />

    <LoadingState v-if="loading" message="Loading features..." />

    <ErrorState v-else-if="error" :message="error" />

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
            :disabled="updating === feature.key"
            class="ml-4 relative inline-flex h-10 w-20 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-cyan focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="feature.enabled ? 'bg-green-600' : 'bg-gray-700'"
            @click="toggleFeature(feature)"
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
