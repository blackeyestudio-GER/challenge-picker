<script setup lang="ts">
import { STATUS_DESIGNS, DEFAULT_STATUS_DESIGN, isValidStatusDesign, STATUS_DESIGN_LABELS, type StatusDesign } from '~/types/obs-designs'

// Public overlay page - shows game status
const route = useRoute()
const uuid = route.params.uuid as string

const { fetchPlayScreen, startPlayScreenPolling, playScreenData, loading, error } = usePlaythrough()

// Design state and validation
const design = ref<StatusDesign>(DEFAULT_STATUS_DESIGN)
const invalidDesign = ref<string | null>(null)

// Fetch user's preferences if no query param provided
const loadDesign = async () => {
  // 1. Check query param first (explicit override)
  if (route.query.design) {
    const requestedDesign = route.query.design as string
    if (isValidStatusDesign(requestedDesign)) {
      design.value = requestedDesign
      invalidDesign.value = null
      return
    } else {
      // Invalid design requested
      invalidDesign.value = requestedDesign
      design.value = DEFAULT_STATUS_DESIGN
      return
    }
  }

  // 2. Fetch user's saved preference from their playthrough
  if (playScreenData.value?.id) {
    try {
      const config = useRuntimeConfig()
      const response = await $fetch<{ success: boolean; data: { statusDesign: string } }>(
        `${config.public.apiBase}/api/play/${uuid}/preferences`
      )
      if (response.success && isValidStatusDesign(response.data.statusDesign)) {
        design.value = response.data.statusDesign
        invalidDesign.value = null
      }
    } catch (err) {
      console.error('Failed to load user preferences, using default', err)
    }
  }
}

// Load design after play screen data is fetched
watch(() => playScreenData.value, (data) => {
  if (data) {
    loadDesign()
  }
}, { immediate: true })

// Supported designs list for error message
const supportedDesigns = computed(() => 
  STATUS_DESIGNS.map(d => `"${d}" (${STATUS_DESIGN_LABELS[d]})`).join(', ')
)

let stopPolling: (() => void) | null = null

onMounted(async () => {
  await fetchPlayScreen(uuid)
  stopPolling = startPlayScreenPolling(uuid, 2000)
})

onUnmounted(() => {
  if (stopPolling) stopPolling()
})

// Status configurations
const statusWord = computed(() => {
  if (!playScreenData.value) return ''
  switch (playScreenData.value.status) {
    case 'setup': return 'SETUP'
    case 'active': return 'LIVE'
    case 'paused': return 'PAUSED'
    case 'completed': return 'ENDED'
    default: return ''
  }
})

const statusSymbol = computed(() => {
  if (!playScreenData.value) return ''
  switch (playScreenData.value.status) {
    case 'setup': return '⚙️'
    case 'active': return '▶️'
    case 'paused': return '⏸️'
    case 'completed': return '⏹️'
    default: return ''
  }
})

const statusColor = computed(() => {
  if (!playScreenData.value) return 'text-gray-900'
  switch (playScreenData.value.status) {
    case 'setup': return 'text-yellow-600'
    case 'active': return 'text-green-600'
    case 'paused': return 'text-orange-600'
    case 'completed': return 'text-gray-600'
    default: return 'text-gray-900'
  }
})

const buttonBg = computed(() => {
  if (!playScreenData.value) return 'bg-gray-800'
  switch (playScreenData.value.status) {
    case 'setup': return 'bg-gray-800'
    case 'active': return 'bg-gray-800'
    case 'paused': return 'bg-gray-800'
    case 'completed': return 'bg-gray-800'
    default: return 'bg-gray-800'
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4">
    <!-- Invalid Design Error -->
    <div v-if="invalidDesign" class="text-center max-w-2xl">
      <div class="text-red-600 text-3xl font-bold mb-4">
        Invalid Status Design
      </div>
      <div class="text-gray-800 text-xl mb-4">
        Design "{{ invalidDesign }}" is not supported.
      </div>
      <div class="text-gray-700 text-lg">
        Supported designs: {{ supportedDesigns }}
      </div>
      <div class="mt-6 text-gray-600 text-base">
        Using default: {{ DEFAULT_STATUS_DESIGN }}
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-red-500 text-3xl font-bold">
      ERROR
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="text-gray-800 text-3xl font-bold">
      ...
    </div>

    <!-- Word Design -->
    <div v-else-if="playScreenData && design === 'word'" :class="['text-8xl font-black tracking-wider', statusColor]">
      {{ statusWord }}
    </div>

    <!-- Symbols Design -->
    <div v-else-if="playScreenData && design === 'symbols'" class="text-9xl">
      {{ statusSymbol }}
    </div>

    <!-- Buttons Design -->
    <div v-else-if="playScreenData && design === 'buttons'" class="text-center">
      <div :class="['rounded-2xl px-12 py-8 inline-flex items-center justify-center', buttonBg]">
        <span :class="['text-8xl', statusColor]">{{ statusSymbol }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Clean, minimal styling for OBS */
body {
  margin: 0;
  padding: 0;
}
</style>
