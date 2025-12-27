<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import type { ActiveRule } from '~/types/playthrough'

// Page meta
definePageMeta({
  middleware: 'auth',
})

// Auth composable
const { token } = useAuth()

// Active rules state
const activeRules = ref<ActiveRule[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const actionInProgress = ref<number | null>(null)

// Refresh interval (every 1 second for real-time feel)
const REFRESH_INTERVAL = 1000

// Group rules by type
const permanentRules = computed(() => 
  activeRules.value.filter(r => !r.expiresAt && !r.currentAmount)
)

const timerRules = computed(() => 
  activeRules.value.filter(r => r.expiresAt)
)

const counterRules = computed(() => 
  activeRules.value.filter(r => r.currentAmount !== null && r.currentAmount !== undefined)
)

// Calculate time remaining for timer rules
function getTimeRemaining(expiresAt: string): string {
  const now = new Date().getTime()
  const expires = new Date(expiresAt).getTime()
  const diff = expires - now

  if (diff <= 0) return 'Expired'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  if (hours > 0) {
    return `${hours}h ${minutes}m ${seconds}s`
  }

  if (minutes > 0) {
    return `${minutes}m ${seconds}s`
  }

  return `${seconds}s`
}

// Get time status (for color coding)
function getTimeStatus(expiresAt: string): 'critical' | 'warning' | 'normal' {
  const now = new Date().getTime()
  const expires = new Date(expiresAt).getTime()
  const diff = expires - now

  if (diff <= 0) return 'critical'
  if (diff <= 60000) return 'critical' // Less than 1 minute
  if (diff <= 180000) return 'warning' // Less than 3 minutes

  return 'normal'
}

// Fetch active rules
async function fetchActiveRules() {
  if (!token.value) {
    error.value = 'Not authenticated'
    loading.value = false
    return
  }

  try {
    const response = await fetch('http://localhost:8090/api/playthrough/active-rules', {
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      if (response.status === 404) {
        // No active playthrough
        activeRules.value = []
        error.value = 'No active playthrough found. Start a new playthrough to see rules.'
        return
      }
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    if (data.success && data.data) {
      activeRules.value = data.data.rules || []
      error.value = null
    }
  } catch (err) {
    console.error('Error fetching active rules:', err)
    error.value = err instanceof Error ? err.message : 'Failed to fetch active rules'
  } finally {
    loading.value = false
  }
}

// Decrement counter
async function decrementCounter(ruleId: number) {
  if (!token.value || actionInProgress.value) return

  actionInProgress.value = ruleId

  try {
    const response = await fetch('http://localhost:8090/api/playthrough/counters/decrement', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ ruleId }),
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    if (data.success) {
      // Refresh rules to get updated counter
      await fetchActiveRules()
    }
  } catch (err) {
    console.error('Error decrementing counter:', err)
    alert('Failed to decrement counter. Please try again.')
  } finally {
    actionInProgress.value = null
  }
}

// Increment counter (undo)
async function incrementCounter(ruleId: number) {
  if (!token.value || actionInProgress.value) return

  actionInProgress.value = ruleId

  try {
    const response = await fetch('http://localhost:8090/api/playthrough/counters/increment', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ ruleId }),
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    if (data.success) {
      // Refresh rules to get updated counter
      await fetchActiveRules()
    }
  } catch (err) {
    console.error('Error incrementing counter:', err)
    alert('Failed to increment counter. Please try again.')
  } finally {
    actionInProgress.value = null
  }
}

// Setup auto-refresh
let refreshTimer: NodeJS.Timeout | null = null

onMounted(() => {
  fetchActiveRules()
  
  // Refresh every second for live updates
  refreshTimer = setInterval(fetchActiveRules, REFRESH_INTERVAL)
})

onBeforeUnmount(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
})
</script>

<template>
  <div class="dashboard-page">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Page Header -->
      <div class="page-header mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Playthrough Dashboard</h1>
        <p class="text-gray-400">Manage your active rules and track your progress</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p class="text-gray-400 mt-4">Loading your active rules...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error && activeRules.length === 0" class="error-state">
        <div class="error-icon">⚠️</div>
        <h2 class="text-2xl font-bold text-white mb-2">{{ error }}</h2>
        <NuxtLink to="/playthrough/new" class="btn-primary mt-4">
          Start New Playthrough
        </NuxtLink>
      </div>

      <!-- Active Rules Display -->
      <div v-else class="rules-dashboard">
        <!-- Summary Stats -->
        <div class="stats-grid mb-8">
          <div class="stat-card permanent-stat">
            <div class="stat-icon">⚡</div>
            <div class="stat-content">
              <div class="stat-value">{{ permanentRules.length }}</div>
              <div class="stat-label">Permanent Rules</div>
            </div>
          </div>

          <div class="stat-card timer-stat">
            <div class="stat-icon">⏱️</div>
            <div class="stat-content">
              <div class="stat-value">{{ timerRules.length }}</div>
              <div class="stat-label">Timed Rules</div>
            </div>
          </div>

          <div class="stat-card counter-stat">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
              <div class="stat-value">{{ counterRules.length }}</div>
              <div class="stat-label">Counter Rules</div>
            </div>
          </div>

          <div class="stat-card total-stat">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-value">{{ activeRules.length }}</div>
              <div class="stat-label">Total Active</div>
            </div>
          </div>
        </div>

        <!-- Permanent Rules -->
        <div v-if="permanentRules.length > 0" class="rule-section mb-8">
          <h2 class="section-title permanent-title">
            <span class="title-icon">⚡</span>
            Permanent Rules
          </h2>
          <div class="rule-grid">
            <div 
              v-for="rule in permanentRules" 
              :key="rule.id"
              class="rule-card permanent-card"
            >
              <div class="rule-header">
                <h3 class="rule-name">{{ rule.name }}</h3>
                <span class="rule-badge permanent-badge">Permanent</span>
              </div>
              <p class="rule-description">{{ rule.description }}</p>
            </div>
          </div>
        </div>

        <!-- Timer Rules -->
        <div v-if="timerRules.length > 0" class="rule-section mb-8">
          <h2 class="section-title timer-title">
            <span class="title-icon">⏱️</span>
            Timed Rules
          </h2>
          <div class="rule-grid">
            <div 
              v-for="rule in timerRules" 
              :key="rule.id"
              class="rule-card timer-card"
              :class="`timer-${getTimeStatus(rule.expiresAt!)}`"
            >
              <div class="rule-header">
                <h3 class="rule-name">{{ rule.name }}</h3>
                <span class="rule-timer" :class="`timer-${getTimeStatus(rule.expiresAt!)}`">
                  {{ getTimeRemaining(rule.expiresAt!) }}
                </span>
              </div>
              <p class="rule-description">{{ rule.description }}</p>
            </div>
          </div>
        </div>

        <!-- Counter Rules -->
        <div v-if="counterRules.length > 0" class="rule-section mb-8">
          <h2 class="section-title counter-title">
            <span class="title-icon">🎯</span>
            Counter Rules
          </h2>
          <div class="rule-grid">
            <div 
              v-for="rule in counterRules" 
              :key="rule.id"
              class="rule-card counter-card"
            >
              <div class="rule-header">
                <h3 class="rule-name">{{ rule.name }}</h3>
                <div class="counter-display">
                  {{ rule.currentAmount }} remaining
                </div>
              </div>
              <p class="rule-description">{{ rule.description }}</p>
              
              <!-- Counter Controls -->
              <div class="counter-controls">
                <button
                  @click="decrementCounter(rule.id)"
                  :disabled="actionInProgress === rule.id || rule.currentAmount === 0"
                  class="btn-decrement"
                  title="Decrease counter (mark as completed)"
                >
                  <span class="btn-icon">−</span>
                  <span class="btn-label">Complete</span>
                </button>
                
                <button
                  @click="incrementCounter(rule.id)"
                  :disabled="actionInProgress === rule.id"
                  class="btn-increment"
                  title="Increase counter (undo)"
                >
                  <span class="btn-icon">+</span>
                  <span class="btn-label">Undo</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- No Active Rules -->
        <div v-if="activeRules.length === 0" class="no-rules-state">
          <div class="no-rules-icon">🎴</div>
          <h2 class="text-2xl font-bold text-white mb-2">No Active Rules</h2>
          <p class="text-gray-400 mb-4">Your playthrough has no active rules yet.</p>
          <button class="btn-secondary">Draw a Card</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

/* Page Header */
.page-header {
  text-align: center;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error State */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  text-align: center;
}

.error-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid;
  transition: all 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.permanent-stat { border-color: #ef4444; }
.timer-stat { border-color: #3b82f6; }
.counter-stat { border-color: #10b981; }
.total-stat { border-color: #8b5cf6; }

.stat-icon {
  font-size: 32px;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #ffffff;
}

.stat-label {
  font-size: 14px;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Rule Section */
.rule-section {
  margin-bottom: 32px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.title-icon {
  font-size: 28px;
}

.permanent-title { color: #ef4444; }
.timer-title { color: #3b82f6; }
.counter-title { color: #10b981; }

/* Rule Grid */
.rule-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 16px;
}

/* Rule Card */
.rule-card {
  padding: 20px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid;
  transition: all 0.2s ease;
}

.rule-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.permanent-card { border-color: #ef4444; }
.timer-card { border-color: #3b82f6; }
.counter-card { border-color: #10b981; }

.timer-card.timer-critical {
  border-color: #ef4444;
  animation: pulse 1s ease infinite;
}

.timer-card.timer-warning {
  border-color: #f59e0b;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

/* Rule Header */
.rule-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
  gap: 12px;
}

.rule-name {
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
  flex: 1;
}

/* Rule Badge */
.rule-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.permanent-badge {
  background: #ef4444;
  color: #ffffff;
}

/* Rule Timer */
.rule-timer {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  white-space: nowrap;
}

.rule-timer.timer-normal {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
}

.rule-timer.timer-warning {
  background: rgba(245, 158, 11, 0.2);
  color: #f59e0b;
}

.rule-timer.timer-critical {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

/* Counter Display */
.counter-display {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  white-space: nowrap;
}

/* Rule Description */
.rule-description {
  font-size: 14px;
  color: #9ca3af;
  line-height: 1.5;
  margin-bottom: 16px;
}

/* Counter Controls */
.counter-controls {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.counter-controls button {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s ease;
  cursor: pointer;
}

.counter-controls button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-decrement {
  background: #10b981;
  color: #ffffff;
  border: none;
}

.btn-decrement:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}

.btn-increment {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-increment:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}

.btn-icon {
  font-size: 18px;
  line-height: 1;
}

/* No Rules State */
.no-rules-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  text-align: center;
  padding: 40px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px dashed rgba(255, 255, 255, 0.2);
}

.no-rules-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

/* Buttons */
.btn-primary,
.btn-secondary {
  display: inline-block;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.btn-primary {
  background: #3b82f6;
  color: #ffffff;
  border: none;
}

.btn-primary:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .rule-grid {
    grid-template-columns: 1fr;
  }

  .page-header h1 {
    font-size: 28px;
  }
}
</style>

