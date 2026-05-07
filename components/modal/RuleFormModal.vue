<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue'
import type { AdminRule, CreateRuleRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import IconPickerModal from '~/components/modal/IconPickerModal.vue'

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
const { warning } = useNotify()

const formData = ref<CreateRuleRequest & { id?: number }>({
  name: '',
  description: '',
  ruleType: 'basic',
  iconIdentifier: null,
  difficultyLevels: []
})

// Icon picker state
const showIconPicker = ref(false)

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

// Function to populate form data from rule
const populateFormFromRule = (rule: AdminRule | null) => {
  if (rule) {
    formData.value = {
      id: rule.id,
      name: rule.name,
      description: rule.description || '',
      ruleType: rule.ruleType,
      iconIdentifier: rule.iconIdentifier || null,
      difficultyLevels: rule.difficultyLevels.map(level => ({
        difficultyLevel: level.difficultyLevel,
        durationMinutes: level.durationSeconds !== null && level.durationSeconds !== undefined 
          ? Math.round(level.durationSeconds / 60) 
          : null,
        amount: level.amount ?? null
      }))
    }
    // Determine duration type from first level
    if (rule.difficultyLevels.length > 0) {
      const firstLevel = rule.difficultyLevels[0]
      const hasDuration = firstLevel.durationSeconds !== null && firstLevel.durationSeconds !== undefined
      const hasAmount = firstLevel.amount !== null && firstLevel.amount !== undefined
      
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
      iconIdentifier: null,
      difficultyLevels: Array.from({ length: levelCount }, (_, i) => ({
        difficultyLevel: i + 1,
        durationMinutes: getDefaultDuration(defaultType, i + 1),
        amount: null
      }))
    }
    durationType.value = 'time'
    isLegendaryPermanent.value = true
  }
}

watch(() => props.editingRule, populateFormFromRule, { immediate: true })

// Also watch show prop to ensure form is populated when modal opens
watch(() => props.show, (isShowing) => {
  if (isShowing && props.editingRule) {
    // Small delay to ensure DOM is ready
    nextTick(() => {
      populateFormFromRule(props.editingRule)
    })
  }
})

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
      warning('Please fill in duration for all difficulty levels')
      return
    }
  }
  
  if (durationType.value === 'counter' || durationType.value === 'both') {
    const allAmountsValid = formData.value.difficultyLevels.every(l => l.amount && l.amount > 0)
    if (!allAmountsValid) {
      warning('Please fill in amount for all difficulty levels')
      return
    }
  }
  
  // For basic/court rules, must have duration OR amount
  if (formData.value.ruleType !== 'legendary' && durationType.value === 'permanent') {
    warning('Basic/Court rules cannot be permanent. They must have duration or amount.')
    return
  }
  
  // Convert durationMinutes to durationSeconds for backend
  const submitData = {
    ...formData.value,
    difficultyLevels: formData.value.difficultyLevels.map(level => ({
      difficultyLevel: level.difficultyLevel,
      durationSeconds: level.durationMinutes !== null && level.durationMinutes !== undefined 
        ? level.durationMinutes * 60 
        : null,
      amount: level.amount ?? null
    }))
  }
  
  emit('submit', submitData)
}

const handleClose = () => {
  emit('close')
}

const handleIconSelect = (iconIdentifier: string) => {
  formData.value.iconIdentifier = iconIdentifier
  showIconPicker.value = false
}

const clearIcon = () => {
  formData.value.iconIdentifier = null
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
  <div v-if="show" class="admin-modal-backdrop z-50" @click.self="handleClose">
    <div class="admin-modal-surface max-w-4xl overflow-y-auto">
      <div class="admin-modal-header">
        <h2 class="admin-modal-title">
          {{ editingRule ? 'Edit Rule' : 'Create Rule' }}
        </h2>
        <button class="admin-modal-close" @click="handleClose">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form class="admin-modal-body space-y-6" @submit.prevent="handleSubmit">
        <!-- Basic Info -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Name *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
              placeholder="e.g., Pistol Only, No Healing, Speed Run"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Description</label>
            <textarea
              v-model="formData.description"
              rows="2"
              class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
              placeholder="Optional description of the rule"
            />
          </div>
        </div>

        <!-- Icon Selection -->
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Icon</label>
          <div class="flex items-center gap-3">
            <button
              type="button"
              class="btn btn-secondary px-4 py-2 flex items-center gap-2"
              @click="showIconPicker = true"
            >
              <Icon name="heroicons:photo" class="w-5 h-5" />
              {{ formData.iconIdentifier ? 'Change Icon' : 'Select Icon' }}
            </button>
            <div v-if="formData.iconIdentifier" class="flex items-center gap-2 px-3 py-2 bg-[var(--color-bg-overlay)] rounded-xl border border-[var(--color-border-secondary)]">
              <span class="text-sm text-[var(--color-text-muted)]">Selected:</span>
              <span class="text-sm text-[var(--color-text-primary)] font-medium">{{ formData.iconIdentifier }}</span>
              <button
                type="button"
                class="text-[var(--color-text-muted)] hover:text-[var(--status-failed-text)] transition"
                @click="clearIcon"
              >
                <Icon name="heroicons:x-mark" class="w-4 h-4" />
              </button>
            </div>
          </div>
          <p class="text-xs text-[var(--color-text-muted)] mt-2">
            💡 Select an icon to represent this rule in card designs
          </p>
        </div>

        <!-- Rule Type Selection -->
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Rule Type *</label>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <label
              v-for="(config, type) in ruleTypeConfig"
              :key="type"
              class="relative cursor-pointer"
            >
              <input
                v-model="formData.ruleType"
                type="radio"
                :value="type"
                class="peer sr-only"
              >
              <div class="p-4 border-2 rounded-xl transition peer-checked:border-[var(--color-accent-primary)] peer-checked:bg-[color-mix(in_srgb,var(--color-accent-primary)_10%,transparent)] border-[var(--color-border-secondary)] hover:border-[var(--color-border-primary)]">
                <div class="font-semibold text-[var(--color-text-primary)] mb-1">{{ config.name }}</div>
                <div class="text-xs text-[var(--color-text-muted)]">{{ config.description }}</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Duration Type Selection -->
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Rule Behavior *</label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <label class="relative cursor-pointer">
              <input
                v-model="durationType"
                type="radio"
                value="time"
                class="peer sr-only"
              >
              <div class="p-3 border-2 rounded-xl transition peer-checked:border-[var(--color-accent-primary)] peer-checked:bg-[color-mix(in_srgb,var(--color-accent-primary)_10%,transparent)] border-[var(--color-border-secondary)] hover:border-[var(--color-border-primary)] text-center">
                <div class="text-sm font-semibold text-[var(--color-text-primary)]">⏱️ Time-based</div>
                <div class="text-xs text-[var(--color-text-muted)] mt-1">Has countdown</div>
              </div>
            </label>
            
            <label class="relative cursor-pointer">
              <input
                v-model="durationType"
                type="radio"
                value="counter"
                class="peer sr-only"
              >
              <div class="p-3 border-2 rounded-xl transition peer-checked:border-[var(--color-accent-primary)] peer-checked:bg-[color-mix(in_srgb,var(--color-accent-primary)_10%,transparent)] border-[var(--color-border-secondary)] hover:border-[var(--color-border-primary)] text-center">
                <div class="text-sm font-semibold text-[var(--color-text-primary)]">🔢 Counter</div>
                <div class="text-xs text-[var(--color-text-muted)] mt-1">User counts down</div>
              </div>
            </label>
            
            <label class="relative cursor-pointer">
              <input
                v-model="durationType"
                type="radio"
                value="both"
                class="peer sr-only"
              >
              <div class="p-3 border-2 rounded-xl transition peer-checked:border-[var(--color-accent-primary)] peer-checked:bg-[color-mix(in_srgb,var(--color-accent-primary)_10%,transparent)] border-[var(--color-border-secondary)] hover:border-[var(--color-border-primary)] text-center">
                <div class="text-sm font-semibold text-[var(--color-text-primary)]">⏱️🔢 Both</div>
                <div class="text-xs text-[var(--color-text-muted)] mt-1">Timer + counter</div>
              </div>
            </label>
            
            <label class="relative cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': formData.ruleType !== 'legendary' }">
              <input
                v-model="durationType"
                type="radio"
                value="permanent"
                :disabled="formData.ruleType !== 'legendary'"
                class="peer sr-only"
              >
              <div class="p-3 border-2 rounded-xl transition peer-checked:border-[var(--color-accent-secondary)] peer-checked:bg-[color-mix(in_srgb,var(--color-accent-secondary)_12%,transparent)] border-[var(--color-border-secondary)] hover:border-[var(--color-border-primary)] text-center peer-disabled:opacity-50">
                <div class="text-sm font-semibold text-[var(--color-text-primary)]">🔮 Permanent</div>
                <div class="text-xs text-[var(--color-text-muted)] mt-1">Always active</div>
              </div>
            </label>
          </div>
          <p class="text-xs text-[var(--color-text-muted)] mt-2">
            <strong>Time-based:</strong> Countdown timer | 
            <strong>Counter:</strong> User clicks minus button | 
            <strong>Permanent:</strong> Always active (legendary only)
          </p>
        </div>

        <!-- Difficulty Levels -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-[var(--color-text-secondary)]">
              Difficulty Levels ({{ expectedLevels }} required)
            </label>
            <span class="text-xs text-[var(--color-text-muted)]">
              {{ formData.difficultyLevels.length }} / {{ expectedLevels }} defined
            </span>
          </div>
          
          <div class="space-y-3 bg-[var(--color-bg-overlay)] rounded-xl p-4 max-h-96 overflow-y-auto">
            <div
              v-for="(level, index) in formData.difficultyLevels"
              :key="index"
              class="p-4 bg-[var(--color-bg-card)] rounded-xl border border-[var(--color-border-secondary)]"
            >
              <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-semibold text-[var(--color-accent-primary)] min-w-[80px]">
                  Level {{ level.difficultyLevel }}
                </span>
                <span class="text-xs text-[var(--color-text-muted)]">
                  ({{ getCardName(level.difficultyLevel) }})
                </span>
              </div>
              
              <!-- Permanent Rule -->
              <div v-if="durationType === 'permanent'">
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-[color-mix(in_srgb,var(--color-accent-secondary)_12%,transparent)] border border-[color-mix(in_srgb,var(--color-accent-secondary)_40%,var(--color-border-secondary))]">
                  <Icon name="heroicons:infinity" class="w-5 h-5 text-[var(--color-accent-secondary)]" />
                  <span class="text-sm text-[var(--color-accent-secondary)] font-medium">Permanent Rule (Always Active)</span>
                </div>
                <p class="text-xs text-[var(--color-text-muted)] mt-1">
                  This rule will be active for the entire playthrough and can be set as default in rulesets
                </p>
              </div>
              
              <!-- Time-based Rule -->
              <div v-else-if="durationType === 'time'" class="space-y-2">
                <div>
                  <label class="block text-xs text-[var(--color-text-muted)] mb-1">Duration (minutes) *</label>
                  <input
                    v-model.number="level.durationMinutes"
                    type="number"
                    required
                    min="1"
                    max="1440"
                    class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
                    :placeholder="getDefaultDuration(formData.ruleType, level.difficultyLevel)?.toString() || '60'"
                  >
                  <p v-if="level.durationMinutes && level.durationMinutes > 0" class="text-xs text-[var(--color-accent-primary)] mt-1">
                    = {{ formatDuration(level.durationMinutes * 60) }}
                  </p>
                </div>
              </div>
              
              <!-- Counter-based Rule -->
              <div v-else-if="durationType === 'counter'" class="space-y-2">
                <div>
                  <label class="block text-xs text-[var(--color-text-muted)] mb-1">Amount/Count *</label>
                  <input
                    v-model.number="level.amount"
                    type="number"
                    required
                    min="1"
                    max="9999"
                    class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
                    :placeholder="level.difficultyLevel.toString()"
                  >
                  <p class="text-xs text-[var(--color-text-muted)] mt-1">
                    Example: "Take damage {{ level.amount || level.difficultyLevel }} times"
                  </p>
                </div>
              </div>
              
              <!-- Both (Hybrid) -->
              <div v-else-if="durationType === 'both'" class="space-y-2">
                <div>
                  <label class="block text-xs text-[var(--color-text-muted)] mb-1">Duration (minutes) *</label>
                  <input
                    v-model.number="level.durationMinutes"
                    type="number"
                    required
                    min="1"
                    max="1440"
                    class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
                    placeholder="1800"
                  >
                  <p v-if="level.durationMinutes && level.durationMinutes > 0" class="text-xs text-[var(--color-accent-primary)] mt-1">
                    = {{ formatDuration(level.durationMinutes * 60) }}
                  </p>
                </div>
                <div>
                  <label class="block text-xs text-[var(--color-text-muted)] mb-1">Amount/Count *</label>
                  <input
                    v-model.number="level.amount"
                    type="number"
                    required
                    min="1"
                    max="9999"
                    class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
                    :placeholder="level.difficultyLevel.toString()"
                  >
                  <p class="text-xs text-[var(--color-text-muted)] mt-1">
                    Example: "Defeat {{ level.amount || level.difficultyLevel }} bosses in {{ formatDuration(level.durationMinutes || 1800) }}"
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <p class="text-xs text-[var(--color-text-muted)] mt-2">
            💡 Tip: <strong class="text-[var(--color-accent-primary)]">Time-based:</strong> Countdown timer in playthrough. 
            <strong class="text-[var(--color-accent-primary)]">Counter:</strong> User clicks [-] button to count down (e.g., "Take damage 9 times"). 
            <strong class="text-[var(--color-accent-secondary)]">Permanent:</strong> Always active (only legendary rules, can be defaults). 
            Common time values: 5m = 300, 10m = 600, 30m = 1800, 1h = 3600
          </p>
        </div>
        
        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-border-secondary)]">
          <button
            type="button"
            class="btn btn-secondary px-6 py-2"
            @click="handleClose"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary px-6 py-2 font-bold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ editingRule ? 'Update Rule' : 'Create Rule' }}
          </button>
        </div>
      </form>
      
      <!-- Icon Picker Modal -->
      <IconPickerModal
        :show="showIconPicker"
        :current-icon="formData.iconIdentifier"
        @close="showIconPicker = false"
        @select="handleIconSelect"
      />
    </div>
  </div>
</template>
