<template>
  <div class="challenge-page">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-purple-500 mx-auto mb-4"/>
        <p class="challenge-page__loading-text">Loading comparison...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="challenge-page__error-card border rounded-lg p-8 text-center">
        <h2 class="challenge-page__error-title text-2xl font-bold mb-2">Error</h2>
        <p class="challenge-page__error-message mb-6">{{ error }}</p>
        <NuxtLink to="/" class="inline-block px-6 py-3 challenge-page__button-secondary rounded-lg transition">
          Go to Homepage
        </NuxtLink>
      </div>

      <!-- Comparison View -->
      <div v-else-if="comparisonData" class="space-y-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="challenge-page__header-title text-4xl font-bold mb-2">Challenge Comparison</h1>
          <p class="challenge-page__header-subtitle text-xl">
            {{ comparisonData.gameName }} - {{ comparisonData.rulesetName }}
          </p>
          <p class="challenge-page__header-meta mt-2">
            Compare your playthrough with {{ comparisonData.participants.length }} participant{{ comparisonData.participants.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <!-- Comparison Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          <!-- Source Playthrough (Challenger) -->
          <div class="challenge-page__card rounded-xl border-2 border-theme-accent p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="challenge-page__card-title text-2xl font-bold flex items-center gap-2">
                <span class="text-yellow-400">👑</span>
                {{ comparisonData.sourceUsername }}
              </h2>
              <span class="challenge-page__card-badge px-3 py-1 rounded-full text-sm font-semibold border">
                Challenger
              </span>
            </div>
            
            <!-- Duration -->
            <div class="mb-4">
              <p class="challenge-page__stat-label text-sm mb-1">Duration</p>
              <p class="challenge-page__stat-value text-2xl font-bold">
                {{ formatDuration(comparisonData.sourceDuration) }}
              </p>
            </div>

            <!-- Active Rules Count -->
            <div class="mb-4">
              <p class="challenge-page__stat-label text-sm mb-1">Rules Activated</p>
              <p class="challenge-page__stat-value text-xl font-semibold">
                {{ comparisonData.sourceActiveRules.length }} rule{{ comparisonData.sourceActiveRules.length !== 1 ? 's' : '' }}
              </p>
            </div>

            <!-- Active Rules List -->
            <div class="mt-4">
              <p class="challenge-page__stat-label text-sm mb-2 font-semibold">Rules:</p>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="rule in comparisonData.sourceActiveRules"
                  :key="rule.ruleId"
                  class="challenge-page__rule-card rounded-lg p-2 text-sm"
                  :class="rule.completed ? 'opacity-60' : ''"
                >
                  <div class="flex items-center justify-between">
                    <span class="challenge-page__rule-name font-medium">{{ rule.ruleName }}</span>
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
                  <div class="challenge-page__rule-meta text-xs mt-1">
                    {{ rule.ruleType }} • Difficulty {{ rule.difficultyLevel }}
                    <span v-if="rule.currentAmount !== null">
                      • {{ rule.currentAmount }}x
                    </span>
                  </div>
                </div>
                <div v-if="comparisonData.sourceActiveRules.length === 0" class="challenge-page__empty-text text-sm italic">
                  No rules activated yet
                </div>
              </div>
            </div>

            <!-- View Playthrough Link -->
            <NuxtLink
              :to="`/play/${comparisonData.sourcePlaythroughUuid}`"
              class="mt-4 block w-full text-center px-4 py-2 challenge-page__button-secondary rounded-lg transition"
            >
              View Playthrough
            </NuxtLink>
          </div>

          <!-- Participants -->
          <div
            v-for="participant in comparisonData.participants"
            :key="participant.username"
            class="challenge-page__card rounded-xl border p-6"
            :class="participant.status === 'accepted' ? 'challenge-page__card challenge-page__status-accepted' : participant.status === 'pending' ? 'challenge-page__card challenge-page__status-pending' : 'border-theme-primary'"
          >
            <div class="flex items-center justify-between mb-4">
              <h2 class="challenge-page__card-title text-2xl font-bold">
                {{ participant.username }}
              </h2>
              <span
                class="px-3 py-1 rounded-full text-sm font-semibold border"
                :class="{
                  'challenge-page__status-accepted': participant.status === 'accepted',
                  'challenge-page__status-pending': participant.status === 'pending',
                  'challenge-page__status-declined': participant.status === 'declined'
                }"
              >
                {{ participant.status === 'accepted' ? 'Accepted' : participant.status === 'pending' ? 'Pending' : 'Declined' }}
              </span>
            </div>

            <!-- Duration -->
            <div class="mb-4">
              <p class="challenge-page__stat-label text-sm mb-1">Duration</p>
              <p class="challenge-page__stat-value text-2xl font-bold">
                {{ participant.duration !== null ? formatDuration(participant.duration) : 'Not started' }}
              </p>
            </div>

            <!-- Active Rules Count -->
            <div class="mb-4">
              <p class="challenge-page__stat-label text-sm mb-1">Rules Activated</p>
              <p class="challenge-page__stat-value text-xl font-semibold">
                {{ participant.activeRules.length }} rule{{ participant.activeRules.length !== 1 ? 's' : '' }}
              </p>
            </div>

            <!-- Active Rules List -->
            <div v-if="participant.status === 'accepted'" class="mt-4">
              <p class="challenge-page__stat-label text-sm mb-2 font-semibold">Rules:</p>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="rule in participant.activeRules"
                  :key="rule.ruleId"
                  class="challenge-page__rule-card rounded-lg p-2 text-sm"
                  :class="rule.completed ? 'opacity-60' : ''"
                >
                  <div class="flex items-center justify-between">
                    <span class="challenge-page__rule-name font-medium">{{ rule.ruleName }}</span>
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
                  <div class="challenge-page__rule-meta text-xs mt-1">
                    {{ rule.ruleType }} • Difficulty {{ rule.difficultyLevel }}
                    <span v-if="rule.currentAmount !== null">
                      • {{ rule.currentAmount }}x
                    </span>
                  </div>
                </div>
                <div v-if="participant.activeRules.length === 0" class="challenge-page__empty-text text-sm italic">
                  No rules activated yet
                </div>
              </div>
            </div>

            <!-- View Playthrough Link (only if accepted) -->
            <NuxtLink
              v-if="participant.status === 'accepted' && participant.playthroughUuid"
              :to="`/play/${participant.playthroughUuid}`"
              class="mt-4 block w-full text-center px-4 py-2 btn-success rounded-lg transition"
            >
              View Playthrough
            </NuxtLink>
            <div
              v-else-if="participant.status === 'pending'"
              class="mt-4 text-center challenge-page__stat-label text-sm"
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
        <div class="challenge-page__summary-card rounded-xl border p-6 mt-8">
          <h3 class="challenge-page__summary-title text-xl font-bold mb-4">Summary Statistics</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="challenge-page__rule-card rounded-lg p-4">
              <p class="challenge-page__summary-stat-label text-sm mb-1">Total Participants</p>
              <p class="challenge-page__summary-stat-value text-3xl font-bold">
                {{ comparisonData.participants.length + 1 }}
              </p>
            </div>
            <div class="challenge-page__rule-card rounded-lg p-4">
              <p class="challenge-page__summary-stat-label text-sm mb-1">Accepted Challenges</p>
              <p class="text-3xl font-bold text-green-400">
                {{ acceptedCount }}
              </p>
            </div>
            <div class="challenge-page__rule-card rounded-lg p-4">
              <p class="challenge-page__summary-stat-label text-sm mb-1">Average Duration</p>
              <p class="challenge-page__summary-stat-value text-3xl font-bold">
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
import type { ChallengeComparisonData, ChallengeComparisonResponse } from '~/composables/useChallenges'
import { getApiErrorMessage } from '~/composables/useApiError'

definePageMeta({
  middleware: 'auth'
})

interface ComparisonParticipant {
  status: string
  duration: number | null
}

interface ChallengeComparisonData {
  sourceDuration: number | null
  participants: ComparisonParticipant[]
}

const route = useRoute()
const { getAuthHeader } = useAuth()

const playthroughUuid = computed(() => route.params.playthroughUuid as string)

const comparisonData = ref<ChallengeComparisonData | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const acceptedCount = computed(() => {
  if (!comparisonData.value) return 0
  return comparisonData.value.participants.filter((p) => p.status === 'accepted').length
})

const averageDuration = computed(() => {
  if (!comparisonData.value) return null
  
  const durations = [
    comparisonData.value.sourceDuration,
    ...comparisonData.value.participants
      .filter((p) => p.status === 'accepted' && p.duration !== null)
      .map((p) => p.duration)
  ].filter((d): d is number => d !== null && d !== undefined)
  
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
    const response = await $fetch<ChallengeComparisonResponse>(`${config.public.apiBase}/challenges/comparison/${playthroughUuid.value}`, {
      headers: getAuthHeader()
    })

    if (response.success && response.data) {
      comparisonData.value = response.data
    } else {
      error.value = response.error?.message || 'Failed to load comparison'
    }
  } catch (err: unknown) {
    error.value = getApiErrorMessage(err, 'Failed to load comparison')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchComparison()
})
</script>
