<script setup lang="ts">
import ChallengeSomeoneModal from '~/components/modal/ChallengeSomeoneModal.vue'
import { getApiErrorMessage } from '~/composables/useApiError'
import type {
  DashboardActiveRule as ActiveRule,
  DashboardPickStatus as PickStatus,
  DashboardQueueStatus as QueueStatus,
  DashboardResponse
} from '~/composables/usePlaythrough'

definePageMeta({
  layout: false // Play screen has its own full-page design
})

const { startPlaythrough, pausePlaythrough, resumePlaythrough, endPlaythrough, pickRule, playScreenData, loading } = usePlaythrough()
const { user, getAuthHeader } = useAuth()
const { notifyApiError, warning } = useNotify()
const route = useRoute()

interface CardDesign {
  imageBase64: string | null
  isTemplate: boolean
}

interface DesignSetResponse {
  displayIcon?: boolean
}

interface PlayRuleConfig {
  id: number
  ruleId?: number | null
  name?: string | null
  ruleName?: string | null
  description?: string | null
  ruleDescription?: string | null
  ruleType: string
  difficultyLevel: number | null
  durationSeconds: number | null
  amount: number | null
  tarotCardIdentifier: string | null
  iconIdentifier: string | null
  iconColor: string | null
  iconBrightness: number | null
  iconOpacity: number | null
  isDefault: boolean
  isEnabled?: boolean
}

interface AvailableRule extends PlayRuleConfig {
  ruleId: number
  ruleName: string
  ruleDescription: string | null
  cardImageBase64: string | null
  isTemplate: boolean
  isEnabled: boolean
}

interface ApiErrorData {
  error?: {
    code?: string
    message?: string
  }
}

interface ApiErrorLike {
  data?: ApiErrorData
  status?: number
  statusCode?: number
}

interface CardDesignResponse {
  success?: boolean
  data?: {
    cardDesigns?: Record<string, CardDesign>
  }
}

const actionLoading = ref(false)
const authRequired = ref(false)
const showStopModal = ref(false)
const shareButtonText = ref('Share with Viewers')
const showChallengeModal = ref(false)
const error = ref<{ message: string; code: string } | null>(null)

// Tab management (Dashboard vs Overview)
const activeTab = ref<'dashboard' | 'overview'>('dashboard')

// Card designs and display mode
const cardDesigns = ref<Record<string, CardDesign>>({})
const cardDesignsLoading = ref(false)
const designMode = ref<{
  displayIcon: boolean
  displayText: boolean
}>({
  displayIcon: false,
  displayText: false
})

// Host design set for viewers
const hostDesignSet = ref<DesignSetResponse | null>(null)

// Check if current user is the host (comes from backend)
const isHost = ref(false)

// Active rules polling with client-side countdown
const activeRules = ref<Array<ActiveRule & { clientTimeRemaining?: number }>>([])
const activeRulesLoading = ref(false)

// Backend pick status (replaces frontend state management)
const pickStatus = ref<PickStatus>({
  canPick: true,
  rateLimitSeconds: null,
  cooldownRuleIds: [],
  availableRulesCount: 0,
  message: 'Ready to draw'
})

// Queue status from backend
const queueStatus = ref<QueueStatus>({
  queueLength: 0,
  pendingRules: []
})

// Single unified polling interval (replaces 3 separate intervals)
let dashboardPollInterval: number | null = null

// Available rules from playthrough configuration
const availableRules = computed(() => {
  if (!playScreenData.value?.configuration?.rules) return []
  
  const rules: AvailableRule[] = (playScreenData.value.configuration.rules as PlayRuleConfig[])
    .filter(rule => rule.isEnabled !== false)
    .map((rule) => {
      const cardDesign = rule.tarotCardIdentifier ? cardDesigns.value[rule.tarotCardIdentifier] : null
      return {
        id: rule.id,
        ruleId: rule.ruleId || rule.id,
        ruleName: rule.ruleName || rule.name || '',
        ruleDescription: rule.ruleDescription || rule.description || null,
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
  
  // Group by base rule (ruleId), then sort groups by Rarity => Rule Type => Rule Name
  // Within each group, sort by difficulty level (descending - highest to lowest)
  
  // Helper functions
  const getRarityOrder = (rule: AvailableRule) => {
    if (rule.ruleType === 'legendary') return 1
    if (rule.ruleType === 'court') return 2
    if (rule.ruleType === 'basic') return 3
    return 4 // Unknown type
  }
  
  const getRuleTypeOrder = (rule: AvailableRule) => {
    if (rule.ruleType === 'legendary') return 0 // No sub-type
    if (rule.ruleType === 'court') return 0 // No sub-type
    if (rule.ruleType === 'basic') {
      // Basic Common (1-5) = 1, Basic Magical (6-10) = 2
      return (rule.difficultyLevel ?? 0) <= 5 ? 1 : 2
    }
    return 0
  }
  
  // Group rules by ruleId
  const groupedRules = new Map<number, AvailableRule[]>()
  rules.forEach(rule => {
    if (!groupedRules.has(rule.ruleId)) {
      groupedRules.set(rule.ruleId, [])
    }
    groupedRules.get(rule.ruleId)!.push(rule)
  })
  
  // Sort each group by difficulty (descending)
  groupedRules.forEach((group) => {
    group.sort((a, b) => b.difficultyLevel - a.difficultyLevel)
  })
  
  // Convert map to array and sort groups by: Rarity => Rule Type => Rule Name
  const sortedGroups = Array.from(groupedRules.values()).sort((groupA, groupB) => {
    const ruleA = groupA[0] // Use first rule in group for comparison
    const ruleB = groupB[0]
    
    // First sort by rarity
    const rarityA = getRarityOrder(ruleA)
    const rarityB = getRarityOrder(ruleB)
    if (rarityA !== rarityB) {
      return rarityA - rarityB
    }
    
    // Then sort by rule type (common vs magical for basic)
    const typeA = getRuleTypeOrder(ruleA)
    const typeB = getRuleTypeOrder(ruleB)
    if (typeA !== typeB) {
      return typeA - typeB
    }
    
    // Finally sort by rule name (alphabetically)
    return ruleA.ruleName.localeCompare(ruleB.ruleName)
  })
  
  // Flatten groups back to single array
  return sortedGroups.flat()
})

// Enrich active rules with card design data from availableRules
const enrichedActiveRules = computed(() => {
  return activeRules.value.map(activeRule => {
    const ruleConfig = availableRules.value.find(r => r.ruleId === activeRule.ruleId)
    
    // If rule config found, use it
    if (ruleConfig) {
      return {
        ...activeRule,
        cardData: ruleConfig
      }
    }
    
    // If not found but rule is legendary/permanent, try to find card design by ruleId
    // This handles cases where permanent rules are active but not in configuration
    if (activeRule.ruleType === 'legendary' || activeRule.type === 'permanent') {
      // Try to find any rule with same ruleId in availableRules (might be different difficulty)
      const anyRuleConfig = availableRules.value.find(r => r.ruleId === activeRule.ruleId)
      if (anyRuleConfig) {
        return {
          ...activeRule,
          cardData: anyRuleConfig
        }
      }
      
      // If still not found, create minimal cardData from active rule data
      // We'll need to fetch card design separately
      return {
        ...activeRule,
        cardData: {
          ruleId: activeRule.ruleId,
          ruleName: activeRule.ruleName,
          ruleType: activeRule.ruleType,
          difficultyLevel: null,
          durationSeconds: null,
          amount: null,
          tarotCardIdentifier: null, // Will need to fetch
          cardImageBase64: null,
          isTemplate: false,
          iconIdentifier: null,
          iconColor: null,
          iconBrightness: null,
          iconOpacity: null,
          isDefault: activeRule.ruleType === 'legendary',
          isEnabled: true
        }
      }
    }
    
    return {
      ...activeRule,
      cardData: null
    }
  })
})

// Separate permanent (legendary/default) and optional active rules
const permanentActiveRules = computed(() => {
  return enrichedActiveRules.value.filter(rule => 
    rule.ruleType === 'legendary' || rule.cardData?.isDefault === true
  )
})

const optionalActiveRules = computed(() => {
  return enrichedActiveRules.value.filter(rule => 
    rule.ruleType !== 'legendary' && rule.cardData?.isDefault !== true
  )
})

// Check if user can pick rules (host-only page)
const canPickRules = computed(() => {
  if (!playScreenData.value) return false
  return playScreenData.value.status === 'active'
})

// Picking state
const pickingRule = ref(false)

// Check if a rule is on cooldown (from backend)
const isRuleOnCooldown = (ruleId: number): boolean => {
  return pickStatus.value.cooldownRuleIds.includes(ruleId)
}

// Client-side playthrough timer (updates every second)
const clientElapsedSeconds = ref(0)
let sessionTimerInterval: number | null = null

// Calculate elapsed time from startedAt, accounting for paused time
const updateSessionTimer = () => {
  if (!playScreenData.value?.startedAt) {
    clientElapsedSeconds.value = 0
    return
  }

  const startTime = new Date(playScreenData.value.startedAt).getTime()
  const now = Date.now()
  const totalPausedDuration = (playScreenData.value.totalPausedDuration || 0) * 1000 // Convert seconds to ms
  
  // If paused, calculate elapsed time up to pausedAt
  if (playScreenData.value.status === 'paused' && playScreenData.value.pausedAt) {
    const pausedAt = new Date(playScreenData.value.pausedAt).getTime()
    const elapsed = Math.floor((pausedAt - startTime - totalPausedDuration) / 1000)
    clientElapsedSeconds.value = Math.max(0, elapsed)
  } else if (playScreenData.value.status === 'active') {
    // Active: calculate elapsed time excluding paused time
    const elapsed = Math.floor((now - startTime - totalPausedDuration) / 1000)
    clientElapsedSeconds.value = Math.max(0, elapsed)
  } else {
    // Setup or completed: use backend totalDuration if available, otherwise 0
    if (playScreenData.value.totalDuration) {
      clientElapsedSeconds.value = playScreenData.value.totalDuration
    } else {
      clientElapsedSeconds.value = 0
    }
  }
}

// Watch for status changes to start/stop timer
watch(() => playScreenData.value?.status, (status) => {
  if (sessionTimerInterval) {
    clearInterval(sessionTimerInterval)
    sessionTimerInterval = null
  }

  // Update immediately
  updateSessionTimer()

  // Run timer interval when active or paused (to handle resume correctly)
  if (status === 'active' || status === 'paused') {
    sessionTimerInterval = setInterval(updateSessionTimer, 1000) as unknown as number
  }
}, { immediate: true })

// Watch for startedAt changes to recalculate
watch(() => playScreenData.value?.startedAt, () => {
  updateSessionTimer()
}, { immediate: true })

// Watch for pausedAt and totalPausedDuration changes
watch(() => [playScreenData.value?.pausedAt, playScreenData.value?.totalPausedDuration], () => {
  updateSessionTimer()
}, { deep: true })

// Playthrough timer (formatted session time)
const sessionTimeFormatted = computed(() => {
  return formatDuration(clientElapsedSeconds.value)
})

// Fetch all dashboard data in a single request (batched endpoint)
async function fetchDashboardData(silent: boolean = false) {
  const uuid = route.params.uuid as string
  
  if (!silent) {
    activeRulesLoading.value = true
  }
  
  try {
    const response = await $fetch<DashboardResponse>(`/api/playthrough/${uuid}/dashboard`, {
      method: 'GET',
      headers: user.value ? getAuthHeader() : {}
    })

    if (response.success && response.data) {
      // Update playthrough data (for timer and status)
      if (response.data.playthrough) {
        playScreenData.value = response.data.playthrough
      }

      // Update isHost from backend (more reliable than frontend calculation)
      if (response.data.isHost !== undefined) {
        isHost.value = response.data.isHost
      }

      // Update active rules
      if (response.data.activeRules) {
        activeRules.value = response.data.activeRules.map(rule => ({
          ...rule,
          clientTimeRemaining: rule.timeRemaining
        }))
      }

      // Update pick status (if host)
      if (response.data.pickStatus) {
        pickStatus.value = response.data.pickStatus
      }

      // Update queue status
      if (response.data.queueStatus) {
        queueStatus.value = response.data.queueStatus
      }
    }
  } catch (err: unknown) {
    const apiError = err as ApiErrorLike

    // Check if auth is required
    if (apiError.data?.error?.code === 'AUTH_REQUIRED') {
      authRequired.value = true
      error.value = {
        message: apiError.data.error.message || 'Authentication required',
        code: apiError.data.error.code || 'AUTH_REQUIRED'
      }
      return
    }

    // Handle rate limiting (429) - stop polling temporarily
    if (apiError.status === 429 || apiError.statusCode === 429) {
      if (dashboardPollInterval) {
        clearInterval(dashboardPollInterval)
        dashboardPollInterval = null
      }
      // Restart polling after 10 seconds
      setTimeout(() => {
        if (!dashboardPollInterval) {
          dashboardPollInterval = setInterval(async () => {
            await fetchDashboardData(true)
          }, 5000) as unknown as number
        }
      }, 10000)
      return
    }

    // Silently fail on polling errors (unless not silent mode)
    if (!silent) {
      console.error('Error fetching dashboard data:', err)
    }
  } finally {
    if (!silent) {
      activeRulesLoading.value = false
    }
  }
}

// Pick a random rule (backend handles all validation)
async function pickRandomRule() {
  if (pickingRule.value || !canPickRules.value) return
  
  const uuid = route.params.uuid as string
  pickingRule.value = true
  try {
    // Get active permanent rule IDs to exclude from pool (prevent infinite redraw loops)
    const activePermanentRuleIds = new Set(
      activeRules.value
        .filter(r => r.ruleType === 'legendary')
        .map(r => r.ruleId)
    )
    
    // Get all non-default enabled rules
    // Exclude permanent rules that are already active (can't have same permanent rule twice)
    // Other rules can be picked - if already active, they'll wait in queue until the current instance expires
    const eligibleRules = availableRules.value.filter(rule => {
      if (rule.isDefault) return false
      
      // Exclude permanent rules that are already active
      if (rule.ruleType === 'legendary' && activePermanentRuleIds.has(rule.ruleId)) {
        return false
      }
      
      // Ensure rule has required fields
      if (!rule.ruleId || rule.difficultyLevel === undefined || rule.difficultyLevel === null) {
        return false
      }
      
      return true
    })
    
    if (eligibleRules.length === 0) {
      throw new Error('No rules available to pick')
    }
    
    // Pick a random rule
    const randomRule = eligibleRules[Math.floor(Math.random() * eligibleRules.length)]
    
    await pickRule(
      uuid,
      randomRule.ruleId,
      randomRule.difficultyLevel ?? 0
    )

    // Refresh dashboard data (playthrough, active rules, pick status, queue)
    await fetchDashboardData(true)
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to pick card')
  } finally {
    pickingRule.value = false
  }
}

// Decrement counter rule
const decrementingCounters = ref<Set<number>>(new Set())
async function decrementCounter(playthroughRuleId: number) {
  // Prevent multiple simultaneous decrements for the same counter
  if (decrementingCounters.value.has(playthroughRuleId)) {
    return
  }
  
  decrementingCounters.value.add(playthroughRuleId)
  
  try {
    const response = await $fetch(`/api/playthrough/rules/${playthroughRuleId}/decrement`, {
      method: 'POST',
      headers: getAuthHeader()
    })

    if (response.success) {
      await fetchDashboardData(true)
    }
  } catch (err: unknown) {
    const apiError = err as ApiErrorLike

    // Only log if it's not a validation error (counter already at 0, etc.)
    if (apiError.status !== 400 && apiError.statusCode !== 400) {
      console.error('Error decrementing counter:', err)
    }
    // Silently ignore validation errors (counter already at 0, rule not found, etc.)
  } finally {
    decrementingCounters.value.delete(playthroughRuleId)
  }
}

// Fetch card designs
async function fetchCardDesigns() {
  if (!playScreenData.value?.configuration?.rules) return
  
  cardDesignsLoading.value = true
  try {
    // Get unique tarot card identifiers from configuration
    const configIdentifiers = Array.from(new Set(
      (playScreenData.value.configuration.rules as PlayRuleConfig[])
        .map(rule => rule.tarotCardIdentifier)
        .filter((identifier): identifier is string => Boolean(identifier))
    ))
    
    // Also get identifiers from active rules (in case they're not in config)
    const activeRuleIdentifiers = Array.from(new Set(
      activeRules.value
        .map((rule) => {
          // Try to find tarotCardIdentifier from availableRules
          const ruleConfig = availableRules.value.find(ar => ar.ruleId === rule.ruleId)
          return ruleConfig?.tarotCardIdentifier || null
        })
        .filter((id: string | null) => id !== null)
    ))
    
    // Combine and deduplicate
    const identifiers = Array.from(new Set([...configIdentifiers, ...activeRuleIdentifiers]))

    if (identifiers.length === 0) {
      cardDesignsLoading.value = false
      return
    }

    // On play page, always use card visual mode (text shown below cards, not inside)
    // Fetch host's active design set if viewer (for icon support)
    if (!isHost.value && playScreenData.value?.userUuid) {
      try {
        const designSetResponse = await $fetch(`/api/users/${playScreenData.value.userUuid}/active-design-set`)
        if (designSetResponse.success && designSetResponse.data) {
          hostDesignSet.value = designSetResponse.data
          // Use icon setting from design set, but always disable text (shown below)
          designMode.value = {
            displayIcon: designSetResponse.data.displayIcon || false,
            displayText: false // Always false on play page - text shown below
          }
        } else {
          designMode.value = {
            displayIcon: false,
            displayText: false
          }
        }
      } catch {
        console.warn('Could not fetch host design set, using card visual mode')
        designMode.value = {
          displayIcon: false,
          displayText: false
        }
      }
    } else if (isHost.value && user.value) {
      // Fetch own design set (only if authenticated)
      try {
        const designSetResponse = await $fetch('/api/users/me/active-design-set', {
          headers: getAuthHeader()
        })
        if (designSetResponse.success && designSetResponse.data) {
          // Use icon setting from design set, but always disable text (shown below)
          designMode.value = {
            displayIcon: designSetResponse.data.displayIcon || false,
            displayText: false // Always false on play page - text shown below
          }
        } else {
          designMode.value = {
            displayIcon: false,
            displayText: false
          }
        }
      } catch {
        console.warn('Could not fetch user design set, using card visual mode')
        designMode.value = {
          displayIcon: false,
          displayText: false
        }
      }
    } else {
      // Fallback: use card visual mode (no text, icons/images only)
      designMode.value = {
        displayIcon: false,
        displayText: false
      }
    }

    // Fetch card designs
    // If viewing as non-host, use host's design set; otherwise use authenticated user's or default
    const params: { identifiers: string; userUuid?: string } = {
      identifiers: identifiers.join(',')
    }
    
    if (!isHost.value && playScreenData.value?.userUuid) {
      // Fetch host's design set for viewers
      params.userUuid = playScreenData.value.userUuid
    }
    
    try {
      const response = await $fetch<CardDesignResponse>('/api/design/card-designs', {
        method: 'GET',
        params
      })

      if (response?.success && response.data?.cardDesigns) {
        // Backend returns cardDesigns as an object with identifiers as keys
        cardDesigns.value = response.data.cardDesigns
      } else {
        // If response doesn't have cardDesigns, initialize empty object
        cardDesigns.value = {}
      }
    } catch (err: unknown) {
      console.error('Error fetching card designs:', err)
      // Initialize empty card designs on error (text-only mode)
      cardDesigns.value = {}
    }
  } catch (err: unknown) {
    console.error(getApiErrorMessage(err, 'Error in fetchCardDesigns'))
    cardDesigns.value = {}
  } finally {
    cardDesignsLoading.value = false
  }
}

// Load playthrough data
async function loadPlaythrough() {
  try {
    // Fetch initial dashboard data (includes playthrough, active rules, pick status)
    await fetchDashboardData()

    // Load card designs
    await fetchCardDesigns()

    // Start unified polling interval (5 seconds for real-time updates)
    if (!dashboardPollInterval) {
      dashboardPollInterval = setInterval(async () => {
        // Always poll, even when paused (timer needs to stay updated)
        await fetchDashboardData(true)
      }, 5000) as unknown as number
    }
  } catch (err: unknown) {
    // Error handling is done in fetchDashboardData
    console.error('Error loading playthrough:', err)
  }
}

// Start session
async function handleStart() {
  if (actionLoading.value) return
  const uuid = route.params.uuid as string
  actionLoading.value = true
  try {
    await startPlaythrough(uuid)
    await loadPlaythrough()
  } catch (err) {
    console.error('Error starting playthrough:', err)
  } finally {
    actionLoading.value = false
  }
}

// Pause session
async function handlePause() {
  if (actionLoading.value) return
  const uuid = route.params.uuid as string
  actionLoading.value = true
  try {
    await pausePlaythrough(uuid)
    await loadPlaythrough()
  } catch (err) {
    console.error('Error pausing playthrough:', err)
  } finally {
    actionLoading.value = false
  }
}

// Resume session
async function handleResume() {
  if (actionLoading.value) return
  const uuid = route.params.uuid as string
  actionLoading.value = true
  try {
    await resumePlaythrough(uuid)
    await loadPlaythrough()
  } catch (err) {
    console.error('Error resuming playthrough:', err)
  } finally {
    actionLoading.value = false
  }
}

// End session
async function handleEnd() {
  if (actionLoading.value) return
  const uuid = route.params.uuid as string
  showStopModal.value = false
  actionLoading.value = true
  try {
    const result = await endPlaythrough(uuid)
    if (result.deleted && result.message) {
      warning(result.message)
    }
    navigateTo('/my-runs')
  } catch (err) {
    console.error('Error ending playthrough:', err)
    actionLoading.value = false
  }
}

// Share link (shares viewer URL, not host URL)
function shareLink() {
  const uuid = route.params.uuid as string
  const viewerUrl = `${window.location.origin}/view/${uuid}`
  navigator.clipboard.writeText(viewerUrl)
  shareButtonText.value = '✓ Copied!'
  setTimeout(() => {
    shareButtonText.value = 'Share with Viewers'
  }, 2000)
}

// Format duration helper
function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

// Client-side countdown for active timed rules
let countdownInterval: number | null = null
function startClientCountdowns() {
  if (countdownInterval) clearInterval(countdownInterval)
  
  countdownInterval = setInterval(() => {
    if (playScreenData.value?.status !== 'active') return
    
    activeRules.value.forEach(rule => {
      if (rule.clientTimeRemaining !== null && rule.clientTimeRemaining > 0) {
        rule.clientTimeRemaining--
      }
    })
  }, 1000) as unknown as number
}

// Lifecycle
onMounted(async () => {
  await loadPlaythrough()
  startClientCountdowns()
})

onUnmounted(() => {
  if (dashboardPollInterval) clearInterval(dashboardPollInterval)
  if (countdownInterval) clearInterval(countdownInterval)
  if (sessionTimerInterval) clearInterval(sessionTimerInterval)
})
</script>

<template>
  <div class="play-page min-h-screen">
    <!-- Auth Required -->
    <div v-if="authRequired" class="flex items-center justify-center min-h-screen p-6">
      <div class="play-page__auth-card rounded-2xl p-8 max-w-md w-full text-center border">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="play-page__auth-title text-2xl font-bold mb-4">Login Required</h1>
        <p class="play-page__auth-message mb-6">This playthrough requires you to be logged in to view.</p>
        <NuxtLink to="/login" class="btn-primary inline-block">
          Login to View
        </NuxtLink>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"/>
        <p class="play-page__loading-text">Loading playthrough...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen p-6">
      <div class="play-page__error-card rounded-2xl p-8 max-w-md w-full text-center border">
        <div class="text-6xl mb-4">😕</div>
        <h1 class="play-page__error-title text-2xl font-bold mb-4">Session Not Found</h1>
        <p class="play-page__error-message mb-6">{{ error.message || 'No active playthrough found' }}</p>
        <NuxtLink to="/" class="btn-secondary inline-block">
          Go to Homepage
        </NuxtLink>
      </div>
    </div>

    <!-- Main Playthrough View -->
    <div v-else-if="playScreenData" class="min-h-screen p-2 md:p-6">
      <!-- Tab Navigation with Title (Compact) -->
      <div class="play-page__tab-bar flex items-center justify-between mb-3 border-b pb-2">
        <!-- Title Section (Left) -->
        <div class="flex-1 pr-4">
          <h1 class="play-page__tab-title text-base md:text-lg font-bold truncate">
            {{ playScreenData.gameTitle }}
          </h1>
          <p class="play-page__tab-subtitle text-xs truncate">{{ playScreenData.rulesetName }}</p>
        </div>

        <!-- Tab Buttons (Right) -->
        <div class="flex gap-1">
          <button
            :class="[
              'px-3 py-1.5 font-semibold transition-all text-xs md:text-sm whitespace-nowrap border-b-2',
              activeTab === 'dashboard'
                ? 'play-page__tab-button--active'
                : 'play-page__tab-button'
            ]"
            @click="activeTab = 'dashboard'"
          >
            📊 Dashboard
          </button>
          <button
            :class="[
              'px-3 py-1.5 font-semibold transition-all text-xs md:text-sm whitespace-nowrap border-b-2',
              activeTab === 'overview'
                ? 'play-page__tab-button--active'
                : 'play-page__tab-button'
            ]"
            @click="activeTab = 'overview'"
          >
            📋 Overview
          </button>
        </div>
      </div>

      <!-- DASHBOARD TAB -->
      <div v-show="activeTab === 'dashboard'" class="space-y-4">
        <!-- Mobile-First Dashboard Layout: 1/4 controls, 3/4 cards -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
          <!-- LEFT SIDE: Controls & Timer (1/4 width) -->
          <div class="space-y-4 lg:col-span-1">
            <!-- Session Timer (Big Display) -->
            <div class="play-page__card rounded-2xl p-6 border">
              <div class="text-center">
                <p class="play-page__timer-label text-sm mb-2">Session Time</p>
                <div class="play-page__timer-value text-5xl md:text-6xl font-mono font-bold mb-4">
                  {{ sessionTimeFormatted }}
                </div>
                <div class="flex items-center justify-center gap-2">
                  <span
:class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    playScreenData.status === 'active' ? 'play-page__status-badge--active' :
                    playScreenData.status === 'paused' ? 'play-page__status-badge--paused' :
                    'play-page__status-badge--setup'
                  ]">
                    {{ playScreenData.status === 'active' ? '▶ Playing' :
                       playScreenData.status === 'paused' ? '⏸ Paused' :
                       '⏹ Setup' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Control Buttons (Cassette Style - Big Buttons) -->
            <div class="play-page__card rounded-2xl p-6 border">
              <div class="flex items-center justify-center gap-3">
                <!-- Play/Pause -->
                <button
                  v-if="playScreenData.status === 'setup'"
                  :disabled="actionLoading"
                  class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold text-3xl md:text-4xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="handleStart"
                >
                  ▶
                </button>
                <button
                  v-else-if="playScreenData.status === 'active'"
                  :disabled="actionLoading"
                  class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-br from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold text-3xl md:text-4xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="handlePause"
                >
                  ⏸
                </button>
                <button
                  v-else-if="playScreenData.status === 'paused'"
                  :disabled="actionLoading"
                  class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold text-3xl md:text-4xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="handleResume"
                >
                  ▶
                </button>

                <!-- Stop -->
                <button
                  :disabled="actionLoading || playScreenData.status === 'setup'"
                  class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold text-3xl md:text-4xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="showStopModal = true"
                >
                  ⏹
                </button>
              </div>
            </div>

            <!-- Action Buttons (Random Card, Share, Challenge) -->
            <div class="play-page__card rounded-2xl p-4 border space-y-3">
              <!-- Share Button -->
              <button
                class="w-full py-3 md:py-3 px-4 md:px-6 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold text-sm md:text-base transition-all shadow-lg hover:shadow-xl min-h-[44px]"
                @click="shareLink"
              >
                {{ shareButtonText }}
              </button>

              <!-- Challenge Button -->
              <button
                class="play-page__challenge-button w-full py-3 md:py-3 px-4 md:px-6 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold text-sm md:text-base transition-all shadow-lg hover:shadow-xl min-h-[44px]"
                @click="showChallengeModal = true"
              >
                ⚔️ Challenge Someone
              </button>

              <!-- View Comparison Button (only show if host) -->
              <NuxtLink
                v-if="isHost && playScreenData"
                :to="`/challenges/comparison/${playScreenData.uuid}`"
                class="w-full py-3 md:py-3 px-4 md:px-6 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold text-sm md:text-base transition-all shadow-lg hover:shadow-xl min-h-[44px] flex items-center justify-center"
              >
                📊 View Comparison
              </NuxtLink>

              <!-- Draw Random Card (Always visible, at bottom) - Host Only -->
              <button
                v-if="isHost"
                :disabled="pickingRule || !pickStatus.canPick || !canPickRules"
                class="w-full py-4 md:py-4 px-4 md:px-6 rounded-xl font-bold text-base md:text-lg transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed min-h-[52px] md:min-h-[60px]"
                :class="pickStatus.canPick && canPickRules
                  ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white'
                  : 'bg-gray-700 text-gray-400 cursor-not-allowed'"
                @click="pickRandomRule"
              >
                <span v-if="pickingRule">⏳ Drawing...</span>
                <span v-else-if="pickStatus.rateLimitSeconds">⏱ {{ pickStatus.message }}</span>
                <span v-else>🎴 Draw Random Card</span>
              </button>

              <!-- Pick Status Info (Host Only) -->
              <div v-if="isHost && pickStatus.cooldownRuleIds.length > 0" class="text-center text-sm play-page__card-subtitle">
                {{ pickStatus.cooldownRuleIds.length }} rule{{ pickStatus.cooldownRuleIds.length > 1 ? 's' : '' }} on cooldown
              </div>

              <!-- Queue Status (Host Only) -->
              <div v-if="isHost" class="play-page__queue-card rounded-lg p-3 border">
                <div class="play-page__queue-title text-center text-sm font-semibold mb-2">
                  📋 Card Queue ({{ queueStatus.queueLength }})
                </div>
                <div v-if="queueStatus.queueLength > 0" class="space-y-1">
                  <div 
                    v-for="rule in queueStatus.pendingRules.slice(0, 3)" 
                    :key="rule.ruleId"
                    class="play-page__queue-item text-xs flex justify-between items-center"
                  >
                    <span class="truncate flex-1">{{ rule.ruleName }}</span>
                    <span class="play-page__queue-meta ml-2 shrink-0">
                      <span v-if="rule.ruleType === 'legendary'" class="text-yellow-500">⭐</span>
                      <span v-else>~{{ rule.eta }}s</span>
                    </span>
                  </div>
                  <div v-if="queueStatus.queueLength > 3" class="play-page__queue-empty text-xs text-center mt-1">
                    +{{ queueStatus.queueLength - 3 }} more...
                  </div>
                </div>
                <div v-else class="play-page__queue-empty text-xs text-center italic">
                  Cards activate immediately when slots are available
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT SIDE: Active Rules (3/4 width) -->
          <div class="space-y-6 lg:col-span-3">
            <!-- Permanent Rules Row -->
            <div v-if="permanentActiveRules.length > 0" class="play-page__card rounded-2xl p-4 md:p-6 border">
              <h2 class="section-title flex items-center gap-2">
                <span class="text-2xl">⭐</span>
                Permanent Rules
                <span v-if="activeRulesLoading" class="play-page__section-subtitle text-sm">(updating...)</span>
              </h2>
              
              <div class="flex flex-wrap gap-4 justify-start">
                <div
                  v-for="rule in permanentActiveRules"
                  :key="rule.id"
                  class="play-page__rule-card rounded-xl p-4 border transition-all flex flex-col items-center"
                >
                  <!-- Card Design (Fixed Tarot Card Size: 140px x 240px) -->
                  <div v-if="rule.cardData || rule.ruleId" class="mb-3 flex justify-center items-start" style="width: fit-content; margin: 0 auto;">
                    <RuleCard
                        :rule-id="rule.cardData?.ruleId || rule.ruleId"
                        :rule-name="rule.cardData?.ruleName || rule.ruleName"
                        :rule-type="rule.cardData?.ruleType || rule.ruleType"
                        :rule-description="null"
                        :difficulty-level="rule.cardData?.difficultyLevel || null"
                        :duration-seconds="rule.cardData?.durationSeconds || null"
                        :amount="rule.cardData?.amount || null"
                        :tarot-card-identifier="rule.cardData?.tarotCardIdentifier || null"
                        :card-image-base64="rule.cardData?.cardImageBase64 || null"
                        :icon-identifier="rule.cardData?.iconIdentifier || null"
                        :icon-color="rule.cardData?.iconColor || null"
                        :icon-brightness="rule.cardData?.iconBrightness || null"
                        :icon-opacity="rule.cardData?.iconOpacity || null"
                        :is-enabled="true"
                        :is-default="rule.cardData?.isDefault || (rule.ruleType === 'legendary')"
                        :can-toggle="false"
                        :pickrate="0"
                        :is-premium-design="rule.cardData ? !rule.cardData.isTemplate : false"
                        :display-icon="designMode.displayIcon"
                        :display-text="designMode.displayText"
                      />
                  </div>
                  
                  <!-- Rule Name -->
                  <h3 class="play-page__rule-name font-bold text-sm text-center mb-2 line-clamp-2">
                    {{ rule.ruleName }}
                  </h3>
                  
                  <!-- Always Active Badge -->
                  <div class="text-center">
                    <span class="play-page__rule-type-badge play-page__badge--default">
                      ★ Always Active
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Optional Rules Row -->
            <div v-if="optionalActiveRules.length > 0" class="play-page__card rounded-2xl p-4 md:p-6 border">
              <h2 class="section-title flex items-center gap-2">
                <span class="text-2xl">⚡</span>
                Optional Rules
                <span v-if="activeRulesLoading" class="play-page__section-subtitle text-sm">(updating...)</span>
              </h2>
              
              <div class="flex flex-wrap gap-4 justify-start">
                <div
                  v-for="rule in optionalActiveRules"
                  :key="rule.id"
                  class="play-page__rule-card rounded-xl p-4 border transition-all flex flex-col items-center"
                >
                  <!-- Card Design (Fixed Tarot Card Size: 140px x 240px) -->
                  <div v-if="rule.cardData" class="mb-3 flex justify-center items-start" style="width: fit-content; margin: 0 auto;">
                    <RuleCard
                      :rule-id="rule.cardData.ruleId"
                      :rule-name="rule.cardData.ruleName"
                      :rule-type="rule.cardData.ruleType"
                      :rule-description="null"
                      :difficulty-level="rule.cardData.difficultyLevel"
                      :duration-seconds="rule.cardData.durationSeconds"
                      :amount="rule.cardData.amount"
                      :tarot-card-identifier="rule.cardData.tarotCardIdentifier"
                      :card-image-base64="rule.cardData.cardImageBase64"
                      :icon-identifier="rule.cardData.iconIdentifier"
                      :icon-color="rule.cardData.iconColor"
                      :icon-brightness="rule.cardData.iconBrightness"
                      :icon-opacity="rule.cardData.iconOpacity"
                      :is-enabled="true"
                      :is-default="false"
                      :can-toggle="false"
                      :pickrate="0"
                      :is-premium-design="!rule.cardData.isTemplate"
                      :display-icon="designMode.displayIcon"
                      :display-text="designMode.displayText"
                    />
                  </div>
                  
                  <!-- Rule Name -->
                  <h3 class="play-page__rule-name font-bold text-sm text-center mb-3 line-clamp-2">
                    {{ rule.ruleName }}
                  </h3>
                  
                  <!-- Timer -->
                  <div v-if="rule.type === 'time' || rule.type === 'hybrid'" class="text-center mb-2">
                    <div class="play-page__rule-timer font-mono text-2xl font-bold">
                      ⏱ {{ Math.max(0, rule.clientTimeRemaining || 0) }}s
                    </div>
                  </div>

                  <!-- Counter -->
                  <div v-if="rule.type === 'counter' || rule.type === 'hybrid'" class="text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                      <span class="play-page__rule-counter font-mono text-2xl font-bold">
                        🎯 {{ rule.currentAmount || 0 }}
                      </span>
                      <button
                        v-if="isHost && rule.currentAmount > 0"
                        class="px-3 py-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg font-bold transition-colors text-sm"
                        @click="decrementCounter(rule.id)"
                      >
                        -1
                      </button>
                    </div>
                    <span v-if="rule.currentAmount === 0" class="play-page__rule-completed text-xs italic block">
                      (Completed)
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- No Active Rules -->
            <div v-if="enrichedActiveRules.length === 0" class="play-page__card rounded-2xl p-8 border text-center">
              <p class="play-page__empty-message text-lg">No active rules yet</p>
              <p class="play-page__empty-hint text-sm mt-2">
                {{ playScreenData.status === 'setup' ? 'Start the session to activate default rules' : 'Draw a card to add rules' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- OVERVIEW TAB -->
      <div v-show="activeTab === 'overview'" class="space-y-6">
        <div class="play-page__card rounded-2xl p-4 md:p-6 border">
          <h2 class="section-title flex items-center gap-2">
            <span class="text-2xl">📋</span>
            Available Rules
          </h2>
          <p class="play-page__section-subtitle mb-6 text-sm md:text-base">All rules configured for this playthrough. Default rules are always active.</p>

          <!-- Available Rules - Card Left, Info Right - 3 Columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="rule in availableRules"
              :key="rule.id"
              class="play-page__available-rule-card rounded-xl p-4 border transition-all flex items-start gap-4"
              :class="isRuleOnCooldown(rule.ruleId) ? 'play-page__available-rule-card--cooldown' : ''"
            >
              <!-- Card Design (Left) -->
              <div class="flex-shrink-0">
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
                  :is-default="rule.isDefault || false"
                  :can-toggle="false"
                  :pickrate="0"
                  :is-premium-design="!rule.isTemplate"
                  :display-icon="designMode.displayIcon"
                  :display-text="designMode.displayText"
                />
              </div>
              
              <!-- Rule Info (Right) -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                  <h3 class="play-page__available-rule-name font-bold text-base md:text-lg">
                    {{ rule.ruleName }}
                  </h3>
                  <!-- Rarity Badge -->
                  <span
class="play-page__rule-type-badge flex-shrink-0"
                    :class="rule.ruleType === 'legendary' ? 'play-page__rule-type-badge--legendary' :
                           rule.ruleType === 'court' ? 'play-page__rule-type-badge--court' :
                           rule.difficultyLevel !== null && rule.difficultyLevel <= 5 ? 'play-page__rule-type-badge--basic-common' :
                           'play-page__rule-type-badge--basic-magical'">
                    {{ rule.ruleType === 'legendary' ? '⭐ Legendary' :
                       rule.ruleType === 'court' ? '👑 Court' :
                       rule.difficultyLevel <= 5 ? '⚪ Common' : '🔵 Magical' }}
                  </span>
                </div>
                
                <!-- Rule Description -->
                <p v-if="rule.ruleDescription" class="play-page__available-rule-description text-sm mb-3 line-clamp-3">
                  {{ rule.ruleDescription }}
                </p>
                
                <!-- Rule Details -->
                <div class="flex flex-wrap items-center gap-3 text-sm play-page__available-rule-meta mb-2">
                  <span v-if="rule.difficultyLevel" class="flex items-center gap-1">
                    <span>Difficulty:</span>
                    <span class="font-semibold">{{ rule.difficultyLevel }}</span>
                  </span>
                  <span v-if="rule.durationSeconds" class="flex items-center gap-1">
                    <span>Duration:</span>
                    <span class="font-semibold">{{ formatDuration(rule.durationSeconds) }}</span>
                  </span>
                  <span v-if="rule.amount" class="flex items-center gap-1">
                    <span>Amount:</span>
                    <span class="font-semibold">{{ rule.amount }}x</span>
                  </span>
                </div>
                
                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <div v-if="isRuleOnCooldown(rule.ruleId)" class="text-xs text-yellow-400 flex items-center gap-1">
                    ⏳ On Cooldown
                  </div>
                  <div v-if="rule.isDefault">
                    <span class="play-page__rule-type-badge play-page__badge--default">
                      ★ Default Rule
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stop Confirmation Modal -->
    <div v-if="showStopModal" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50" @click.self="showStopModal = false">
      <div class="play-page__card rounded-2xl p-6 max-w-md w-full border border-red-500">
        <h2 class="play-page__card-title text-2xl font-bold mb-4">⚠️ End Playthrough?</h2>
        <p class="play-page__card-subtitle mb-6">
          Are you sure you want to end this playthrough? This action cannot be undone and you won't be able to restart it.
        </p>
        <div class="flex gap-3">
          <button
            class="flex-1 py-3 px-6 rounded-xl btn-secondary font-semibold transition-all"
            @click="showStopModal = false"
          >
            Cancel
          </button>
          <button
            :disabled="actionLoading"
            class="flex-1 py-3 px-6 rounded-xl btn-danger font-bold transition-all disabled:opacity-50"
            @click="handleEnd"
          >
            End Playthrough
          </button>
        </div>
      </div>
    </div>

    <!-- Challenge Modal -->
    <ChallengeSomeoneModal
      v-if="playScreenData"
      :show="showChallengeModal"
      :playthrough-uuid="playScreenData.uuid"
      @close="showChallengeModal = false"
    />
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
