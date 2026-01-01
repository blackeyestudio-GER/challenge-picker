<script setup lang="ts">
definePageMeta({
  middleware: 'auth', // Only authenticated users (gamehost) can access
  layout: false // Play screen has its own full-page design
})

const { fetchMyPlayScreen, fetchPlayScreen, startPlaythrough, pausePlaythrough, resumePlaythrough, endPlaythrough, playScreenData, loading, error } = usePlaythrough()
const { user } = useAuth()
const route = useRoute()

const actionLoading = ref(false)

// Active rules polling
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
}>>([])
const activeRulesLoading = ref(false)
let activeRulesPollInterval: number | null = null

// Session timer (for active sessions)
const elapsedSeconds = ref(0)
let timerInterval: number | null = null

// Calculate elapsed time from startedAt
const updateElapsedTime = () => {
  if (!playScreenData.value?.startedAt) {
    elapsedSeconds.value = 0
    return
  }

  const startTime = new Date(playScreenData.value.startedAt).getTime()
  const now = Date.now()
  elapsedSeconds.value = Math.floor((now - startTime) / 1000)
}

// Format seconds to MM:SS
const formatTime = (seconds: number): string => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

// Computed formatted time
const formattedElapsedTime = computed(() => formatTime(elapsedSeconds.value))

// Watch for status changes to start/stop timer
watch(() => playScreenData.value?.status, (status) => {
  // Clear existing timer
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }

  // Only run timer when active
  if (status === 'active') {
    updateElapsedTime()
    timerInterval = setInterval(updateElapsedTime, 1000)
  }
}, { immediate: true })

// Fetch active rules (only when status is active)
const fetchActiveRules = async () => {
  if (!playScreenData.value || playScreenData.value.status !== 'active' || !user.value) {
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
      activeRules.value = response.data.activeRules
    }
  } catch (err) {
    console.error('Failed to fetch active rules:', err)
    // Silently fail - don't show error to user
  } finally {
    activeRulesLoading.value = false
  }
}

// Start/stop active rules polling based on status
watch(() => playScreenData.value?.status, (status) => {
  // Clear existing polling
  if (activeRulesPollInterval) {
    clearInterval(activeRulesPollInterval)
    activeRulesPollInterval = null
  }

  // Only poll when active
  if (status === 'active') {
    fetchActiveRules() // Initial fetch
    activeRulesPollInterval = setInterval(fetchActiveRules, 2000) // Poll every 2 seconds
  } else {
    activeRules.value = []
  }
}, { immediate: true })

// Fetch play screen data on mount (no polling - just one fetch)
// Always use authenticated user's UUID to find their active playthrough (only one allowed per user)
// The UUID in the route is ignored - we always find by user UUID
onMounted(async () => {
  if (!user.value) {
    error.value = 'User not authenticated'
    return
  }
  
  // Find active playthrough by user UUID (ensures only one active playthrough per user)
  await fetchMyPlayScreen()
})

// Cleanup on unmount
onUnmounted(() => {
  if (activeRulesPollInterval) {
    clearInterval(activeRulesPollInterval)
  }
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})

// Check if current user is the gamehost
const isGamehost = computed(() => {
  if (!user.value || !playScreenData.value) return false
  return user.value.username === playScreenData.value.gamehostUsername
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
    alert('Share link copied to clipboard!')
  } catch (err) {
    console.error('Failed to copy link:', err)
    alert('Failed to copy link. Please try again.')
  }
}

// Gamehost control functions
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

const handleEnd = async () => {
  if (!playScreenData.value || actionLoading.value) return
  
  if (!confirm('Are you sure you want to end this session? This cannot be undone.')) {
    return
  }
  
  actionLoading.value = true
  try {
    await endPlaythrough(playScreenData.value.uuid)
    await fetchMyPlayScreen()
  } catch (err) {
    console.error('Failed to end:', err)
    alert('Failed to end session')
  } finally {
    actionLoading.value = false
  }
}

// Status display helpers
const statusDisplay = computed(() => {
  if (!playScreenData.value) return { text: '', color: '', icon: '', message: '' }
  
  switch (playScreenData.value.status) {
    case 'setup':
      return { 
        text: 'Setup', 
        color: 'bg-yellow-500',
        icon: '⚙️',
        message: 'Ready to start'
      }
    case 'active':
      return { 
        text: 'Live', 
        color: 'bg-green-500',
        icon: '🎮',
        message: 'Game in progress!'
      }
    case 'paused':
      return { 
        text: 'Paused', 
        color: 'bg-orange-500',
        icon: '⏸️',
        message: 'Session paused'
      }
    case 'completed':
      return { 
        text: 'Ended', 
        color: 'bg-gray-500',
        icon: '🏁',
        message: 'Session has ended'
      }
    default:
      return { text: '', color: '', icon: '', message: '' }
  }
})

// Format time remaining
const formatTimeRemaining = (seconds: number | null): string => {
  if (seconds === null || seconds <= 0) return '00:00'
  return formatTime(seconds)
}
</script>

<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-white"></div>
        <p class="text-white mt-4 text-lg">Loading dashboard...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen px-4 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
      <div class="bg-red-500/20 border-2 border-red-300 rounded-lg p-8 max-w-md text-center">
        <div class="text-6xl mb-4">😕</div>
        <h2 class="text-2xl font-bold text-white mb-2">Session Not Found</h2>
        <p class="text-red-100 mb-6">{{ error }}</p>
        <NuxtLink 
          to="/"
          class="inline-block px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white rounded-lg hover:opacity-90 transition font-medium"
        >
          Go to Homepage
        </NuxtLink>
      </div>
    </div>

    <!-- Dashboard -->
    <div v-else-if="playScreenData && isGamehost" class="container mx-auto px-4 py-8 max-w-7xl min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
      <!-- Header -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 mb-6 shadow-2xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-center space-x-4">
            <!-- Game Image -->
            <div v-if="playScreenData.gameImage" class="w-20 h-20 rounded-lg overflow-hidden bg-white/20 flex-shrink-0">
              <img :src="playScreenData.gameImage" :alt="playScreenData.gameName" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-20 h-20 rounded-lg bg-white/20 flex items-center justify-center text-4xl flex-shrink-0">
              🎮
            </div>
            
            <!-- Game Info -->
            <div>
              <h1 class="text-3xl font-bold text-white mb-1">{{ playScreenData.gameName }}</h1>
              <p class="text-white/80">{{ playScreenData.rulesetName }}</p>
            </div>
          </div>

          <!-- Status Badge and Timer -->
          <div class="flex flex-col items-end gap-2">
            <div :class="[statusDisplay.color, 'px-4 py-2 rounded-full flex items-center space-x-2 text-white font-bold']">
              <span>{{ statusDisplay.icon }}</span>
              <span>{{ statusDisplay.text }}</span>
            </div>
            
            <!-- Session Timer (only when active or paused) -->
            <div v-if="playScreenData.status === 'active' || playScreenData.status === 'paused'" 
                 class="text-white/80 text-sm font-mono bg-white/10 px-3 py-1 rounded-lg">
              ⏱️ {{ formattedElapsedTime }}
            </div>
          </div>
        </div>
      </div>

      <!-- Share Section (always visible) -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 mb-6 shadow-2xl border border-white/20">
        <h2 class="text-xl font-bold text-white mb-4">Share with Viewers</h2>
        <p class="text-white/60 text-sm mb-4">
          Share this link so viewers can watch your session live.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-3">
          <input
            type="text"
            :value="shareLink"
            readonly
            class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg font-mono text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan"
          />
          <button
            @click="copyShareLink"
            class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white rounded-lg hover:opacity-90 transition font-medium whitespace-nowrap"
          >
            📋 Copy Link
          </button>
          <NuxtLink
            :to="shareLink"
            target="_blank"
            class="px-6 py-3 bg-gray-700 border border-gray-600 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium text-center whitespace-nowrap"
          >
            👁️ Preview
          </NuxtLink>
        </div>
      </div>

      <!-- Active Rules List (only when active) -->
      <div v-if="playScreenData.status === 'active'" class="bg-white/10 backdrop-blur-md rounded-2xl p-6 mb-6 shadow-2xl border border-white/20">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-white">Active Rules</h2>
          <div v-if="activeRulesLoading" class="text-white/60 text-sm">Updating...</div>
        </div>
        
        <div v-if="activeRules.length === 0" class="text-center py-8 text-white/60">
          No active rules at the moment
        </div>
        
        <div v-else class="space-y-3">
          <div
            v-for="rule in activeRules"
            :key="rule.id"
            class="bg-white/5 rounded-lg p-4 border border-white/10"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="font-medium text-white mb-1">{{ rule.ruleName }}</div>
                <div class="flex items-center gap-4 text-sm text-white/60">
                  <!-- Counter display -->
                  <span v-if="rule.type === 'counter' || rule.type === 'hybrid'">
                    Count: {{ rule.currentAmount }}{{ rule.initialAmount ? ` / ${rule.initialAmount}` : '' }}
                  </span>
                  <!-- Timer display -->
                  <span v-if="rule.type === 'time' || rule.type === 'hybrid'">
                    Time: {{ formatTimeRemaining(rule.timeRemaining) }}
                  </span>
                  <!-- Permanent -->
                  <span v-if="rule.type === 'permanent'" class="text-green-400">
                    Permanent
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Session Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/20">
          <div class="text-3xl font-bold text-white mb-1">{{ playScreenData.totalRulesCount }}</div>
          <div class="text-white/60 text-sm">Total Rules</div>
        </div>
        <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/20">
          <div class="text-3xl font-bold text-white mb-1">{{ playScreenData.activeRulesCount }}</div>
          <div class="text-white/60 text-sm">Active Rules</div>
        </div>
        <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/20">
          <div class="text-3xl font-bold text-white mb-1">{{ playScreenData.maxConcurrentRules }}</div>
          <div class="text-white/60 text-sm">Max Concurrent</div>
        </div>
      </div>

      <!-- Gamehost Controls -->
      <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/20">
        <!-- Setup Phase Controls -->
        <div v-if="playScreenData.status === 'setup'" class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <button
            @click="handleStart"
            :disabled="actionLoading"
            class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:opacity-90 transition font-bold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ actionLoading ? 'Starting...' : '▶️ Start Session' }}
          </button>
        </div>

        <!-- Active Phase Controls -->
        <div v-else-if="playScreenData.status === 'active'" class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <button
            @click="handlePause"
            :disabled="actionLoading"
            class="px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ⏸️ {{ actionLoading ? 'Pausing...' : 'Pause' }}
          </button>
          <button
            @click="handleEnd"
            :disabled="actionLoading"
            class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ⏹️ {{ actionLoading ? 'Ending...' : 'End Session' }}
          </button>
        </div>

        <!-- Paused Phase Controls -->
        <div v-else-if="playScreenData.status === 'paused'" class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <button
            @click="handleResume"
            :disabled="actionLoading"
            class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ▶️ {{ actionLoading ? 'Resuming...' : 'Resume' }}
          </button>
          <button
            @click="handleEnd"
            :disabled="actionLoading"
            class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ⏹️ {{ actionLoading ? 'Ending...' : 'End Session' }}
          </button>
        </div>

        <!-- Completed Phase -->
        <div v-else-if="playScreenData.status === 'completed'" class="text-center text-white/60">
          <p>Session has ended</p>
          <p v-if="playScreenData.totalDuration" class="text-sm mt-2">
            Duration: {{ Math.floor(playScreenData.totalDuration / 60) }}m {{ playScreenData.totalDuration % 60 }}s
          </p>
        </div>
      </div>
    </div>

    <!-- Not Gamehost / Not Found -->
    <div v-else-if="playScreenData && !isGamehost" class="flex items-center justify-center min-h-screen px-4 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
      <div class="bg-red-500/20 border-2 border-red-300 rounded-lg p-8 max-w-md text-center">
        <div class="text-6xl mb-4">🔒</div>
        <h2 class="text-2xl font-bold text-white mb-2">Access Denied</h2>
        <p class="text-red-100 mb-6">Only the gamehost can access this dashboard.</p>
        <NuxtLink 
          :to="shareLink"
          class="inline-block px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white rounded-lg hover:opacity-90 transition font-medium"
        >
          View Public Page
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
