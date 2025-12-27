<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import type { ActiveRule } from '~/types/playthrough'

// Auth composable
const { token } = useAuth()

// Active rules state
const activeRules = ref<ActiveRule[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// Refresh interval (every 2 seconds for OBS)
const REFRESH_INTERVAL = 2000

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

  if (diff <= 0) return '00:00'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  if (hours > 0) {
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
  }

  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
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
        // No active playthrough - clear rules
        activeRules.value = []
        error.value = null
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

// Setup auto-refresh
let refreshTimer: NodeJS.Timeout | null = null

onMounted(() => {
  fetchActiveRules()
  
  // Refresh every 2 seconds for live updates
  refreshTimer = setInterval(fetchActiveRules, REFRESH_INTERVAL)
})

onBeforeUnmount(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
})
</script>

<template>
  <div class="obs-overlay">
    <!-- Loading State -->
    <div v-if="loading" class="overlay-section">
      <div class="loading">Loading rules...</div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="overlay-section">
      <div class="error">{{ error }}</div>
    </div>

    <!-- No Active Playthrough -->
    <div v-else-if="activeRules.length === 0" class="overlay-section">
      <div class="no-rules">No active playthrough</div>
    </div>

    <!-- Active Rules Display -->
    <div v-else class="rules-container">
      <!-- Permanent Rules -->
      <div v-if="permanentRules.length > 0" class="rule-group permanent-group">
        <h2 class="group-title">🔴 Permanent Rules</h2>
        <div class="rule-list">
          <div 
            v-for="rule in permanentRules" 
            :key="rule.id"
            class="rule-item permanent-rule"
          >
            <div class="rule-icon">⚡</div>
            <div class="rule-content">
              <div class="rule-name">{{ rule.name }}</div>
              <div class="rule-description">{{ rule.description }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timer Rules -->
      <div v-if="timerRules.length > 0" class="rule-group timer-group">
        <h2 class="group-title">⏱️ Timed Rules</h2>
        <div class="rule-list">
          <div 
            v-for="rule in timerRules" 
            :key="rule.id"
            class="rule-item timer-rule"
          >
            <div class="rule-icon">⏰</div>
            <div class="rule-content">
              <div class="rule-name">{{ rule.name }}</div>
              <div class="rule-timer">{{ getTimeRemaining(rule.expiresAt!) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Counter Rules -->
      <div v-if="counterRules.length > 0" class="rule-group counter-group">
        <h2 class="group-title">🎯 Counter Rules</h2>
        <div class="rule-list">
          <div 
            v-for="rule in counterRules" 
            :key="rule.id"
            class="rule-item counter-rule"
          >
            <div class="rule-icon">🔢</div>
            <div class="rule-content">
              <div class="rule-name">{{ rule.name }}</div>
              <div class="rule-counter">{{ rule.currentAmount }} remaining</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* OBS-Optimized Styles */
.obs-overlay {
  width: 100vw;
  min-height: 100vh;
  background: transparent;
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: #ffffff;
}

/* Loading/Error States */
.overlay-section {
  padding: 20px;
  text-align: center;
}

.loading,
.error,
.no-rules {
  font-size: 18px;
  font-weight: 600;
  padding: 16px 24px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.8);
  display: inline-block;
}

.error {
  color: #ff4444;
  border: 2px solid #ff4444;
}

.no-rules {
  color: #888;
  border: 2px solid #444;
}

/* Rules Container */
.rules-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Rule Groups */
.rule-group {
  background: rgba(0, 0, 0, 0.85);
  border-radius: 12px;
  padding: 16px;
  border: 2px solid;
  backdrop-filter: blur(10px);
}

.permanent-group {
  border-color: #ff4444;
}

.timer-group {
  border-color: #4444ff;
}

.counter-group {
  border-color: #44ff44;
}

/* Group Title */
.group-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 12px 0;
  text-transform: uppercase;
  letter-spacing: 1px;
  text-shadow: 0 0 10px currentColor;
}

.permanent-group .group-title {
  color: #ff4444;
}

.timer-group .group-title {
  color: #4444ff;
}

.counter-group .group-title {
  color: #44ff44;
}

/* Rule List */
.rule-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Rule Item */
.rule-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border-left: 4px solid;
  transition: all 0.2s ease;
}

.permanent-rule {
  border-left-color: #ff4444;
}

.timer-rule {
  border-left-color: #4444ff;
}

.counter-rule {
  border-left-color: #44ff44;
}

/* Rule Icon */
.rule-icon {
  font-size: 24px;
  line-height: 1;
  flex-shrink: 0;
}

/* Rule Content */
.rule-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rule-name {
  font-size: 16px;
  font-weight: 600;
  color: #ffffff;
  text-shadow: 0 0 8px rgba(0, 0, 0, 0.8);
}

.rule-description {
  font-size: 13px;
  color: #cccccc;
  line-height: 1.4;
  text-shadow: 0 0 6px rgba(0, 0, 0, 0.8);
}

.rule-timer,
.rule-counter {
  font-size: 18px;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  letter-spacing: 1px;
  text-shadow: 0 0 10px currentColor;
}

.rule-timer {
  color: #4444ff;
}

.rule-counter {
  color: #44ff44;
}

/* Responsive Design (for testing) */
@media (max-width: 768px) {
  .obs-overlay {
    padding: 12px;
  }

  .group-title {
    font-size: 16px;
  }

  .rule-name {
    font-size: 14px;
  }

  .rule-description {
    font-size: 12px;
  }

  .rule-timer,
  .rule-counter {
    font-size: 16px;
  }
}

/* Animation for new rules */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.rule-item {
  animation: slideIn 0.3s ease;
}

/* Pulse animation for low timer */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}

.timer-rule:has(.rule-timer:contains('00:')) {
  animation: pulse 1s ease infinite;
}
</style>

