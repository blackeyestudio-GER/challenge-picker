<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import type { AdminRule, CreateRuleRequest, RuleDifficultyLevel } from '~/composables/useAdmin'
import { Icon } from '#components'

interface Props {
  show: boolean
  editingRule: AdminRule | null
  loading?: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'submit', data: CreateRuleRequest & { id?: number }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formData = ref<CreateRuleRequest & { id?: number }>({
  name: '',
  description: '',
  ruleType: 'basic',
  difficultyLevels: []
})

// Rule type configuration
const ruleTypeConfig = {
  basic: { name: 'Basic (Number Cards)', levels: 9, description: 'Maps to cards 2-10 (9 levels, +1m each). Always time-based.' },
  court: { name: 'Court (Face Cards)', levels: 4, description: 'Maps to Page, Knight, Queen, King (4 levels, +5m each). Always time-based.' },
  legendary: { name: 'Legendary (Major Arcana)', levels: 1, description: 'Maps to a specific Major Arcana card. Can be permanent OR timed.' }
}

const isLegendaryPermanent = ref(true) // Toggle for legendary rules

// Duration type selection
const durationType = ref<'time' | 'counter' | 'both' | 'permanent'>('time')

// Computed expected number of levels
const expectedLevels = computed(() => ruleTypeConfig[formData.value.ruleType].levels)

// Get default duration for a level based on rule type
const getDefaultDuration = (ruleType: string, level: number): number | null => {
  if (ruleType === 'legendary') {
    return null // Legendary rules are permanent (no duration)
  }
  if (ruleType === 'court') {
    return level * 300 // 5m, 10m, 15m, 20m (300s increments)
  }
  return level * 60 // Basic: 1m, 2m, 3m, etc. (60s increments)
}

// Initialize difficulty levels when rule type changes
watch(() => formData.value.ruleType, (newType) => {
  const expectedCount = ruleTypeConfig[newType].levels
  const currentCount = formData.value.difficultyLevels.length
  
  if (currentCount < expectedCount) {
    // Add missing levels with type-specific durations
    for (let i = currentCount + 1; i <= expectedCount; i++) {
      formData.value.difficultyLevels.push({
        difficultyLevel: i,
        durationMinutes: getDefaultDuration(newType, i)
      })
    }
  } else if (currentCount > expectedCount) {
    // Remove excess levels
    formData.value.difficultyLevels = formData.value.difficultyLevels.slice(0, expectedCount)
  }
})

watch(() => props.editingRule, (rule) => {
  if (rule) {
    formData.value = {
      id: rule.id,
      name: rule.name,
      description: rule.description || '',
      ruleType: rule.ruleType,
      difficultyLevels: rule.difficultyLevels.map(level => ({
        difficultyLevel: level.difficultyLevel,
        durationMinutes: level.durationMinutes,
        amount: level.amount
      }))
    }
    // Determine duration type from first level
    if (rule.difficultyLevels.length > 0) {
      const firstLevel = rule.difficultyLevels[0]
      const hasDuration = firstLevel.durationMinutes !== null
      const hasAmount = firstLevel.amount !== null
      
      if (hasDuration && hasAmount) {
        durationType.value = 'both'
      } else if (hasDuration) {
        durationType.value = 'time'
      } else if (hasAmount) {
        durationType.value = 'counter'
      } else {
        durationType.value = 'permanent'
      }
      
      isLegendaryPermanent.value = !hasDuration && !hasAmount
    }
  } else {
    const defaultType = 'basic'
    const levelCount = ruleTypeConfig[defaultType].levels
    formData.value = {
      name: '',
      description: '',
      ruleType: defaultType,
      difficultyLevels: Array.from({ length: levelCount }, (_, i) => ({
        difficultyLevel: i + 1,
        durationMinutes: getDefaultDuration(defaultType, i + 1),
        amount: null
      }))
    }
    durationType.value = 'time'
    isLegendaryPermanent.value = true
  }
}, { immediate: true })

// Watch duration type changes
watch(durationType, (type) => {
  formData.value.difficultyLevels.forEach((level, index) => {
    if (type === 'time') {
      level.durationMinutes = level.durationMinutes || 60 * (index + 1)
      level.amount = null
    } else if (type === 'counter') {
      level.durationMinutes = null
      level.amount = level.amount || (index + 1)
    } else if (type === 'both') {
      level.durationMinutes = level.durationMinutes || 60 * (index + 1)
      level.amount = level.amount || (index + 1)
    } else if (type === 'permanent') {
      level.durationMinutes = null
      level.amount = null
    }
  })
  
  // Update legendary permanent flag
  isLegendaryPermanent.value = type === 'permanent'
})

// Watch legendary permanent toggle (for backward compatibility)
watch(isLegendaryPermanent, (isPermanent) => {
  if (formData.value.ruleType === 'legendary') {
    if (isPermanent) {
      durationType.value = 'permanent'
    } else if (durationType.value === 'permanent') {
      durationType.value = 'time'
    }
  }
})

const handleSubmit = () => {
  // Validate based on duration type
  if (durationType.value === 'time' || durationType.value === 'both') {
    const allDurationsValid = formData.value.difficultyLevels.every(l => l.durationMinutes && l.durationMinutes > 0)
    if (!allDurationsValid) {
      alert('Please fill in duration for all difficulty levels')
      return
    }
  }
  
  if (durationType.value === 'counter' || durationType.value === 'both') {
    const allAmountsValid = formData.value.difficultyLevels.every(l => l.amount && l.amount > 0)
    if (!allAmountsValid) {
      alert('Please fill in amount for all difficulty levels')
      return
    }
  }
  
  // For basic/court rules, must have duration OR amount
  if (formData.value.ruleType !== 'legendary' && durationType.value === 'permanent') {
    alert('Basic/Court rules cannot be permanent. They must have duration or amount.')
    return
  }
  
  emit('submit', formData.value)
}

const handleClose = () => {
  emit('close')
}

// Get card name for difficulty level
const getCardName = (level: number): string => {
  const type = formData.value.ruleType
  if (type === 'basic') {
    return `Card ${level + 1}` // 2-10
  } else if (type === 'court') {
    return ['Page', 'Knight', 'Queen', 'King'][level - 1]
  } else {
    return 'Major Arcana'
  }
}

// Format seconds into human-readable time
const formatDuration = (seconds: number): string => {
  if (seconds === 0) return '0s'
  
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  const parts = []
  if (hours > 0) parts.push(`${hours}h`)
  if (minutes > 0) parts.push(`${minutes}m`)
  if (secs > 0) parts.push(`${secs}s`)
  
  return parts.join(' ')
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4" @click.self="handleClose">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full border border-gray-700 max-h-[90vh] overflow-y-auto">
      <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between sticky top-0 bg-gray-800 z-10">
        <h2 class="text-2xl font-bold text-white">
          {{ editingRule ? 'Edit Rule' : 'Create Rule' }}
        </h2>
        <button @click="handleClose" class="text-gray-400 hover:text-white">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Basic Info -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
              placeholder="e.g., Pistol Only, No Healing, Speed Run"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
            <textarea
              v-model="formData.description"
              rows="2"
              class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
              placeholder="Optional description of the rule"
            ></textarea>
          </div>
        </div>

        <!-- Rule Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Rule Type *</label>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <label
              v-for="(config, type) in ruleTypeConfig"
              :key="type"
              class="relative cursor-pointer"
            >
              <input
                type="radio"
                :value="type"
                v-model="formData.ruleType"
                class="peer sr-only"
              />
              <div class="p-4 border-2 rounded-lg transition peer-checked:border-cyan peer-checked:bg-cyan/10 border-gray-600 hover:border-gray-500">
                <div class="font-semibold text-white mb-1">{{ config.name }}</div>
                <div class="text-xs text-gray-400">{{ config.description }}</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Duration Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Rule Behavior *</label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <label class="relative cursor-pointer">
              <input
                type="radio"
                value="time"
                v-model="durationType"
                class="peer sr-only"
              />
              <div class="p-3 border-2 rounded-lg transition peer-checked:border-cyan peer-checked:bg-cyan/10 border-gray-600 hover:border-gray-500 text-center">
                <div class="text-sm font-semibold text-white">⏱️ Time-based</div>
                <div class="text-xs text-gray-400 mt-1">Has countdown</div>
              </div>
            </label>
            
            <label class="relative cursor-pointer">
              <input
                type="radio"
                value="counter"
                v-model="durationType"
                class="peer sr-only"
              />
              <div class="p-3 border-2 rounded-lg transition peer-checked:border-cyan peer-checked:bg-cyan/10 border-gray-600 hover:border-gray-500 text-center">
                <div class="text-sm font-semibold text-white">🔢 Counter</div>
                <div class="text-xs text-gray-400 mt-1">User counts down</div>
              </div>
            </label>
            
            <label class="relative cursor-pointer">
              <input
                type="radio"
                value="both"
                v-model="durationType"
                class="peer sr-only"
              />
              <div class="p-3 border-2 rounded-lg transition peer-checked:border-cyan peer-checked:bg-cyan/10 border-gray-600 hover:border-gray-500 text-center">
                <div class="text-sm font-semibold text-white">⏱️🔢 Both</div>
                <div class="text-xs text-gray-400 mt-1">Timer + counter</div>
              </div>
            </label>
            
            <label class="relative cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': formData.ruleType !== 'legendary' }">
              <input
                type="radio"
                value="permanent"
                v-model="durationType"
                :disabled="formData.ruleType !== 'legendary'"
                class="peer sr-only"
              />
              <div class="p-3 border-2 rounded-lg transition peer-checked:border-purple-600 peer-checked:bg-purple-900/10 border-gray-600 hover:border-gray-500 text-center peer-disabled:opacity-50">
                <div class="text-sm font-semibold text-white">🔮 Permanent</div>
                <div class="text-xs text-gray-400 mt-1">Always active</div>
              </div>
            </label>
          </div>
          <p class="text-xs text-gray-500 mt-2">
            <strong>Time-based:</strong> Countdown timer | 
            <strong>Counter:</strong> User clicks minus button | 
            <strong>Permanent:</strong> Always active (legendary only)
          </p>
        </div>

        <!-- Difficulty Levels -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-gray-300">
              Difficulty Levels ({{ expectedLevels }} required)
            </label>
            <span class="text-xs text-gray-400">
              {{ formData.difficultyLevels.length }} / {{ expectedLevels }} defined
            </span>
          </div>
          
          <div class="space-y-3 bg-gray-900/50 rounded-lg p-4 max-h-96 overflow-y-auto">
            <div
              v-for="(level, index) in formData.difficultyLevels"
              :key="index"
              class="p-4 bg-gray-900 rounded-lg border border-gray-700"
            >
              <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-semibold text-cyan min-w-[80px]">
                  Level {{ level.difficultyLevel }}
                </span>
                <span class="text-xs text-gray-400">
                  ({{ getCardName(level.difficultyLevel) }})
                </span>
              </div>
              
              <!-- Permanent Rule -->
              <div v-if="durationType === 'permanent'">
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-purple-900/20 border border-purple-700/50">
                  <Icon name="heroicons:infinity" class="w-5 h-5 text-purple-400" />
                  <span class="text-sm text-purple-300 font-medium">Permanent Rule (Always Active)</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                  This rule will be active for the entire playthrough and can be set as default in rulesets
                </p>
              </div>
              
              <!-- Time-based Rule -->
              <div v-else-if="durationType === 'time'" class="space-y-2">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Duration (seconds) *</label>
                  <input
                    v-model.number="level.durationMinutes"
                    type="number"
                    required
                    min="1"
                    max="86400"
                    class="w-full px-3 py-2 rounded-lg bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
                    :placeholder="getDefaultDuration(formData.ruleType, level.difficultyLevel)?.toString() || '60'"
                  />
                  <p v-if="level.durationMinutes && level.durationMinutes > 0" class="text-xs text-cyan mt-1">
                    = {{ formatDuration(level.durationMinutes) }}
                  </p>
                </div>
              </div>
              
              <!-- Counter-based Rule -->
              <div v-else-if="durationType === 'counter'" class="space-y-2">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Amount/Count *</label>
                  <input
                    v-model.number="level.amount"
                    type="number"
                    required
                    min="1"
                    max="9999"
                    class="w-full px-3 py-2 rounded-lg bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
                    :placeholder="level.difficultyLevel.toString()"
                  />
                  <p class="text-xs text-gray-400 mt-1">
                    Example: "Take damage {{ level.amount || level.difficultyLevel }} times"
                  </p>
                </div>
              </div>
              
              <!-- Both (Hybrid) -->
              <div v-else-if="durationType === 'both'" class="space-y-2">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Duration (seconds) *</label>
                  <input
                    v-model.number="level.durationMinutes"
                    type="number"
                    required
                    min="1"
                    max="86400"
                    class="w-full px-3 py-2 rounded-lg bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
                    placeholder="1800"
                  />
                  <p v-if="level.durationMinutes && level.durationMinutes > 0" class="text-xs text-cyan mt-1">
                    = {{ formatDuration(level.durationMinutes) }}
                  </p>
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Amount/Count *</label>
                  <input
                    v-model.number="level.amount"
                    type="number"
                    required
                    min="1"
                    max="9999"
                    class="w-full px-3 py-2 rounded-lg bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
                    :placeholder="level.difficultyLevel.toString()"
                  />
                  <p class="text-xs text-gray-400 mt-1">
                    Example: "Defeat {{ level.amount || level.difficultyLevel }} bosses in {{ formatDuration(level.durationMinutes || 1800) }}"
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <p class="text-xs text-gray-400 mt-2">
            💡 Tip: <strong class="text-cyan-400">Time-based:</strong> Countdown timer in playthrough. 
            <strong class="text-cyan-400">Counter:</strong> User clicks [-] button to count down (e.g., "Take damage 9 times"). 
            <strong class="text-purple-400">Permanent:</strong> Always active (only legendary rules, can be defaults). 
            Common time values: 5m = 300, 10m = 600, 30m = 1800, 1h = 3600
          </p>
        </div>
        
        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-700">
          <button
            type="button"
            @click="handleClose"
            class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-2 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ editingRule ? 'Update Rule' : 'Create Rule' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

