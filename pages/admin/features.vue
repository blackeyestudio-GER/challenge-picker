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
  <div class="max-w-7xl mx-auto py-8 px-4 admin-features-page">
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
        class="admin-panel p-6 transition-all"
        :class="feature.enabled ? 'admin-features-page__card admin-features-page__card--enabled' : 'admin-features-page__card'"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-4 flex-1">
            <div
              class="flex-shrink-0 admin-icon-orb admin-features-page__icon-orb"
              :class="feature.enabled ? 'admin-features-page__icon-orb--enabled' : 'admin-features-page__icon-orb--disabled'"
            >
              <Icon
                :name="getFeatureIcon(feature.key)"
                class="w-6 h-6"
              />
            </div>

            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-bold text-[var(--color-text-primary)]">{{ feature.name }}</h3>
                <span
                  class="admin-badge"
                  :class="feature.enabled ? 'admin-badge--success' : 'admin-badge--secondary'"
                >
                  {{ feature.enabled ? 'ENABLED' : 'DISABLED' }}
                </span>
              </div>
              <p class="admin-text-muted">{{ feature.description }}</p>

              <div v-if="feature.key === 'browse_community_runs'" class="mt-3 text-sm admin-text-subtle">
                <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
                Note: Browse button will still only show if completed runs with videos exist
              </div>
              <div v-if="feature.key === 'shop'" class="mt-3 text-sm admin-text-subtle">
                <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
                Configure shop settings in the <NuxtLink to="/admin/shop" class="admin-link">Shop tab</NuxtLink>
              </div>
            </div>
          </div>

          <button
            :disabled="updating === feature.key"
            class="ml-4 admin-toggle disabled:opacity-50 disabled:cursor-not-allowed"
            :class="feature.enabled ? 'admin-toggle--active' : ''"
            @click="toggleFeature(feature)"
          >
            <span class="sr-only">Toggle {{ feature.name }}</span>
            <span class="admin-toggle__thumb">
              <Icon
                v-if="updating === feature.key"
                name="heroicons:arrow-path"
                class="w-4 h-4 animate-spin"
              />
              <Icon
                v-else
                :name="feature.enabled ? 'heroicons:check' : 'heroicons:x-mark'"
                class="w-4 h-4"
              />
            </span>
          </button>
        </div>
      </div>
    </div>

    <div class="mt-8 admin-panel admin-features-page__info-box p-6">
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

<style scoped>
.admin-features-page__card--enabled {
  border-color: var(--status-active-border);
}

.admin-features-page__icon-orb--enabled {
  background-color: var(--status-active-bg);
  border-color: var(--status-active-border);
  color: var(--status-active-text);
}

.admin-features-page__icon-orb--disabled {
  color: var(--color-text-muted);
}

.admin-features-page__info-box {
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-accent-primary) 12%, var(--color-bg-card)), color-mix(in srgb, var(--color-bg-card) 92%, rgba(0, 0, 0, 0.03)));
}
</style>
