<script setup lang="ts">
// No auth middleware - this is a public page!
const route = useRoute()
const uuid = route.params.uuid as string

const { fetchPlayScreen, startPlayScreenPolling, pausePlaythrough, resumePlaythrough, endPlaythrough, playScreenData, loading, error } = usePlaythrough()
const { user } = useAuth()

const actionLoading = ref(false)
let stopPolling: (() => void) | null = null

// Session timer (for active sessions)
const elapsedSeconds = ref(0)
let timerInterval: NodeJS.Timeout | null = null

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

// Fetch play screen data on mount and start polling
onMounted(async () => {
  await fetchPlayScreen(uuid)
  
  // Start polling every 2 seconds
  stopPolling = startPlayScreenPolling(uuid, 2000)
})

// Cleanup on unmount
onUnmounted(() => {
  if (stopPolling) {
    stopPolling()
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

// Gamehost control functions
const handlePause = async () => {
  if (!playScreenData.value || actionLoading.value) return
  
  actionLoading.value = true
  try {
    await pausePlaythrough(uuid)
    await fetchPlayScreen(uuid)
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
    await resumePlaythrough(uuid)
    await fetchPlayScreen(uuid)
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
    await endPlaythrough(uuid)
    await fetchPlayScreen(uuid)
  } catch (err) {
    console.error('Failed to end:', err)
    alert('Failed to end session')
  } finally {
    actionLoading.value = false
  }
}

// Status display helpers
const statusDisplay = computed(() => {
  if (!playScreenData.value) return { text: '', color: '', icon: '' }
  
  switch (playScreenData.value.status) {
    case 'setup':
      return { 
        text: 'Setting Up', 
        color: 'bg-yellow-500',
        icon: '⚙️',
        message: 'Gamehost is configuring the session...'
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
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-white"></div>
        <p class="text-white mt-4 text-lg">Loading session...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-red-500/20 border-2 border-red-300 rounded-lg p-8 max-w-md text-center">
        <div class="text-6xl mb-4">😕</div>
        <h2 class="text-2xl font-bold text-white mb-2">Session Not Found</h2>
        <p class="text-red-100 mb-6">{{ error }}</p>
        <NuxtLink 
          to="/"
          class="inline-block px-6 py-3 bg-white text-purple-600 rounded-lg hover:bg-gray-100 transition font-medium"
        >
          Go to Homepage
        </NuxtLink>
      </div>
    </div>

    <!-- Play Screen -->
    <div v-else-if="playScreenData" class="container mx-auto px-4 py-8 max-w-6xl">
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
              <p class="text-white/60 text-sm">Hosted by {{ playScreenData.gamehostUsername }}</p>
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

      <!-- Setup Phase - Waiting Screen -->
      <div v-if="playScreenData.status === 'setup'" class="bg-white/10 backdrop-blur-md rounded-2xl p-12 shadow-2xl border border-white/20 text-center">
        <div class="max-w-2xl mx-auto">
          <div class="text-8xl mb-6 animate-pulse">⏳</div>
          <h2 class="text-4xl font-bold text-white mb-4">{{ statusDisplay.message }}</h2>
          <p class="text-white/80 text-lg mb-8">
            The gamehost is setting up the session. The game will start soon!
          </p>
          
          <!-- Session Stats -->
          <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white/10 rounded-lg p-4">
              <div class="text-3xl font-bold text-white">{{ playScreenData.totalRulesCount }}</div>
              <div class="text-white/60 text-sm">Total Rules</div>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
              <div class="text-3xl font-bold text-white">{{ playScreenData.activeRulesCount }}</div>
              <div class="text-white/60 text-sm">Active Rules</div>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
              <div class="text-3xl font-bold text-white">{{ playScreenData.maxConcurrentRules }}</div>
              <div class="text-white/60 text-sm">Max Concurrent</div>
            </div>
          </div>

          <p class="text-white/60 text-sm">
            Share this link with others to watch together!
          </p>
        </div>
      </div>

      <!-- Active/Paused/Completed Phase -->
      <div v-else class="bg-white/10 backdrop-blur-md rounded-2xl p-12 shadow-2xl border border-white/20 text-center">
        <div class="text-6xl mb-4">{{ statusDisplay.icon }}</div>
        <h2 class="text-3xl font-bold text-white mb-4">{{ statusDisplay.message }}</h2>
        
        <!-- Active/Paused Stats -->
        <div v-if="playScreenData.status === 'active' || playScreenData.status === 'paused'" class="mb-8">
          <p class="text-white/80 mb-4">
            Active gameplay view with rules and timers coming soon...
          </p>
          <div class="grid grid-cols-2 gap-4 max-w-md mx-auto">
            <div class="bg-white/10 rounded-lg p-4">
              <div class="text-2xl font-bold text-white">{{ playScreenData.completedRulesCount }}</div>
              <div class="text-white/60 text-sm">Completed</div>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
              <div class="text-2xl font-bold text-white">{{ playScreenData.activeRulesCount }}</div>
              <div class="text-white/60 text-sm">Remaining</div>
            </div>
          </div>
        </div>

        <!-- Completed Stats -->
        <div v-if="playScreenData.status === 'completed' && playScreenData.totalDuration" class="mt-6">
          <p class="text-white/80">
            Duration: {{ Math.floor(playScreenData.totalDuration / 60) }}m {{ playScreenData.totalDuration % 60 }}s
          </p>
        </div>
      </div>

      <!-- Gamehost Controls -->
      <div v-if="isGamehost" class="mt-6">
        <!-- Setup Phase Controls -->
        <div v-if="playScreenData.status === 'setup'" class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-white">
              <p class="font-medium">You are the gamehost</p>
              <p class="text-sm text-white/60">Go to the setup page to configure and start the session</p>
            </div>
            <NuxtLink
              :to="`/playthrough/${playScreenData.id}/setup`"
              class="px-6 py-3 bg-white text-purple-600 rounded-lg hover:bg-gray-100 transition font-medium whitespace-nowrap"
            >
              Go to Setup →
            </NuxtLink>
          </div>
        </div>

        <!-- Active Phase Controls -->
        <div v-else-if="playScreenData.status === 'active'" class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
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
        </div>

        <!-- Paused Phase Controls -->
        <div v-else-if="playScreenData.status === 'paused'" class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
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
        </div>
      </div>
    </div>
  </div>
</template>

