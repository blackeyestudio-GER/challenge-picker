<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const playthroughUuid = route.params.uuid as string

const { fetchPlaythrough, toggleRule, updateMaxConcurrent, startPlaythrough, currentPlaythrough, loading, error } = usePlaythrough()

let pollInterval: NodeJS.Timeout | null = null

onMounted(async () => {
  await fetchPlaythrough(playthroughUuid)
  
  // Poll for updates every 3 seconds (in case status changes externally)
  pollInterval = setInterval(async () => {
    try {
      await fetchPlaythrough(playthroughUuid)
    } catch (err) {
      // Silently fail on polling errors
      console.error('Polling error:', err)
    }
  }, 3000)
})

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval)
  }
})

const maxConcurrent = ref(3)
const updatingMax = ref(false)

watchEffect(() => {
  if (currentPlaythrough.value) {
    maxConcurrent.value = currentPlaythrough.value.maxConcurrentRules
  }
})

const activeRules = computed(() => 
  currentPlaythrough.value?.rules.filter(r => r.isActive) || []
)

const toggleRuleStatus = async (ruleId: number) => {
  try {
    await toggleRule(playthroughUuid, ruleId)
  } catch (err) {
    console.error('Failed to toggle rule:', err)
  }
}

const updateMax = async () => {
  if (!currentPlaythrough.value) return
  
  updatingMax.value = true
  try {
    await updateMaxConcurrent(playthroughUuid, maxConcurrent.value)
  } catch (err) {
    console.error('Failed to update max concurrent:', err)
  } finally {
    updatingMax.value = false
  }
}

const starting = ref(false)

const startSession = async () => {
  if (!currentPlaythrough.value) return
  
  starting.value = true
  
  try {
    await startPlaythrough(playthroughUuid)
    // Redirect to the public play screen
    router.push(`/play/${currentPlaythrough.value.uuid}`)
  } catch (err) {
    console.error('Failed to start playthrough:', err)
    alert('Failed to start session. Please try again.')
  } finally {
    starting.value = false
  }
}

const cancel = () => {
  // TODO: Delete/cancel the playthrough
  router.push('/dashboard')
}

// Shareable link
const config = useRuntimeConfig()
const shareableLink = computed(() => {
  if (!currentPlaythrough.value) return ''
  return `${window.location.origin}/play/${currentPlaythrough.value.uuid}`
})

const copyLink = async () => {
  if (!shareableLink.value) return
  
  try {
    await navigator.clipboard.writeText(shareableLink.value)
    alert('Link copied to clipboard!')
  } catch (err) {
    console.error('Failed to copy link:', err)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 py-8 px-4">
    <div class="max-w-5xl mx-auto">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
        <p class="text-white mt-4">Loading...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/20 border border-red-300 rounded-lg p-6 text-center">
        <p class="text-white">{{ error }}</p>
        <button
          @click="router.push('/dashboard')"
          class="mt-4 px-6 py-2 bg-white text-purple-600 rounded-lg hover:bg-gray-100"
        >
          Back to Dashboard
        </button>
      </div>

      <!-- Configuration Screen -->
      <div v-else-if="currentPlaythrough">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-3xl font-bold text-gray-800">{{ currentPlaythrough.gameName }}</h1>
              <p class="text-gray-600">{{ currentPlaythrough.rulesetName }}</p>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-500">Session ID</div>
              <div class="text-xs font-mono text-gray-400">{{ currentPlaythrough.uuid }}</div>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
              Setup Phase
            </span>
            <span class="text-gray-600 text-sm">
              {{ activeRules.length }} / {{ currentPlaythrough.rules.length }} rules active
            </span>
          </div>
        </div>

        <!-- Shareable Link -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
          <h2 class="text-xl font-bold text-gray-800 mb-3">Share with Viewers</h2>
          <p class="text-gray-600 text-sm mb-4">
            Share this link so viewers can watch your session. They can join now and will see the game start when you're ready!
          </p>
          
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              type="text"
              :value="shareableLink"
              readonly
              class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg font-mono text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
            <button
              @click="copyLink"
              class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium whitespace-nowrap"
            >
              📋 Copy Link
            </button>
            <NuxtLink
              :to="`/play/${currentPlaythrough.uuid}`"
              target="_blank"
              class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium text-center whitespace-nowrap"
            >
              👁️ Preview
            </NuxtLink>
          </div>
        </div>

        <!-- Max Concurrent Rules -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Max Concurrent Rules</h2>
          <p class="text-gray-600 text-sm mb-4">
            How many rules should be displayed at once during gameplay?
          </p>
          
          <div class="flex items-center space-x-4">
            <input
              type="range"
              v-model.number="maxConcurrent"
              min="1"
              max="10"
              class="flex-1 h-2 bg-purple-200 rounded-lg appearance-none cursor-pointer"
            />
            <div class="flex items-center space-x-2">
              <span class="text-2xl font-bold text-purple-600">{{ maxConcurrent }}</span>
              <button
                @click="updateMax"
                :disabled="updatingMax || maxConcurrent === currentPlaythrough.maxConcurrentRules"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
              >
                {{ updatingMax ? 'Updating...' : 'Update' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Rules List -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Rules Configuration</h2>
          <p class="text-gray-600 text-sm mb-4">
            Toggle rules on/off. Only active rules will appear during gameplay.
          </p>

          <div class="space-y-2">
            <button
              v-for="rule in currentPlaythrough.rules"
              :key="rule.id"
              @click="toggleRuleStatus(rule.ruleId)"
              :class="[
                'w-full flex items-center justify-between p-4 rounded-lg border-2 transition-all',
                rule.isActive 
                  ? 'border-purple-300 bg-purple-50 hover:bg-purple-100' 
                  : 'border-gray-200 bg-gray-50 hover:bg-gray-100'
              ]"
            >
              <div class="flex items-center space-x-4">
                <div :class="[
                  'w-6 h-6 rounded-full flex items-center justify-center transition-all',
                  rule.isActive ? 'bg-purple-600' : 'bg-gray-300'
                ]">
                  <span v-if="rule.isActive" class="text-white text-sm">✓</span>
                </div>
                <div class="text-left">
                  <div :class="[
                    'font-medium',
                    rule.isActive ? 'text-gray-800' : 'text-gray-400'
                  ]">
                    {{ rule.text }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ rule.durationMinutes }} minute{{ rule.durationMinutes !== 1 ? 's' : '' }}
                  </div>
                </div>
              </div>
              <div :class="[
                'text-sm font-medium',
                rule.isActive ? 'text-purple-600' : 'text-gray-400'
              ]">
                {{ rule.isActive ? 'Active' : 'Disabled' }}
              </div>
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
          <button
            @click="cancel"
            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="startSession"
            :disabled="activeRules.length === 0 || starting"
            class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-bold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ starting ? 'Starting...' : 'Start Game →' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

