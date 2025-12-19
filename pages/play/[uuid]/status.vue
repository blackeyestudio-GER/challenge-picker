<script setup lang="ts">
import { STATUS_DESIGNS, DEFAULT_STATUS_DESIGN, isValidStatusDesign, STATUS_DESIGN_LABELS, type StatusDesign } from '~/types/obs-designs'

definePageMeta({
  layout: false
})

// Public overlay page - shows game status for a user's active game
const route = useRoute()
const userUuid = route.params.uuid as string // Now expects user UUID, not playthrough UUID

const { fetchPlayScreenByUserUuid, startPlayScreenPollingByUserUuid, playScreenData, loading, error } = usePlaythrough()

// Design state and validation
const design = ref<StatusDesign>(DEFAULT_STATUS_DESIGN)
const invalidDesign = ref<string | null>(null)
const chromaKeyColor = ref('#00FF00') // Default chroma green

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

  // 2. Fetch user's saved preference
  try {
    const config = useRuntimeConfig()
    const response = await $fetch<{ success: boolean; data: { statusDesign: string; chromaKeyColor: string } }>(
      `${config.public.apiBase}/user/${userUuid}/obs-preferences`
    )
    if (response.success) {
      if (isValidStatusDesign(response.data.statusDesign)) {
        design.value = response.data.statusDesign
        invalidDesign.value = null
      }
      if (response.data.chromaKeyColor) {
        chromaKeyColor.value = response.data.chromaKeyColor
      }
    }
  } catch (err) {
    console.error('Failed to load user preferences, using default', err)
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
  await fetchPlayScreenByUserUuid(userUuid)
  stopPolling = startPlayScreenPollingByUserUuid(userUuid, 2000)
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
  if (!playScreenData.value) return '#111'
  switch (playScreenData.value.status) {
    case 'setup': return '#ca8a04'
    case 'active': return '#16a34a'
    case 'paused': return '#ea580c'
    case 'completed': return '#4b5563'
    default: return '#111'
  }
})
</script>

<template>
  <div :style="{ 
    backgroundColor: chromaKeyColor,
    minHeight: '100vh',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    margin: 0,
    padding: 0
  }">
    <!-- Loading Spinner -->
    <div v-if="!playScreenData || loading" :style="{ 
      width: '40px',
      height: '40px',
      border: '4px solid rgba(255,255,255,0.3)',
      borderTop: '4px solid white',
      borderRadius: '50%',
      animation: 'spin 1s linear infinite'
    }"></div>

    <!-- Word Design -->
    <div v-else-if="playScreenData && design === 'word'" :style="{ 
      fontSize: '120px',
      fontWeight: '900',
      letterSpacing: '0.05em',
      color: statusColor
    }">
      {{ statusWord }}
    </div>

    <!-- Symbols Design -->
    <div v-else-if="playScreenData && design === 'symbols'" :style="{ fontSize: '140px' }">
      {{ statusSymbol }}
    </div>

    <!-- Buttons Design -->
    <div v-else-if="playScreenData && design === 'buttons'" :style="{ textAlign: 'center' }">
      <div :style="{ 
        borderRadius: '16px',
        padding: '32px 48px',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#1f2937'
      }">
        <span :style="{ fontSize: '120px', color: statusColor }">{{ statusSymbol }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
</style>
