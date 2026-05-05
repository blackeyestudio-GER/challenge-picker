<script setup lang="ts">
import type { CardDesignData as SharedCardDesignData, CardDesignsResponse } from '~/composables/useCardDesign'
import type { ActiveDesignSetResponse } from '~/generated/api-contracts'
import type {
  DashboardActiveRule as ActiveRule,
  DashboardPickStatus as PickStatus,
  DashboardQueueStatus as QueueStatus,
  DashboardResponse
} from '~/composables/usePlaythrough'

definePageMeta({
  layout: false // View screen has its own full-page design
})

const { playScreenData, loading, pickRule } = usePlaythrough()
const { user, getAuthHeader } = useAuth()
const { notifyApiError, success } = useNotify()
const route = useRoute()

type CardDesign = {
  identifier: string
  imageBase64: SharedCardDesignData['imageBase64']
  isTemplate: SharedCardDesignData['isTemplate']
}

interface ViewRuleConfig {
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

interface AvailableRule extends ViewRuleConfig {
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

const authRequired = ref(false)
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

// Active rules polling with client-side countdown
const activeRules = ref<Array<ActiveRule & { clientTimeRemaining?: number }>>([])

// Single unified polling interval
let dashboardPollInterval: number | null = null

// Loading state for active rules
const activeRulesLoading = ref(false)

// Backend pick status (for viewers to draw cards)
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

// Picking state
const pickingRule = ref(false)

// Available rules from playthrough configuration
const availableRules = computed(() => {
  if (!playScreenData.value?.configuration?.rules) return []
  
  const rules: AvailableRule[] = (playScreenData.value.configuration.rules as ViewRuleConfig[])
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

// Separate permanent and optional active rules
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

// Playthrough timer (formatted session time)
const sessionTimeFormatted = computed(() => {
  if (!playScreenData.value?.totalDuration) return '00:00:00'
  return formatDuration(playScreenData.value.totalDuration)
})

// Fetch all dashboard data in a single request
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
      // Update playthrough data
      if (response.data.playthrough) {
        playScreenData.value = response.data.playthrough
      }

      // Update active rules
      if (response.data.activeRules) {
        activeRules.value = response.data.activeRules.map(rule => ({
          ...rule,
          clientTimeRemaining: rule.timeRemaining
        }))
      }

      // Update pick status (for viewers to draw cards)
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

    if (!silent) {
      notifyApiError(err, 'Failed to refresh dashboard data')
    }
  } finally {
    if (!silent) {
      activeRulesLoading.value = false
    }
  }
}

// Fetch card designs
async function fetchCardDesigns() {
  if (!playScreenData.value?.configuration?.rules) return
  
  cardDesignsLoading.value = true
  try {
    const identifiers = Array.from(new Set(
      (playScreenData.value.configuration.rules as ViewRuleConfig[])
        .map(rule => rule.tarotCardIdentifier)
        .filter((identifier): identifier is string => Boolean(identifier))
    ))

    if (identifiers.length === 0) return

    // On view page, always use card visual mode (text shown below cards, not inside)
    // Fetch host's active design set (for icon support)
    if (playScreenData.value.userUuid) {
      try {
        const designSetResponse = await $fetch<ActiveDesignSetResponse>(`/api/users/${playScreenData.value.userUuid}/active-design-set`)
        if (designSetResponse.success && designSetResponse.data) {
          // Use icon setting from design set, but always disable text (shown below)
          designMode.value = {
            displayIcon: designSetResponse.data.displayIcon || false,
            displayText: false // Always false on view page - text shown below
          }
        } else {
          designMode.value = {
            displayIcon: false,
            displayText: false
          }
        }
      } catch {
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
      const response = await $fetch<CardDesignsResponse>('/api/design/card-designs', {
        method: 'GET',
        params: {
          identifiers: identifiers.join(',')
      }
    })

    if (response.success && response.data?.cardDesigns) {
      cardDesigns.value = Object.entries(response.data.cardDesigns).reduce<Record<string, CardDesign>>((acc, [identifier, design]) => {
        if (design) {
          acc[identifier] = {
            identifier,
            imageBase64: design.imageBase64,
            isTemplate: design.isTemplate
          }
        }
        return acc
      }, {})
    }
  } catch (err) {
    notifyApiError(err, 'Failed to load card designs')
  } finally {
    cardDesignsLoading.value = false
  }
}

// Load playthrough data
async function loadPlaythrough() {
  try {
    await fetchDashboardData()
    await fetchCardDesigns()

    // Start unified polling interval (5 seconds for real-time updates)
    if (!dashboardPollInterval) {
      dashboardPollInterval = setInterval(async () => {
        await fetchDashboardData(true)
      }, 5000) as unknown as number
    }
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to load playthrough')
  }
}

// Format duration helper
function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

// Pick random rule (for viewers)
async function pickRandomRule() {
  if (!playScreenData.value || pickingRule.value) return
  
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
      randomRule.difficultyLevel ?? 0,
      Boolean(user.value)
    )

    // Refresh dashboard data to get updated queue and pick status
    await fetchDashboardData(true)
    success('Card draw queued')
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to draw card')
  } finally {
    pickingRule.value = false
  }
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
})
</script>

<template>
  <div class="view-page min-h-screen">
    <!-- Auth Required -->
    <div v-if="authRequired" class="flex items-center justify-center min-h-screen p-6">
      <div class="view-page__auth-card rounded-2xl p-8 max-w-md w-full text-center border">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="view-page__auth-title text-2xl font-bold mb-4">Login Required</h1>
        <p class="view-page__auth-message mb-6">This playthrough requires you to be logged in to view.</p>
        <NuxtLink to="/login" class="btn-primary inline-block">
          Login to View
        </NuxtLink>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"/>
        <p class="view-page__loading-text">Loading playthrough...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen p-6">
      <div class="view-page__error-card rounded-2xl p-8 max-w-md w-full text-center border">
        <div class="text-6xl mb-4">😕</div>
        <h1 class="view-page__error-title text-2xl font-bold mb-4">Session Not Found</h1>
        <p class="view-page__error-message mb-6">{{ error.message || 'No active playthrough found' }}</p>
        <NuxtLink to="/" class="btn-secondary inline-block">
          Go to Homepage
        </NuxtLink>
      </div>
    </div>

    <!-- Main Viewer View -->
    <div v-else-if="playScreenData" class="min-h-screen p-2 md:p-6">
      <!-- Tab Navigation with Title (Compact) -->
      <div class="view-page__tab-bar flex items-center justify-between mb-3 border-b pb-2">
        <!-- Title Section (Left) -->
        <div class="flex-1 pr-4">
          <h1 class="view-page__tab-title text-base md:text-lg font-bold truncate">
            {{ playScreenData.gameTitle }}
          </h1>
          <p class="view-page__tab-subtitle text-xs truncate">{{ playScreenData.rulesetName }}</p>
        </div>

        <!-- Tab Buttons (Right) -->
        <div class="flex gap-1">
          <button
            :class="[
              'px-3 py-1.5 font-semibold transition-all text-xs md:text-sm whitespace-nowrap border-b-2',
              activeTab === 'dashboard'
                ? 'view-page__tab-button--active'
                : 'view-page__tab-button'
            ]"
            @click="activeTab = 'dashboard'"
          >
            📊 Dashboard
          </button>
          <button
            :class="[
              'px-3 py-1.5 font-semibold transition-all text-xs md:text-sm whitespace-nowrap border-b-2',
              activeTab === 'overview'
                ? 'view-page__tab-button--active'
                : 'view-page__tab-button'
            ]"
            @click="activeTab = 'overview'"
          >
            📋 Overview
          </button>
        </div>
      </div>

      <!-- DASHBOARD TAB (Viewer Mode - No Controls) -->
      <div v-show="activeTab === 'dashboard'" class="space-y-4">
        <!-- Mobile-First Dashboard Layout: 1/4 timer, 3/4 cards -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
          <!-- LEFT SIDE: Timer Display (1/4 width) -->
          <div class="space-y-4 lg:col-span-1">
            <!-- Session Timer -->
            <div class="view-page__card rounded-2xl p-6 border">
              <div class="text-center">
                <p class="view-page__section-subtitle text-sm mb-2">Session Time</p>
                <div class="view-page__timer-value text-5xl md:text-6xl font-mono font-bold mb-4">
                  {{ sessionTimeFormatted }}
                </div>
                <div class="flex items-center justify-center gap-2">
                  <span
:class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    playScreenData.status === 'active' ? 'view-page__status-badge--active' :
                    playScreenData.status === 'paused' ? 'view-page__status-badge--paused' :
                    'view-page__status-badge--setup'
                  ]">
                    {{ playScreenData.status === 'active' ? '▶ Playing' :
                       playScreenData.status === 'paused' ? '⏸ Paused' :
                       '⏹ Setup' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Viewer Info -->
            <div class="view-page__viewer-info rounded-2xl p-4 border">
              <div class="text-center">
                <p class="view-page__viewer-text text-sm font-medium">👀 Viewer Mode</p>
                <p class="view-page__viewer-subtext text-xs mt-1">Watching {{ playScreenData.gamehostUsername }}'s playthrough</p>
              </div>
            </div>

            <!-- Draw Random Card (Viewers can draw when run is active) -->
            <div class="view-page__card rounded-2xl p-4 border space-y-3">
              <!-- Draw Random Card Button -->
              <button
                :disabled="pickingRule || !pickStatus.canPick || playScreenData.status !== 'active'"
                class="w-full py-3 px-4 rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                :class="pickStatus.canPick && playScreenData.status === 'active'
                  ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white'
                  : 'bg-gray-700 text-gray-400 cursor-not-allowed'"
                @click="pickRandomRule"
              >
                <span v-if="pickingRule">⏳ Drawing...</span>
                <span v-else-if="pickStatus.rateLimitSeconds">⏱ {{ pickStatus.message }}</span>
                <span v-else-if="playScreenData.status !== 'active'">⏸ Run Not Started</span>
                <span v-else>🎴 Draw Random Card</span>
              </button>

              <!-- Queue Status -->
              <div class="view-page__queue-card rounded-lg p-3 border">
                <div class="view-page__queue-title text-center text-xs font-semibold mb-2">
                  📋 Card Queue ({{ queueStatus.queueLength }})
                </div>
                <div v-if="queueStatus.queueLength > 0" class="space-y-1">
                  <div 
                    v-for="rule in queueStatus.pendingRules.slice(0, 3)" 
                    :key="rule.ruleId"
                    class="view-page__queue-item text-xs flex justify-between items-center"
                  >
                    <span class="truncate flex-1">{{ rule.ruleName }}</span>
                    <span class="view-page__queue-eta ml-2 shrink-0">
                      <span v-if="rule.ruleType === 'legendary'" class="text-yellow-500">⭐</span>
                      <span v-else>~{{ rule.eta }}s</span>
                    </span>
                  </div>
                  <div v-if="queueStatus.queueLength > 3" class="view-page__queue-eta text-xs text-center mt-1">
                    +{{ queueStatus.queueLength - 3 }} more...
                  </div>
                </div>
                <div v-else class="view-page__queue-eta text-xs text-center italic">
                  Cards activate immediately when slots are available
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT SIDE: Active Rules (3/4 width) -->
          <div class="space-y-6 lg:col-span-3">
            <!-- Permanent Rules Row -->
            <div v-if="permanentActiveRules.length > 0" class="view-page__card rounded-2xl p-4 md:p-6 border">
              <h2 class="section-title flex items-center gap-2">
                <span class="text-2xl text-yellow-400">⭐</span>
                Permanent Rules
                <span v-if="activeRulesLoading" class="view-page__section-subtitle text-sm">(updating...)</span>
              </h2>
              
              <div class="flex flex-wrap gap-4 justify-start">
                <div
                  v-for="rule in permanentActiveRules"
                  :key="rule.id"
                  class="view-page__rule-card rounded-xl p-4 border transition-all flex flex-col items-center"
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
                  <h3 class="view-page__rule-name font-bold text-sm text-center mb-2 line-clamp-2">
                    {{ rule.ruleName }}
                  </h3>
                  
                  <!-- Always Active Badge -->
                  <div class="text-center">
                    <span class="view-page__rule-type-badge view-page__badge--default">
                      ★ Always Active
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Optional Rules Row -->
            <div v-if="optionalActiveRules.length > 0" class="view-page__card rounded-2xl p-4 md:p-6 border">
              <h2 class="section-title flex items-center gap-2">
                <span class="text-2xl text-cyan-400">⚡</span>
                Optional Rules
                <span v-if="activeRulesLoading" class="view-page__section-subtitle text-sm">(updating...)</span>
              </h2>
              
              <div class="flex flex-wrap gap-4 justify-start">
                <div
                  v-for="rule in optionalActiveRules"
                  :key="rule.id"
                  class="view-page__rule-card rounded-xl p-4 border transition-all flex flex-col items-center"
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
                      :is-default="false"
                      :can-toggle="false"
                      :pickrate="0"
                      :is-premium-design="rule.cardData ? !rule.cardData.isTemplate : false"
                      :display-icon="designMode.displayIcon"
                      :display-text="designMode.displayText"
                    />
                  </div>
                  
                  <!-- Rule Name -->
                  <h3 class="view-page__rule-name font-bold text-sm text-center mb-2 line-clamp-2">
                    {{ rule.ruleName }}
                  </h3>
                  
                  <!-- Timer -->
                  <div v-if="rule.type === 'time' || rule.type === 'hybrid'" class="view-page__rule-timer flex items-center gap-1 font-mono text-sm font-bold mt-1">
                    ⏱ {{ Math.max(0, rule.clientTimeRemaining || 0) }}s
                  </div>
                  
                  <!-- Counter -->
                  <div v-if="rule.type === 'counter' || rule.type === 'hybrid'" class="flex items-center gap-1 mt-1">
                    <span class="view-page__rule-counter font-mono text-sm font-bold">
                      🎯 {{ rule.currentAmount || 0 }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- No Active Rules -->
            <div v-if="enrichedActiveRules.length === 0" class="view-page__card rounded-2xl p-8 border text-center">
              <p class="view-page__section-subtitle text-lg">No active rules yet</p>
              <p class="view-page__section-subtitle text-sm mt-2">Waiting for gameplay to start...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- OVERVIEW TAB -->
      <div v-show="activeTab === 'overview'" class="space-y-6">
        <div class="view-page__card rounded-2xl p-4 md:p-6 border">
          <h2 class="section-title flex items-center gap-2">
            <span class="text-2xl">📋</span>
            Available Rules
          </h2>
          <p class="view-page__section-subtitle mb-6 text-sm md:text-base">All rules configured for this playthrough.</p>

          <!-- Available Rules - Card Left, Info Right - 3 Columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="rule in availableRules"
              :key="rule.id"
              class="view-page__available-rule-card rounded-xl p-4 border transition-all flex items-start gap-4"
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
                  <h3 class="view-page__available-rule-name font-bold text-base md:text-lg">
                    {{ rule.ruleName }}
                  </h3>
                  <!-- Rarity Badge -->
                  <span
class="view-page__rule-type-badge flex-shrink-0"
                    :class="rule.ruleType === 'legendary' ? 'view-page__rule-type-badge--legendary' :
                           rule.ruleType === 'court' ? 'view-page__rule-type-badge--court' :
                           rule.difficultyLevel !== null && rule.difficultyLevel <= 5 ? 'view-page__rule-type-badge--basic-common' :
                           'view-page__rule-type-badge--basic-magical'">
                    {{ rule.ruleType === 'legendary' ? '⭐ Legendary' :
                       rule.ruleType === 'court' ? '👑 Court' :
                       rule.difficultyLevel <= 5 ? '⚪ Common' : '🔵 Magical' }}
                  </span>
                </div>
                
                <!-- Rule Description -->
                <p v-if="rule.ruleDescription" class="view-page__available-rule-description text-sm mb-3 line-clamp-3">
                  {{ rule.ruleDescription }}
                </p>
                
                <!-- Rule Details -->
                <div class="flex flex-wrap items-center gap-3 text-sm view-page__available-rule-meta mb-2">
                  <span v-if="rule.difficultyLevel" class="flex items-center gap-1">
                    <span class="view-page__available-rule-meta-label">Difficulty:</span>
                    <span class="font-semibold">{{ rule.difficultyLevel }}</span>
                  </span>
                  <span v-if="rule.durationSeconds" class="flex items-center gap-1">
                    <span class="view-page__available-rule-meta-label">Duration:</span>
                    <span class="font-semibold">{{ formatDuration(rule.durationSeconds) }}</span>
                  </span>
                  <span v-if="rule.amount" class="flex items-center gap-1">
                    <span class="view-page__available-rule-meta-label">Amount:</span>
                    <span class="font-semibold">{{ rule.amount }}x</span>
                  </span>
                </div>
                
                <!-- Badges -->
                <div v-if="rule.isDefault" class="mt-2">
                  <span class="view-page__rule-type-badge view-page__badge--default">
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
</template>

<style scoped>
.btn-primary {
  @apply py-3 px-6 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-semibold transition-all shadow-lg hover:shadow-xl;
}

.btn-secondary {
  @apply py-3 px-6 rounded-xl bg-gray-700 hover:bg-gray-600 text-white font-semibold transition-all;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
