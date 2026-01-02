<script setup lang="ts">
import ChallengeSomeoneModal from '~/components/modal/ChallengeSomeoneModal.vue'

definePageMeta({
  layout: false // Play screen has its own full-page design
})

const { fetchMyPlayScreen, fetchPlayScreen, startPlaythrough, pausePlaythrough, resumePlaythrough, endPlaythrough, playScreenData, loading, error } = usePlaythrough()
const { user } = useAuth()
const route = useRoute()

const actionLoading = ref(false)
const authRequired = ref(false)
const showStopModal = ref(false)
const shareButtonText = ref('Share with Viewers')
const showChallengeModal = ref(false)

// Card designs and display mode
const cardDesigns = ref<Record<string, any>>({})
const cardDesignsLoading = ref(false)
const designMode = ref<{
  displayIcon: boolean
  displayText: boolean
}>({
  displayIcon: false,
  displayText: false
})

// Check if current user is the host (owns this playthrough)
const isHost = computed(() => {
  if (!user.value || !playScreenData.value) return false
  return user.value.uuid === playScreenData.value.userUuid
})

// Active rules polling with client-side countdown
const activeRules = ref<Array<{
  id: number
  ruleId: number
  ruleName: string
  ruleType: string
  type: 'permanent' | 'time' | 'counter' | 'hybrid'
  currentAmount: number | null
  initialAmount: number | null
  durationSeconds: number | null
  expiresAt: string | null
  timeRemaining: number | null
  startedAt: string | null
  clientTimeRemaining?: number // Client-side countdown
}>>([])
const activeRulesLoading = ref(false)
let activeRulesPollInterval: number | null = null

// Available rules from playthrough configuration
// This shows ALL enabled rules in the ruleset with their variants (different durations/amounts)
const availableRules = computed(() => {
  if (!playScreenData.value?.configuration?.rules) return []
  
  return playScreenData.value.configuration.rules
    .filter((rule: any) => rule.isEnabled !== false) // Only show enabled rules
    .map((rule: any) => {
      const cardDesign = rule.tarotCardIdentifier ? cardDesigns.value[rule.tarotCardIdentifier] : null
      return {
        id: rule.id,
        ruleId: rule.ruleId || rule.id,
        ruleName: rule.ruleName || rule.name,
        ruleDescription: rule.ruleDescription || rule.description,
        ruleType: rule.ruleType,
        difficultyLevel: rule.difficultyLevel,
        durationSeconds: rule.durationSeconds,
        amount: rule.amount,
        tarotCardIdentifier: rule.tarotCardIdentifier,
        cardImageBase64: cardDesign?.imageBase64 || null,
        isTemplate: cardDesign?.isTemplate || false,
        iconIdentifier: rule.iconIdentifier,
        iconColor: rule.iconColor,
        iconBrightness: rule.iconBrightness,
        iconOpacity: rule.iconOpacity,
        isDefault: rule.isDefault,
        isEnabled: rule.isEnabled
      }
    })
})

// Default rules (always active from the start, shown in active section)
const permanentRules = computed(() => {
  return availableRules.value.filter(rule => rule.isDefault === true)
})

// Check if user can pick rules
const canPickRules = computed(() => {
  if (!playScreenData.value) return false
  if (playScreenData.value.status !== 'active') return false
  
  // Host can always pick
  if (isHost.value) return true
  
  // Viewers can pick if allowed
  return playScreenData.value.allowViewerPicks
})

// Pick a random rule
const pickingRule = ref(false)
const pickRandomRule = async () => {
  if (!playScreenData.value || pickingRule.value) return
  
  // Get all non-default rules that are available
  const pickableRules = availableRules.value.filter(rule => !rule.isDefault)
  
  if (pickableRules.length === 0) {
    alert('No rules available to pick')
    return
  }
  
  // Pick a random rule
  const randomRule = pickableRules[Math.floor(Math.random() * pickableRules.length)]
  
  pickingRule.value = true
  try {
    const config = useRuntimeConfig()
    const { getAuthHeader } = useAuth()
    
    await $fetch(`${config.public.apiBase}/playthroughs/${playScreenData.value.uuid}/pick-rule`, {
      method: 'POST',
      headers: getAuthHeader(),
      body: {
        ruleId: randomRule.ruleId,
        difficultyLevel: randomRule.difficultyLevel
      }
    })
    
    // Refresh active rules
    await fetchActiveRules()
  } catch (err: any) {
    const errorMessage = err.data?.error?.message || 'Failed to pick rule'
    alert(errorMessage)
    console.error('Failed to pick rule:', err)
  } finally {
    pickingRule.value = false
  }
}

// Fetch card designs when playthrough data is loaded
// Always fetch using the host's active design set for consistency
const fetchCardDesigns = async () => {
  if (!playScreenData.value?.configuration?.rules) return
  
  cardDesignsLoading.value = true
  try {
    const config = useRuntimeConfig()
    const hostUuid = playScreenData.value.userUuid
    
    // Fetch the host's active design set info (to know display mode)
    try {
      const designSetResponse = await $fetch<{
        success: boolean
        data: {
          displayIcon?: boolean
          displayText?: boolean
        }
      }>(`${config.public.apiBase}/users/${hostUuid}/active-design-set`)
      
      if (designSetResponse.success) {
        designMode.value = {
          displayIcon: designSetResponse.data.displayIcon || false,
          displayText: designSetResponse.data.displayText || false
        }
      }
    } catch (err) {
      console.error('Failed to fetch design mode:', err)
      // Default to text-only if fetch fails
      designMode.value = { displayIcon: false, displayText: true }
    }
    
    // Get all unique tarot card identifiers
    const identifiers = Array.from(new Set(
      playScreenData.value.configuration.rules
        .filter((r: any) => r.tarotCardIdentifier)
        .map((r: any) => r.tarotCardIdentifier)
    )) as string[]
    
    // Skip fetching card designs if not displaying full cards
    // (displayIcon or displayText means we're using simplified rendering)
    if (designMode.value.displayIcon || designMode.value.displayText) {
      cardDesignsLoading.value = false
      return
    }
    
    if (identifiers.length === 0) {
      cardDesignsLoading.value = false
      return
    }
    
    // Fetch card designs for the host's active design set
    const url = `${config.public.apiBase}/design/card-designs?identifiers=${identifiers.join(',')}&userUuid=${hostUuid}`
    
    const response = await $fetch<{
      success: boolean
      data: {
        cardDesigns: Record<string, any>
      }
    }>(url)
    
    if (response.success) {
      cardDesigns.value = response.data.cardDesigns
    }
  } catch (err) {
    console.error('Failed to fetch card designs:', err)
  } finally {
    cardDesignsLoading.value = false
  }
}

// Fetch card designs when playthrough data changes
watch(() => playScreenData.value, (newData) => {
  if (newData) {
    fetchCardDesigns()
  }
}, { immediate: true })

// Session timer
const elapsedSeconds = ref(0)
let timerInterval: number | null = null

const updateElapsedTime = () => {
  if (!playScreenData.value?.startedAt) {
    elapsedSeconds.value = 0
    return
  }
  const startTime = new Date(playScreenData.value.startedAt).getTime()
  const now = Date.now()
  elapsedSeconds.value = Math.floor((now - startTime) / 1000)
}

const formatTime = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  if (hours > 0) {
    return `${hours}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const formattedElapsedTime = computed(() => formatTime(elapsedSeconds.value))

// Watch for status changes to start/stop timer
watch(() => playScreenData.value?.status, (status) => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  if (status === 'active') {
    updateElapsedTime()
    timerInterval = setInterval(updateElapsedTime, 1000)
  }
}, { immediate: true })

// Fetch active rules
const fetchActiveRules = async () => {
  if (!playScreenData.value || !user.value) {
    activeRules.value = []
    return
  }

  // Fetch active rules for both active and paused states
  if (playScreenData.value.status !== 'active' && playScreenData.value.status !== 'paused') {
    activeRules.value = []
    return
  }

  activeRulesLoading.value = true
  try {
    const config = useRuntimeConfig()
    const { getAuthHeader } = useAuth()
    
    const response = await $fetch<{
      success: boolean
      data: {
        playthroughId: number
        status: string
        activeRules: typeof activeRules.value
      }
    }>(`${config.public.apiBase}/playthrough/active-rules`, {
      headers: getAuthHeader()
    })

    if (response.success) {
      // Update active rules with server data
      const newRules = response.data.activeRules
      
      // Preserve client-side countdown for existing rules
      newRules.forEach(newRule => {
        const existingRule = activeRules.value.find(r => r.id === newRule.id)
        if (existingRule && existingRule.clientTimeRemaining !== undefined) {
          // Keep client countdown if rule still exists
          newRule.clientTimeRemaining = existingRule.clientTimeRemaining
        } else if (newRule.timeRemaining !== null) {
          // Initialize client countdown from server
          newRule.clientTimeRemaining = newRule.timeRemaining
        }
      })
      
      activeRules.value = newRules
    }
  } catch (err) {
    console.error('Failed to fetch active rules:', err)
  } finally {
    activeRulesLoading.value = false
  }
}

// Client-side countdown for timed rules (only when active, paused when paused)
let countdownInterval: number | null = null

const startCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
  
  countdownInterval = setInterval(() => {
    activeRules.value.forEach(rule => {
      if (rule.type === 'time' || rule.type === 'hybrid') {
        if (rule.clientTimeRemaining !== undefined && rule.clientTimeRemaining > 0) {
          rule.clientTimeRemaining--
        }
      }
    })
  }, 1000)
}

const stopCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
    countdownInterval = null
  }
}

// Start/stop active rules polling and countdown based on status
watch(() => playScreenData.value?.status, (status) => {
  if (activeRulesPollInterval) {
    clearInterval(activeRulesPollInterval)
    activeRulesPollInterval = null
  }
  stopCountdown()

  if (status === 'active' || status === 'paused') {
    // Fetch active rules for both active and paused states
    fetchActiveRules()
    activeRulesPollInterval = setInterval(fetchActiveRules, 3000) // Poll every 3 seconds
    
    // Only countdown when active (pause countdown when paused)
    if (status === 'active') {
      startCountdown()
    }
  } else {
    activeRules.value = []
  }
}, { immediate: true })

// Fetch play screen data on mount
onMounted(async () => {
  const routeUuid = route.params.uuid as string
  
  if (user.value) {
    await fetchMyPlayScreen()
  }
  
  if (!playScreenData.value && routeUuid) {
    try {
      await fetchPlayScreen(routeUuid)
    } catch (err: any) {
      if (err.data?.error?.code === 'AUTH_REQUIRED') {
        authRequired.value = true
        error.value = 'This session requires you to be logged in'
      }
    }
  }
})

// Cleanup on unmount
onUnmounted(() => {
  if (activeRulesPollInterval) clearInterval(activeRulesPollInterval)
  if (timerInterval) clearInterval(timerInterval)
  if (countdownInterval) clearInterval(countdownInterval)
})

// Share link
const shareLink = computed(() => {
  if (!playScreenData.value) return ''
  return `${window.location.origin}/play/${playScreenData.value.uuid}`
})

const copyShareLink = async () => {
  if (!shareLink.value) return
  
  try {
    await navigator.clipboard.writeText(shareLink.value)
    shareButtonText.value = 'Copied!'
    setTimeout(() => {
      shareButtonText.value = 'Share with Viewers'
    }, 2000)
  } catch (err) {
    console.error('Failed to copy link:', err)
  }
}

// Control functions
const handleStart = async () => {
  if (!playScreenData.value || actionLoading.value) return
  actionLoading.value = true
  try {
    await startPlaythrough(playScreenData.value.uuid)
    await fetchMyPlayScreen()
  } catch (err) {
    console.error('Failed to start:', err)
    alert('Failed to start session')
  } finally {
    actionLoading.value = false
  }
}

const handlePause = async () => {
  if (!playScreenData.value || actionLoading.value) return
  actionLoading.value = true
  try {
    await pausePlaythrough(playScreenData.value.uuid)
    await fetchMyPlayScreen()
  } catch (err) {
    console.error('Failed to pause:', err)
    alert('Failed to pause session')
  } finally {
    actionLoading.value = false
  }
}

const handleResume = async () => {
  if (!playScreenData.value || actionLoading.value) return
  actionLoading.value = true
  try {
    await resumePlaythrough(playScreenData.value.uuid)
    await fetchMyPlayScreen()
  } catch (err) {
    console.error('Failed to resume:', err)
    alert('Failed to resume session')
  } finally {
    actionLoading.value = false
  }
}

const handleStop = () => {
  showStopModal.value = true
}

const confirmStop = async () => {
  if (!playScreenData.value || actionLoading.value) return
  actionLoading.value = true
  try {
    await endPlaythrough(playScreenData.value.uuid)
    showStopModal.value = false
    await fetchMyPlayScreen()
  } catch (err) {
    console.error('Failed to end:', err)
    alert('Failed to end session')
  } finally {
    actionLoading.value = false
  }
}

const cancelStop = () => {
  showStopModal.value = false
}

// Status helpers
const statusColor = computed(() => {
  if (!playScreenData.value) return 'bg-gray-500'
  switch (playScreenData.value.status) {
    case 'setup': return 'bg-blue-500'
    case 'active': return 'bg-green-500'
    case 'paused': return 'bg-orange-500'
    case 'completed': return 'bg-gray-500'
    default: return 'bg-gray-500'
  }
})

const statusText = computed(() => {
  if (!playScreenData.value) return 'Unknown'
  switch (playScreenData.value.status) {
    case 'setup': return 'Ready'
    case 'active': return 'Live'
    case 'paused': return 'Paused'
    case 'completed': return 'Ended'
    default: return 'Unknown'
  }
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"></div>
        <p class="text-white/60">Loading session...</p>
      </div>
    </div>

    <!-- Auth Required -->
    <div v-else-if="authRequired" class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 max-w-md text-center border border-white/20">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold mb-2">Login Required</h1>
        <p class="text-white/60 mb-6">This session requires you to be logged in to view</p>
        <NuxtLink to="/login" class="inline-block px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-lg hover:opacity-90 transition font-medium">
          Login to Continue
        </NuxtLink>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error && !playScreenData" class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 max-w-md text-center border border-white/20">
        <div class="text-6xl mb-4">😕</div>
        <h1 class="text-2xl font-bold mb-2">Session Not Found</h1>
        <p class="text-white/60 mb-6">{{ error }}</p>
        <NuxtLink to="/" class="inline-block px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-lg hover:opacity-90 transition font-medium">
          Go to Homepage
        </NuxtLink>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="playScreenData" class="min-h-screen flex flex-col">
      <!-- Top Bar -->
      <div class="bg-black/30 backdrop-blur-md border-b border-white/10 px-6 py-4">
        <div class="max-w-[1800px] mx-auto flex items-center justify-between gap-4">
          <!-- Left: Title & Status -->
          <div class="flex items-center gap-4">
            <div>
              <h1 class="text-2xl font-bold">{{ playScreenData.gameName }}</h1>
              <p class="text-sm text-white/60">{{ playScreenData.rulesetName }}</p>
            </div>
            <div :class="[statusColor, 'px-3 py-1 rounded-full text-sm font-medium']">
              {{ statusText }}
            </div>
          </div>

          <!-- Right: Timer, Controls, Share -->
          <div class="flex items-center gap-6">
            <!-- Session Timer -->
            <div v-if="playScreenData.status === 'active' || playScreenData.status === 'paused'" 
                 class="text-2xl font-mono font-bold">
              {{ formattedElapsedTime }}
            </div>

            <!-- Cassette Recorder Style Controls (Host Only) -->
            <div v-if="isHost" class="flex items-center gap-2 bg-black/50 rounded-lg p-2">
              <!-- Start Button (Setup) -->
              <button
                v-if="playScreenData.status === 'setup'"
                @click="handleStart"
                :disabled="actionLoading"
                class="w-10 h-10 flex items-center justify-center bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed rounded transition"
                title="Start"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                </svg>
              </button>

              <!-- Play Button (Paused) -->
              <button
                v-if="playScreenData.status === 'paused'"
                @click="handleResume"
                :disabled="actionLoading"
                class="w-10 h-10 flex items-center justify-center bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed rounded transition"
                title="Resume"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                </svg>
              </button>

              <!-- Pause Button (Active) -->
              <button
                v-if="playScreenData.status === 'active'"
                @click="handlePause"
                :disabled="actionLoading"
                class="w-10 h-10 flex items-center justify-center bg-orange-600 hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed rounded transition"
                title="Pause"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z"/>
                </svg>
              </button>

              <!-- Stop Button -->
              <button
                v-if="playScreenData.status !== 'completed'"
                @click="handleStop"
                :disabled="actionLoading"
                class="w-10 h-10 flex items-center justify-center bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed rounded transition"
                title="Stop"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <rect x="5" y="5" width="10" height="10" rx="1"/>
                </svg>
              </button>
            </div>

            <!-- Share Button -->
            <button
              @click="copyShareLink"
              class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
              </svg>
              <span class="text-sm font-medium">{{ shareButtonText === 'Copied!' ? 'Copied!' : 'Share with Viewers' }}</span>
            </button>

            <!-- Challenge Button (Host Only) -->
            <button
              v-if="isHost"
              @click="() => { console.log('Challenge button clicked'); showChallengeModal = true }"
              class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg transition flex items-center gap-2"
              title="Get a shareable link to challenge anyone"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
              </svg>
              <span class="text-sm font-medium">Challenge Someone</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content: Split Layout -->
      <div class="flex-1 max-w-[1800px] mx-auto w-full p-6">
        <!-- Action Button Bar -->
        <div class="mb-6 bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-4">
          <div class="flex flex-wrap items-center justify-center gap-3">
            <!-- Pick Random Card Button -->
            <button
              v-if="isHost || playScreenData.allowViewerPicks"
              @click="pickRandomRule"
              :disabled="pickingRule || !canPickRules"
              class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg"
              :title="canPickRules ? 'Pick a random rule from available rules' : 'Start the session to pick rules'"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              <span>Draw Random Card</span>
            </button>
            
            <!-- More buttons can be added here in the future -->
          </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-full">
          <!-- Left: Available Rules -->
          <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Available Rules
              <span class="text-sm text-white/60">({{ availableRules.length }})</span>
            </h2>
            
            <div v-if="cardDesignsLoading" class="text-center py-12 text-white/40">
              <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"></div>
              Loading cards...
            </div>
            
            <div v-else-if="availableRules.length === 0" class="text-center py-12 text-white/40">
              No rules configured
            </div>
            
            <div v-else>
              <div class="space-y-3">
              <div
                v-for="rule in availableRules"
                :key="rule.id"
                class="flex gap-4 bg-white/5 hover:bg-white/10 rounded-lg p-3 border border-white/10 transition"
              >
                <!-- Card Design (Left) -->
                <div class="flex-shrink-0 w-24">
                  <RuleCard
                    :rule-id="rule.ruleId"
                    :rule-name="rule.ruleName"
                    :rule-type="rule.ruleType"
                    :rule-description="null"
                    :difficulty-level="rule.difficultyLevel"
                    :duration-seconds="rule.durationSeconds"
                    :amount="rule.amount"
                    :tarot-card-identifier="rule.tarotCardIdentifier"
                    :card-image-base64="rule.cardImageBase64"
                    :icon-identifier="rule.iconIdentifier"
                    :icon-color="rule.iconColor"
                    :icon-brightness="rule.iconBrightness"
                    :icon-opacity="rule.iconOpacity"
                    :is-enabled="rule.isEnabled"
                    :is-default="rule.isDefault"
                    :can-toggle="false"
                    :pickrate="0"
                    :is-premium-design="!rule.isTemplate"
                    :display-icon="designMode.displayIcon"
                    :display-text="designMode.displayText"
                  />
                </div>

                <!-- Rule Details (Right) -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-semibold text-lg">{{ rule.ruleName }}</h3>
                        <span v-if="rule.isDefault" class="text-yellow-400 text-sm" title="Default - Always Active">★</span>
                        
                        <!-- Rule Type Badge -->
                        <span 
                          :class="[
                            'px-2 py-0.5 rounded text-xs capitalize font-medium',
                            rule.ruleType === 'legendary' ? 'bg-yellow-500/20 text-yellow-400' :
                            rule.ruleType === 'court' ? 'bg-purple-500/20 text-purple-400' :
                            'bg-blue-500/20 text-blue-400'
                          ]"
                        >
                          {{ rule.ruleType }}
                        </span>
                      </div>
                      
                      <p v-if="rule.ruleDescription" class="text-sm text-white/70 mt-1 line-clamp-2">
                        {{ rule.ruleDescription }}
                      </p>
                      
                      <div class="flex items-center gap-4 mt-2 text-sm text-white/60">
                        <!-- Duration -->
                        <div v-if="rule.durationSeconds" class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          <span>{{ Math.floor(rule.durationSeconds / 60) }}m {{ rule.durationSeconds % 60 }}s</span>
                        </div>
                        
                        <!-- Amount (for counter rules) -->
                        <div v-if="rule.amount" class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                          </svg>
                          <span>{{ rule.amount }}x</span>
                        </div>
                        
                        <!-- Permanent indicator -->
                        <div v-if="rule.ruleType === 'legendary' && !rule.durationSeconds" class="flex items-center gap-1 text-yellow-400">
                          <span>∞ Permanent</span>
                        </div>
                        
                        <!-- Difficulty -->
                        <div v-if="rule.difficultyLevel" class="text-white/40">
                          Level {{ rule.difficultyLevel }}
                        </div>
                      </div>
                    </div>
                    
                    <!-- Permanent indicator for default rules (always active) -->
                    <div
                      v-if="rule.isDefault"
                      class="px-4 py-2 bg-yellow-500/20 text-yellow-400 rounded-lg flex items-center gap-2 text-sm font-medium"
                      title="This rule is always active"
                    >
                      <span>★</span>
                      <span>Always Active</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
          </div>

          <!-- Right: Active Rules -->
          <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Active Rules
              <span class="text-sm text-white/60">({{ activeRules.length }})</span>
              <span v-if="playScreenData.status === 'paused'" class="text-xs text-orange-400 ml-2">⏸ Paused</span>
            </h2>
            
            <div v-if="playScreenData.status === 'completed'" class="text-center py-12 text-white/40">
              Session ended
            </div>
            
            <div v-else-if="activeRules.length === 0 && permanentRules.length === 0" class="text-center py-12 text-white/40">
              <p>No default rules configured</p>
              <p v-if="playScreenData.status === 'setup'" class="text-xs mt-2">Start the session to begin adding optional rules</p>
            </div>
            
            <div v-else class="space-y-3">
              <!-- Permanent Rules (Always Active) -->
              <div v-if="permanentRules.length > 0" class="mb-4">
                <h3 class="text-sm font-semibold text-yellow-400 mb-2 flex items-center gap-2">
                  <span>★</span>
                  <span>Permanent Rules ({{ permanentRules.length }})</span>
                </h3>
                <div class="space-y-2">
                  <div
                    v-for="rule in permanentRules"
                    :key="rule.id"
                    class="bg-gradient-to-r from-yellow-500/10 to-orange-500/10 rounded-lg p-3 border border-yellow-500/30"
                  >
                    <div class="flex items-center gap-3">
                      <!-- Card Preview -->
                      <div class="flex-shrink-0 w-16">
                        <RuleCard
                          :rule-id="rule.ruleId"
                          :rule-name="rule.ruleName"
                          :rule-type="rule.ruleType"
                          :rule-description="null"
                          :difficulty-level="rule.difficultyLevel"
                          :duration-seconds="rule.durationSeconds"
                          :amount="rule.amount"
                          :tarot-card-identifier="rule.tarotCardIdentifier"
                          :card-image-base64="rule.cardImageBase64"
                          :icon-identifier="rule.iconIdentifier"
                          :icon-color="rule.iconColor"
                          :icon-brightness="rule.iconBrightness"
                          :icon-opacity="rule.iconOpacity"
                          :is-enabled="true"
                          :is-default="true"
                          :can-toggle="false"
                          :pickrate="0"
                          :is-premium-design="!rule.isTemplate"
                          :display-icon="designMode.displayIcon"
                          :display-text="designMode.displayText"
                        />
                      </div>
                      
                      <div class="flex-1">
                        <h4 class="font-semibold text-yellow-400">{{ rule.ruleName }}</h4>
                        <p v-if="rule.ruleDescription" class="text-xs text-white/60 mt-1">{{ rule.ruleDescription }}</p>
                        <div class="text-xs text-yellow-400/80 mt-1">∞ Permanent</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Active Optional Rules -->
              <div v-if="activeRules.length > 0">
                <h3 v-if="permanentRules.length > 0" class="text-sm font-semibold text-cyan-400 mb-2">
                  Active Optional Rules ({{ activeRules.length }})
                </h3>
              <div
                v-for="rule in activeRules"
                :key="rule.id"
                class="bg-gradient-to-r from-cyan-500/10 to-blue-500/10 rounded-lg p-4 border border-cyan-500/30"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1">
                    <h3 class="font-semibold">{{ rule.ruleName }}</h3>
                    <div class="flex items-center gap-3 mt-2 text-xs text-white/50">
                      <span class="px-2 py-1 bg-white/10 rounded capitalize">{{ rule.ruleType }}</span>
                    </div>
                  </div>
                  
                  <!-- Timer or Counter -->
                  <div class="text-right">
                    <!-- Countdown Timer -->
                    <div v-if="rule.type === 'time' || rule.type === 'hybrid'" class="text-2xl font-mono font-bold text-cyan-400">
                      {{ formatTime(rule.clientTimeRemaining ?? rule.timeRemaining ?? 0) }}
                    </div>
                    
                    <!-- Counter -->
                    <div v-if="rule.type === 'counter' || rule.type === 'hybrid'" class="text-2xl font-mono font-bold text-orange-400">
                      {{ rule.currentAmount ?? 0 }} / {{ rule.initialAmount ?? 0 }}
                    </div>
                    
                    <!-- Permanent -->
                    <div v-if="rule.type === 'permanent'" class="text-sm text-yellow-400 font-medium">
                      ★ Permanent
                    </div>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stop Confirmation Modal -->
    <div v-if="showStopModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 px-4">
      <div class="bg-gray-900 rounded-2xl p-8 max-w-md w-full border border-red-500/50 shadow-2xl">
        <div class="text-center">
          <div class="text-6xl mb-4">⚠️</div>
          <h2 class="text-2xl font-bold mb-3">End Playthrough?</h2>
          <p class="text-white/70 mb-6">
            This will permanently end your session. You won't be able to restart it. Are you sure?
          </p>
          <div class="flex gap-3">
            <button
              @click="cancelStop"
              :disabled="actionLoading"
              class="flex-1 px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition font-medium disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              @click="confirmStop"
              :disabled="actionLoading"
              class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 rounded-lg transition font-medium disabled:opacity-50"
            >
              {{ actionLoading ? 'Ending...' : 'End Session' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Challenge Modal -->
    <ChallengeSomeoneModal
      :show="showChallengeModal"
      :playthrough-uuid="playScreenData?.uuid || ''"
      @close="showChallengeModal = false"
    />
  </div>
</template>

<style scoped>
/* Custom scrollbar for rules lists */
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
