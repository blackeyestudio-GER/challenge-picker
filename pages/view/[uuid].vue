<script setup lang="ts">
definePageMeta({
  layout: false // View screen has its own full-page design
})

const { fetchPlayScreen, playScreenData, loading } = usePlaythrough()
const { user, getAuthHeader } = useAuth()
const route = useRoute()

const authRequired = ref(false)
const error = ref<{ message: string; code: string } | null>(null)

// Tab management (Dashboard vs Overview)
const activeTab = ref<'dashboard' | 'overview'>('dashboard')

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
  clientTimeRemaining?: number
}>>([])

// Single unified polling interval
let dashboardPollInterval: number | null = null

// Loading state for active rules
const activeRulesLoading = ref(false)

// Backend pick status (for viewers to draw cards)
const pickStatus = ref<{
  canPick: boolean
  rateLimitSeconds: number | null
  cooldownRuleIds: number[]
  availableRulesCount: number
  message: string
}>({
  canPick: true,
  rateLimitSeconds: null,
  cooldownRuleIds: [],
  availableRulesCount: 0,
  message: 'Ready to draw'
})

// Queue status from backend
const queueStatus = ref<{
  queueLength: number
  pendingRules: Array<{
    ruleId: number
    ruleName: string
    ruleType: string
    eta: number | null
  }>
}>({
  queueLength: 0,
  pendingRules: []
})

// Picking state
const pickingRule = ref(false)

// Available rules from playthrough configuration
const availableRules = computed(() => {
  if (!playScreenData.value?.configuration?.rules) return []
  
  const rules = playScreenData.value.configuration.rules
    .filter((rule: any) => rule.isEnabled !== false)
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
  
  // Group by base rule (ruleId), then sort groups by Rarity => Rule Type => Rule Name
  // Within each group, sort by difficulty level (descending - highest to lowest)
  
  // Helper functions
  const getRarityOrder = (rule: any) => {
    if (rule.ruleType === 'legendary') return 1
    if (rule.ruleType === 'court') return 2
    if (rule.ruleType === 'basic') return 3
    return 4 // Unknown type
  }
  
  const getRuleTypeOrder = (rule: any) => {
    if (rule.ruleType === 'legendary') return 0 // No sub-type
    if (rule.ruleType === 'court') return 0 // No sub-type
    if (rule.ruleType === 'basic') {
      // Basic Common (1-5) = 1, Basic Magical (6-10) = 2
      return rule.difficultyLevel <= 5 ? 1 : 2
    }
    return 0
  }
  
  // Group rules by ruleId
  const groupedRules = new Map<number, any[]>()
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

// Enrich active rules with card design data
const enrichedActiveRules = computed(() => {
  return activeRules.value.map(activeRule => {
    const ruleConfig = availableRules.value.find(r => r.ruleId === activeRule.ruleId)
    return {
      ...activeRule,
      cardData: ruleConfig || null
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
    const response = await $fetch(`/api/playthrough/${uuid}/dashboard`, {
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
        activeRules.value = response.data.activeRules.map((rule: any) => ({
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
  } catch (err: any) {
    // Check if auth is required
    if (err?.data?.error?.code === 'AUTH_REQUIRED') {
      authRequired.value = true
      error.value = { message: err.data.error.message, code: err.data.error.code }
      return
    }

    if (!silent) {
      console.error('Error fetching dashboard data:', err)
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
      playScreenData.value.configuration.rules
        .filter((r: any) => r.tarotCardIdentifier)
        .map((r: any) => r.tarotCardIdentifier)
    ))

    if (identifiers.length === 0) return

    // On view page, always use card visual mode (text shown below cards, not inside)
    // Fetch host's active design set (for icon support)
    if (playScreenData.value.userUuid) {
      try {
        const designSetResponse = await $fetch(`/api/users/${playScreenData.value.userUuid}/active-design-set`)
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
      } catch (err) {
        console.warn('Could not fetch host design set, using card visual mode')
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
    const response = await $fetch('/api/design/card-designs', {
      method: 'GET',
      params: {
        identifiers: identifiers.join(',')
      }
    })

    if (response.success && response.data) {
      cardDesigns.value = response.data.reduce((acc: any, design: any) => {
        acc[design.identifier] = design
        return acc
      }, {})
    }
  } catch (err) {
    console.error('Error fetching card designs:', err)
  } finally {
    cardDesignsLoading.value = false
  }
}

// Load playthrough data
async function loadPlaythrough() {
  try {
    await fetchDashboardData()
    await fetchCardDesigns()

    // Start unified polling interval
    if (!dashboardPollInterval) {
      dashboardPollInterval = setInterval(async () => {
        await fetchDashboardData(true)
      }, 1000) as unknown as number
    }
  } catch (err: any) {
    console.error('Error loading playthrough:', err)
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
    const response = await $fetch(`/api/playthroughs/${uuid}/pick-rule`, {
      method: 'POST',
      headers: user.value ? getAuthHeader() : {}
    })
    
    if (response.success) {
      // Refresh dashboard data to get updated queue and pick status
      await fetchDashboardData(true)
    } else {
      console.error('Failed to pick rule:', response.error)
    }
  } catch (err: any) {
    console.error('Error picking rule:', err)
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
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Auth Required -->
    <div v-if="authRequired" class="flex items-center justify-center min-h-screen p-6">
      <div class="bg-gray-800 rounded-2xl p-8 max-w-md w-full text-center border border-gray-700">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold text-white mb-4">Login Required</h1>
        <p class="text-gray-400 mb-6">This playthrough requires you to be logged in to view.</p>
        <NuxtLink to="/login" class="btn-primary inline-block">
          Login to View
        </NuxtLink>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-cyan-500 mx-auto mb-4"></div>
        <p class="text-gray-400">Loading playthrough...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen p-6">
      <div class="bg-gray-800 rounded-2xl p-8 max-w-md w-full text-center border border-red-500">
        <div class="text-6xl mb-4">😕</div>
        <h1 class="text-2xl font-bold text-white mb-4">Session Not Found</h1>
        <p class="text-gray-400 mb-6">{{ error.message || 'No active playthrough found' }}</p>
        <NuxtLink to="/" class="btn-secondary inline-block">
          Go to Homepage
        </NuxtLink>
      </div>
    </div>

    <!-- Main Viewer View -->
    <div v-else-if="playScreenData" class="min-h-screen p-2 md:p-6">
      <!-- Tab Navigation with Title (Compact) -->
      <div class="flex items-center justify-between mb-3 border-b border-gray-700 pb-2">
        <!-- Title Section (Left) -->
        <div class="flex-1 pr-4">
          <h1 class="text-base md:text-lg font-bold text-white truncate">
            {{ playScreenData.gameTitle }}
          </h1>
          <p class="text-gray-400 text-xs truncate">{{ playScreenData.rulesetName }}</p>
        </div>

        <!-- Tab Buttons (Right) -->
        <div class="flex gap-1">
          <button
            @click="activeTab = 'dashboard'"
            :class="[
              'px-3 py-1.5 font-semibold transition-all text-xs md:text-sm whitespace-nowrap',
              activeTab === 'dashboard'
                ? 'text-cyan-400 border-b-2 border-cyan-400'
                : 'text-gray-400 hover:text-white'
            ]"
          >
            📊 Dashboard
          </button>
          <button
            @click="activeTab = 'overview'"
            :class="[
              'px-3 py-1.5 font-semibold transition-all text-xs md:text-sm whitespace-nowrap',
              activeTab === 'overview'
                ? 'text-cyan-400 border-b-2 border-cyan-400'
                : 'text-gray-400 hover:text-white'
            ]"
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
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-6 border border-gray-700">
              <div class="text-center">
                <p class="text-gray-400 text-sm mb-2">Session Time</p>
                <div class="text-5xl md:text-6xl font-mono font-bold text-cyan-400 mb-4">
                  {{ sessionTimeFormatted }}
                </div>
                <div class="flex items-center justify-center gap-2">
                  <span :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    playScreenData.status === 'active' ? 'bg-green-500/20 text-green-400' :
                    playScreenData.status === 'paused' ? 'bg-yellow-500/20 text-yellow-400' :
                    'bg-gray-500/20 text-gray-400'
                  ]">
                    {{ playScreenData.status === 'active' ? '▶ Playing' :
                       playScreenData.status === 'paused' ? '⏸ Paused' :
                       '⏹ Setup' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Viewer Info -->
            <div class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-2xl p-4 border border-blue-500/30">
              <div class="text-center">
                <p class="text-blue-400 text-sm font-medium">👀 Viewer Mode</p>
                <p class="text-gray-400 text-xs mt-1">Watching {{ playScreenData.gamehostUsername }}'s playthrough</p>
              </div>
            </div>

            <!-- Draw Random Card (Viewers can draw when run is active) -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-4 border border-gray-700 space-y-3">
              <!-- Draw Random Card Button -->
              <button
                @click="pickRandomRule"
                :disabled="pickingRule || !pickStatus.canPick || playScreenData.status !== 'active'"
                class="w-full py-3 px-4 rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                :class="pickStatus.canPick && playScreenData.status === 'active'
                  ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white'
                  : 'bg-gray-700 text-gray-400 cursor-not-allowed'"
              >
                <span v-if="pickingRule">⏳ Drawing...</span>
                <span v-else-if="pickStatus.rateLimitSeconds">⏱ {{ pickStatus.message }}</span>
                <span v-else-if="playScreenData.status !== 'active'">⏸ Run Not Started</span>
                <span v-else>🎴 Draw Random Card</span>
              </button>

              <!-- Queue Status -->
              <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <div class="text-center text-xs font-semibold text-purple-400 mb-2">
                  📋 Card Queue ({{ queueStatus.queueLength }})
                </div>
                <div v-if="queueStatus.queueLength > 0" class="space-y-1">
                  <div 
                    v-for="rule in queueStatus.pendingRules.slice(0, 3)" 
                    :key="rule.ruleId"
                    class="text-xs text-gray-300 flex justify-between items-center"
                  >
                    <span class="truncate flex-1">{{ rule.ruleName }}</span>
                    <span class="text-gray-400 ml-2 shrink-0">
                      <span v-if="rule.ruleType === 'legendary'" class="text-yellow-500">⭐</span>
                      <span v-else>~{{ rule.eta }}s</span>
                    </span>
                  </div>
                  <div v-if="queueStatus.queueLength > 3" class="text-xs text-gray-500 text-center mt-1">
                    +{{ queueStatus.queueLength - 3 }} more...
                  </div>
                </div>
                <div v-else class="text-xs text-gray-500 text-center italic">
                  Cards activate immediately when slots are available
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT SIDE: Active Rules (3/4 width) -->
          <div class="space-y-6 lg:col-span-3">
            <!-- Permanent Rules Row -->
            <div v-if="permanentActiveRules.length > 0" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-4 md:p-6 border border-gray-700">
              <h2 class="text-xl md:text-2xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl text-yellow-400">⭐</span>
                Permanent Rules
                <span v-if="activeRulesLoading" class="text-sm text-gray-400">(updating...)</span>
              </h2>
              
              <div class="flex flex-wrap gap-4 justify-start">
                <div
                  v-for="rule in permanentActiveRules"
                  :key="rule.id"
                  class="bg-gray-800/50 rounded-xl p-4 border border-gray-700 hover:border-gray-600 transition-all flex flex-col items-center"
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
                        :is-default="rule.cardData.isDefault || false"
                        :can-toggle="false"
                        :pickrate="0"
                        :is-premium-design="!rule.cardData.isTemplate"
                        :display-icon="designMode.displayIcon"
                        :display-text="designMode.displayText"
                      />
                  </div>
                  
                  <!-- Rule Name -->
                  <h3 class="text-white font-bold text-sm text-center mb-2 line-clamp-2">
                    {{ rule.ruleName }}
                  </h3>
                  
                  <!-- Always Active Badge -->
                  <div class="text-center">
                    <span class="inline-flex px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-medium rounded-full">
                      ★ Always Active
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Optional Rules Row -->
            <div v-if="optionalActiveRules.length > 0" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-4 md:p-6 border border-gray-700">
              <h2 class="text-xl md:text-2xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl text-cyan-400">⚡</span>
                Optional Rules
                <span v-if="activeRulesLoading" class="text-sm text-gray-400">(updating...)</span>
              </h2>
              
              <div class="flex flex-wrap gap-4 justify-start">
                <div
                  v-for="rule in optionalActiveRules"
                  :key="rule.id"
                  class="bg-gray-800/50 rounded-xl p-4 border border-gray-700 hover:border-gray-600 transition-all flex flex-col items-center"
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
                  <h3 class="text-white font-bold text-sm text-center mb-2 line-clamp-2">
                    {{ rule.ruleName }}
                  </h3>
                  
                  <!-- Timer -->
                  <div v-if="rule.type === 'time' || rule.type === 'hybrid'" class="flex items-center gap-1 text-cyan-400 font-mono text-sm font-bold mt-1">
                    ⏱ {{ Math.max(0, rule.clientTimeRemaining || 0) }}s
                  </div>
                  
                  <!-- Counter -->
                  <div v-if="rule.type === 'counter' || rule.type === 'hybrid'" class="flex items-center gap-1 mt-1">
                    <span class="text-orange-400 font-mono text-sm font-bold">
                      🎯 {{ rule.currentAmount || 0 }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- No Active Rules -->
            <div v-if="enrichedActiveRules.length === 0" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-8 border border-gray-700 text-center">
              <p class="text-gray-400 text-lg">No active rules yet</p>
              <p class="text-gray-500 text-sm mt-2">Waiting for gameplay to start...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- OVERVIEW TAB -->
      <div v-show="activeTab === 'overview'" class="space-y-6">
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-4 md:p-6 border border-gray-700">
          <h2 class="text-xl md:text-2xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="text-2xl">📋</span>
            Available Rules
          </h2>
          <p class="text-gray-400 mb-6 text-sm md:text-base">All rules configured for this playthrough.</p>

          <!-- Available Rules - Card Left, Info Right - 3 Columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="rule in availableRules"
              :key="rule.id"
              class="bg-gray-800/50 rounded-xl p-4 border border-gray-700 hover:border-gray-600 transition-all flex items-start gap-4"
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
                  <h3 class="text-white font-bold text-base md:text-lg">
                    {{ rule.ruleName }}
                  </h3>
                  <!-- Rarity Badge -->
                  <span class="flex-shrink-0 px-2 py-1 text-xs font-medium rounded-full"
                    :class="rule.ruleType === 'legendary' ? 'bg-yellow-500/20 text-yellow-400' :
                           rule.ruleType === 'court' ? 'bg-purple-500/20 text-purple-400' :
                           rule.difficultyLevel <= 5 ? 'bg-gray-500/20 text-gray-400' :
                           'bg-blue-500/20 text-blue-400'">
                    {{ rule.ruleType === 'legendary' ? '⭐ Legendary' :
                       rule.ruleType === 'court' ? '👑 Court' :
                       rule.difficultyLevel <= 5 ? '⚪ Common' : '🔵 Magical' }}
                  </span>
                </div>
                
                <!-- Rule Description -->
                <p v-if="rule.ruleDescription" class="text-gray-400 text-sm mb-3 line-clamp-3">
                  {{ rule.ruleDescription }}
                </p>
                
                <!-- Rule Details -->
                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-300 mb-2">
                  <span v-if="rule.difficultyLevel" class="flex items-center gap-1">
                    <span class="text-gray-500">Difficulty:</span>
                    <span class="font-semibold">{{ rule.difficultyLevel }}</span>
                  </span>
                  <span v-if="rule.durationSeconds" class="flex items-center gap-1">
                    <span class="text-gray-500">Duration:</span>
                    <span class="font-semibold">{{ formatDuration(rule.durationSeconds) }}</span>
                  </span>
                  <span v-if="rule.amount" class="flex items-center gap-1">
                    <span class="text-gray-500">Amount:</span>
                    <span class="font-semibold">{{ rule.amount }}x</span>
                  </span>
                </div>
                
                <!-- Badges -->
                <div v-if="rule.isDefault" class="mt-2">
                  <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-medium rounded-full">
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

