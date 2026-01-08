<template>
  <div class="min-h-screen" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-purple-500 mx-auto mb-4"></div>
        <p class="text-white/60">Loading comparison...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-900/20 border border-red-700 rounded-lg p-8 text-center">
        <h2 class="text-2xl font-bold text-white mb-2">Error</h2>
        <p class="text-gray-300 mb-6">{{ error }}</p>
        <NuxtLink to="/" class="inline-block px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
          Go to Homepage
        </NuxtLink>
      </div>

      <!-- Comparison View -->
      <div v-else-if="comparisonData" class="space-y-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Challenge Comparison</h1>
          <p class="text-xl text-gray-300">
            {{ comparisonData.gameName }} - {{ comparisonData.rulesetName }}
          </p>
          <p class="text-gray-400 mt-2">
            Compare your playthrough with {{ comparisonData.participants.length }} participant{{ comparisonData.participants.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <!-- Comparison Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          <!-- Source Playthrough (Challenger) -->
          <div class="bg-white/5 backdrop-blur-md rounded-xl border-2 border-purple-500/50 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="text-yellow-400">👑</span>
                {{ comparisonData.sourceUsername }}
              </h2>
              <span class="px-3 py-1 bg-purple-500/30 text-purple-300 rounded-full text-sm font-semibold">
                Challenger
              </span>
            </div>
            
            <!-- Duration -->
            <div class="mb-4">
              <p class="text-gray-400 text-sm mb-1">Duration</p>
              <p class="text-2xl font-bold text-white">
                {{ formatDuration(comparisonData.sourceDuration) }}
              </p>
            </div>

            <!-- Active Rules Count -->
            <div class="mb-4">
              <p class="text-gray-400 text-sm mb-1">Rules Activated</p>
              <p class="text-xl font-semibold text-white">
                {{ comparisonData.sourceActiveRules.length }} rule{{ comparisonData.sourceActiveRules.length !== 1 ? 's' : '' }}
              </p>
            </div>

            <!-- Active Rules List -->
            <div class="mt-4">
              <p class="text-gray-400 text-sm mb-2 font-semibold">Rules:</p>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="rule in comparisonData.sourceActiveRules"
                  :key="rule.ruleId"
                  class="bg-black/30 rounded-lg p-2 text-sm"
                  :class="rule.completed ? 'opacity-60' : ''"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-white font-medium">{{ rule.ruleName }}</span>
                    <span
                      v-if="rule.completed"
                      class="text-green-400 text-xs"
                    >
                      ✓ Completed
                    </span>
                    <span
                      v-else-if="rule.isActive"
                      class="text-yellow-400 text-xs"
                    >
                      Active
                    </span>
                  </div>
                  <div class="text-gray-400 text-xs mt-1">
                    {{ rule.ruleType }} • Difficulty {{ rule.difficultyLevel }}
                    <span v-if="rule.currentAmount !== null">
                      • {{ rule.currentAmount }}x
                    </span>
                  </div>
                </div>
                <div v-if="comparisonData.sourceActiveRules.length === 0" class="text-gray-500 text-sm italic">
                  No rules activated yet
                </div>
              </div>
            </div>

            <!-- View Playthrough Link -->
            <NuxtLink
              :to="`/play/${comparisonData.sourcePlaythroughUuid}`"
              class="mt-4 block w-full text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
            >
              View Playthrough
            </NuxtLink>
          </div>

          <!-- Participants -->
          <div
            v-for="participant in comparisonData.participants"
            :key="participant.username"
            class="bg-white/5 backdrop-blur-md rounded-xl border p-6"
            :class="participant.status === 'accepted' ? 'border-green-500/50' : participant.status === 'pending' ? 'border-yellow-500/50' : 'border-gray-500/50'"
          >
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-2xl font-bold text-white">
                {{ participant.username }}
              </h2>
              <span
                class="px-3 py-1 rounded-full text-sm font-semibold"
                :class="{
                  'bg-green-500/30 text-green-300': participant.status === 'accepted',
                  'bg-yellow-500/30 text-yellow-300': participant.status === 'pending',
                  'bg-red-500/30 text-red-300': participant.status === 'declined'
                }"
              >
                {{ participant.status === 'accepted' ? 'Accepted' : participant.status === 'pending' ? 'Pending' : 'Declined' }}
              </span>
            </div>

            <!-- Duration -->
            <div class="mb-4">
              <p class="text-gray-400 text-sm mb-1">Duration</p>
              <p class="text-2xl font-bold text-white">
                {{ participant.duration !== null ? formatDuration(participant.duration) : 'Not started' }}
              </p>
            </div>

            <!-- Active Rules Count -->
            <div class="mb-4">
              <p class="text-gray-400 text-sm mb-1">Rules Activated</p>
              <p class="text-xl font-semibold text-white">
                {{ participant.activeRules.length }} rule{{ participant.activeRules.length !== 1 ? 's' : '' }}
              </p>
            </div>

            <!-- Active Rules List -->
            <div v-if="participant.status === 'accepted'" class="mt-4">
              <p class="text-gray-400 text-sm mb-2 font-semibold">Rules:</p>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="rule in participant.activeRules"
                  :key="rule.ruleId"
                  class="bg-black/30 rounded-lg p-2 text-sm"
                  :class="rule.completed ? 'opacity-60' : ''"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-white font-medium">{{ rule.ruleName }}</span>
                    <span
                      v-if="rule.completed"
                      class="text-green-400 text-xs"
                    >
                      ✓ Completed
                    </span>
                    <span
                      v-else-if="rule.isActive"
                      class="text-yellow-400 text-xs"
                    >
                      Active
                    </span>
                  </div>
                  <div class="text-gray-400 text-xs mt-1">
                    {{ rule.ruleType }} • Difficulty {{ rule.difficultyLevel }}
                    <span v-if="rule.currentAmount !== null">
                      • {{ rule.currentAmount }}x
                    </span>
                  </div>
                </div>
                <div v-if="participant.activeRules.length === 0" class="text-gray-500 text-sm italic">
                  No rules activated yet
                </div>
              </div>
            </div>

            <!-- View Playthrough Link (only if accepted) -->
            <NuxtLink
              v-if="participant.status === 'accepted' && participant.playthroughUuid"
              :to="`/play/${participant.playthroughUuid}`"
              class="mt-4 block w-full text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
            >
              View Playthrough
            </NuxtLink>
            <div
              v-else-if="participant.status === 'pending'"
              class="mt-4 text-center text-gray-400 text-sm"
            >
              Waiting for response...
            </div>
            <div
              v-else-if="participant.status === 'declined'"
              class="mt-4 text-center text-red-400 text-sm"
            >
              Challenge declined
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6 mt-8">
          <h3 class="text-xl font-bold text-white mb-4">Summary Statistics</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-black/30 rounded-lg p-4">
              <p class="text-gray-400 text-sm mb-1">Total Participants</p>
              <p class="text-3xl font-bold text-white">
                {{ comparisonData.participants.length + 1 }}
              </p>
            </div>
            <div class="bg-black/30 rounded-lg p-4">
              <p class="text-gray-400 text-sm mb-1">Accepted Challenges</p>
              <p class="text-3xl font-bold text-green-400">
                {{ acceptedCount }}
              </p>
            </div>
            <div class="bg-black/30 rounded-lg p-4">
              <p class="text-gray-400 text-sm mb-1">Average Duration</p>
              <p class="text-3xl font-bold text-white">
                {{ formatDuration(averageDuration) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const { getAuthHeader } = useAuth()

const playthroughUuid = computed(() => route.params.playthroughUuid as string)

const comparisonData = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const acceptedCount = computed(() => {
  if (!comparisonData.value) return 0
  return comparisonData.value.participants.filter((p: any) => p.status === 'accepted').length
})

const averageDuration = computed(() => {
  if (!comparisonData.value) return null
  
  const durations = [
    comparisonData.value.sourceDuration,
    ...comparisonData.value.participants
      .filter((p: any) => p.status === 'accepted' && p.duration !== null)
      .map((p: any) => p.duration)
  ].filter((d: any) => d !== null && d !== undefined)
  
  if (durations.length === 0) return null
  
  const sum = durations.reduce((a: number, b: number) => a + b, 0)
  return Math.round(sum / durations.length)
})

const formatDuration = (seconds: number | null | undefined): string => {
  if (seconds === null || seconds === undefined) return 'N/A'
  
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  if (hours > 0) {
    return `${hours}h ${minutes}m ${secs}s`
  } else if (minutes > 0) {
    return `${minutes}m ${secs}s`
  } else {
    return `${secs}s`
  }
}

const fetchComparison = async () => {
  loading.value = true
  error.value = null

  try {
    const config = useRuntimeConfig()
    const response = await $fetch<{
      success: boolean
      data?: any
      error?: { code: string; message: string }
    }>(`${config.public.apiBase}/challenges/comparison/${playthroughUuid.value}`, {
      headers: getAuthHeader()
    })

    if (response.success && response.data) {
      comparisonData.value = response.data
    } else {
      error.value = response.error?.message || 'Failed to load comparison'
    }
  } catch (err: any) {
    error.value = err.data?.error?.message || 'Failed to load comparison'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchComparison()
})
</script>

