<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '#components'

interface Props {
  ruleId: number
  ruleName: string
  ruleType: 'basic' | 'court' | 'legendary'
  ruleDescription: string | null
  difficultyLevel: number
  durationSeconds: number | null
  amount: number | null
  tarotCardIdentifier: string | null
  cardImageBase64: string | null
  iconIdentifier: string | null
  iconColor?: string | null
  iconBrightness?: number | null
  iconOpacity?: number | null
  isEnabled: boolean
  isDefault: boolean
  canToggle: boolean
  pickrate: number // Percentage chance (0-100)
  isPremiumDesign: boolean // True if user has premium/full design set
  displayIcon?: boolean // True if design should show icon
  displayText?: boolean // True if design should show text
}

const props = withDefaults(defineProps<Props>(), {
  iconColor: null,
  iconBrightness: null,
  iconOpacity: null
})

const emit = defineEmits<{
  toggle: [ruleId: number, difficultyLevel: number]
}>()

// Normalize props to convert undefined to null
const normalizedIconColor = computed(() => props.iconColor ?? null)
const normalizedIconBrightness = computed(() => props.iconBrightness ?? null)
const normalizedIconOpacity = computed(() => props.iconOpacity ?? null)

// Check if basic card is common (1-5) or magical (6-10)
const isCommonBasic = computed(() => {
  return props.ruleType === 'basic' && props.difficultyLevel <= 5
})

const isMagicalBasic = computed(() => {
  return props.ruleType === 'basic' && props.difficultyLevel >= 6
})

// Detect if this is an "anti-rule" (prohibition rule like "No Pistol")
const isAntiRule = computed(() => {
  const name = props.ruleName.toLowerCase().trim()
  return name.startsWith('no ') || name.includes(' no ')
})

const cardImageUrl = computed(() => {
  if (props.cardImageBase64) {
    return `data:image/jpeg;base64,${props.cardImageBase64}`
  }
  return null
})

const iconStyle = computed(() => {
  const style: Record<string, string> = {}
  if (normalizedIconColor.value) {
    style.color = normalizedIconColor.value
  }
  if (normalizedIconBrightness.value !== null) {
    style.filter = `brightness(${normalizedIconBrightness.value})`
  }
  if (normalizedIconOpacity.value !== null) {
    style.opacity = String(normalizedIconOpacity.value)
  }
  return style
})

const formatDuration = (seconds: number): string => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  if (minutes === 0) {
    return `${remainingSeconds}s`
  }
  if (remainingSeconds === 0) {
    return `${minutes}m`
  }
  return `${minutes}m ${remainingSeconds}s`
}

// Format rule title with duration/amount
const ruleTitle = computed(() => {
  let title = props.ruleName
  if (props.durationSeconds) {
    title += ` (${formatDuration(props.durationSeconds)})`
  } else if (props.amount) {
    title += ` (${props.amount}x)`
  }
  return title
})

// Check if this is a template design (no card image)
const isTemplateDesign = computed(() => !props.cardImageBase64)

const handleToggle = () => {
  if (props.canToggle) {
    emit('toggle', props.ruleId, props.difficultyLevel)
  }
}
</script>

<template>
  <!-- Icon-Only Mode: Just the icon in a bordered box -->
  <div v-if="displayIcon && !displayText" class="rule-card-icon-only"
    :class="{
      'rule-card-icon-only--enabled': isEnabled,
      'rule-card-icon-only--disabled': !isEnabled,
      'rule-card-icon-only--clickable': canToggle,
      [`rule-card-icon-only--${ruleType}`]: true,
      'rule-card-icon-only--common-basic': isCommonBasic,
      'rule-card-icon-only--magical-basic': isMagicalBasic
    }"
    @click="handleToggle"
    :title="ruleTitle"
  >
    <div class="rule-card-icon-only__content">
      <div class="rule-card-icon-only__icon-wrapper">
        <Icon 
          v-if="iconIdentifier" 
          :name="`game-icons:${iconIdentifier}`" 
          class="rule-card-icon-only__icon"
          :style="iconStyle"
        />
        <div v-else class="rule-card-icon-only__placeholder">?</div>
        
        <!-- Prohibited Overlay for Anti-Rules (Street Sign Style) -->
        <div v-if="isAntiRule" class="rule-card-icon-only__prohibited">
          <div class="rule-card-icon-only__prohibited-circle"></div>
          <div class="rule-card-icon-only__prohibited-line"></div>
        </div>
      </div>
      
      <!-- Toggle Indicator for icon-only mode -->
      <div v-if="canToggle" class="rule-card-icon-only__toggle">
        <Icon
          :name="isEnabled ? 'heroicons:check-circle-solid' : 'heroicons:circle'"
          class="w-4 h-4"
          :class="isEnabled ? 'text-green-400' : 'text-gray-500'"
        />
      </div>
    </div>
  </div>

  <!-- Icon + Text Mode: Icon with text label below -->
  <div v-else-if="displayIcon && displayText" class="rule-card-icon-text"
    :class="{
      'rule-card-icon-text--enabled': isEnabled,
      'rule-card-icon-text--disabled': !isEnabled,
      'rule-card-icon-text--clickable': canToggle,
      'rule-card-icon-text--anti-rule': isAntiRule,
      [`rule-card-icon-text--${ruleType}`]: true,
      'rule-card-icon-text--common-basic': isCommonBasic,
      'rule-card-icon-text--magical-basic': isMagicalBasic
    }"
    @click="handleToggle"
  >
    <div class="rule-card-icon-text__content">
      <div class="rule-card-icon-text__icon-wrapper">
        <Icon 
          v-if="iconIdentifier" 
          :name="`game-icons:${iconIdentifier}`" 
          class="rule-card-icon-text__icon"
          :style="iconStyle"
        />
        <div v-else class="rule-card-icon-text__placeholder">?</div>
        
        <!-- Prohibited Overlay for Anti-Rules (Street Sign Style) -->
        <div v-if="isAntiRule" class="rule-card-icon-text__prohibited">
          <div class="rule-card-icon-text__prohibited-circle"></div>
          <div class="rule-card-icon-text__prohibited-line"></div>
        </div>
      </div>
      
      <div class="rule-card-icon-text__text">
        <h4 class="rule-card-icon-text__name">{{ ruleTitle }}</h4>
      </div>
      
      <!-- Toggle Indicator for icon-text mode -->
      <div v-if="canToggle" class="rule-card-icon-text__toggle">
        <Icon
          :name="isEnabled ? 'heroicons:check-circle-solid' : 'heroicons:circle'"
          class="w-4 h-4"
          :class="isEnabled ? 'text-green-400' : 'text-gray-500'"
        />
      </div>
    </div>
  </div>

  <!-- Text-Only Mode: Simple text display without card visuals -->
  <div v-else-if="!displayIcon && displayText" class="rule-card-text-only"
    :class="{
      'rule-card-text-only--enabled': isEnabled,
      'rule-card-text-only--disabled': !isEnabled,
      'rule-card-text-only--clickable': canToggle,
      'rule-card-text-only--anti-rule': isAntiRule,
      [`rule-card-text-only--${ruleType}`]: true,
    }"
    @click="handleToggle"
  >
    <div class="rule-card-text-only__content">
      <div class="rule-card-text-only__header">
        <h3 class="rule-card-text-only__name">{{ ruleTitle }}</h3>
        <!-- Rarity badge removed - it's shown in the play page details -->
      </div>
      <p v-if="ruleDescription" class="rule-card-text-only__description">{{ ruleDescription }}</p>
      
      <!-- Toggle Indicator for text-only mode -->
      <div v-if="canToggle" class="rule-card-text-only__toggle">
        <Icon
          :name="isEnabled ? 'heroicons:check-circle' : 'heroicons:circle'"
          class="w-5 h-5"
          :class="isEnabled ? 'text-green-400' : 'text-gray-500'"
        />
      </div>
    </div>
  </div>

  <!-- Card Visual Modes -->
  <div
    v-else
    class="rule-card"
    :class="{
      'rule-card--enabled': isEnabled,
      'rule-card--disabled': !isEnabled,
      'rule-card--clickable': canToggle,
      'rule-card--default': isDefault,
      'rule-card--template': isTemplateDesign,
      'rule-card--anti-rule': isAntiRule,
      [`rule-card--${ruleType}`]: true,
      'rule-card--common-basic': isCommonBasic,
      'rule-card--magical-basic': isMagicalBasic
    }"
    @click="handleToggle"
  >
    <!-- Full Card Image Design -->
    <template v-if="!isTemplateDesign">
      <!-- Card Image Background -->
      <div v-if="cardImageUrl" class="rule-card__image-wrapper">
        <img :src="cardImageUrl" :alt="ruleName" class="rule-card__image" />
        <div class="rule-card__overlay" />
      </div>

      <!-- Card Content -->
      <div class="rule-card__content">
        <!-- For Premium Legendary: Just boxed text with rule name and timing -->
        <div v-if="ruleType === 'legendary' && isPremiumDesign" class="rule-card__legendary-box">
          <span class="rule-card__legendary-name">{{ ruleTitle }}</span>
        </div>
        
        <!-- For Non-Legendary: Standard display -->
        <template v-else>
          <!-- Rule Title (with duration/amount) -->
          <h3 class="rule-card__name">{{ ruleTitle }}</h3>
        </template>
        
        <!-- Legendary Rule Name (in middle) - For template designs with card image -->
        <div v-if="ruleType === 'legendary' && !isPremiumDesign && cardImageUrl" class="rule-card__legendary-middle">
          <span class="rule-card__legendary-middle-name">{{ ruleName }}</span>
        </div>
      </div>
    </template>

    <!-- Template Design (No Card Image) -->
    <template v-else>
      <div class="rule-card__template-content">
        <!-- Icon (big, centered) -->
        <div class="rule-card__template-icon-wrapper">
          <div class="rule-card__template-icon" :style="iconStyle">
            <Icon 
              v-if="iconIdentifier" 
              :name="`game-icons:${iconIdentifier}`" 
              class="rule-card__template-icon-svg"
            />
            <Icon 
              v-else 
              name="heroicons:sparkles" 
              class="rule-card__template-icon-svg" 
            />
          </div>
          
          <!-- Prohibited Overlay for Anti-Rules (Street Sign Style) -->
          <div v-if="isAntiRule" class="rule-card__template-prohibited">
            <div class="rule-card__template-prohibited-circle"></div>
            <div class="rule-card__template-prohibited-line"></div>
          </div>
        </div>

        <!-- Legendary Rule Name (in middle for template designs) -->
        <h3 v-if="ruleType === 'legendary' && !isPremiumDesign" class="rule-card__template-legendary-name">
          {{ ruleName }}
        </h3>

        <!-- Rule Name with Duration/Amount (below icon, or below legendary name) -->
        <h3 v-if="ruleType !== 'legendary' || isPremiumDesign" class="rule-card__template-name">{{ ruleTitle }}</h3>
      </div>
    </template>

    <!-- Toggle Indicator (only show on creation page) -->
    <div v-if="canToggle" class="rule-card__toggle-indicator">
      <Icon
        :name="isEnabled ? 'heroicons:check-circle' : 'heroicons:circle'"
        class="rule-card__toggle-icon"
        :class="{
          'rule-card__toggle-icon--enabled': isEnabled,
          'rule-card__toggle-icon--disabled': !isEnabled
        }"
      />
    </div>
  </div>
</template>

<style scoped>
.rule-card {
  position: relative;
  /* Standard tarot card: 70mm x 120mm = 7:12 ratio */
  /* Responsive scaling: scales with viewport width while maintaining 7:12 ratio */
  width: clamp(80px, 8vw, 160px) !important;
  height: clamp(137px, calc(8vw * 1.714), 274px) !important; /* 12/7 ratio = 1.714 */
  min-width: 80px !important;
  max-width: 160px !important;
  min-height: 137px !important;
  max-height: 274px !important;
  border-radius: clamp(0.375rem, 0.5vw, 0.5rem); /* Scales with viewport */
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: var(--color-bg-card);
  border: 2px solid var(--color-border-secondary);
  /* Default shadow */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  display: block; /* Ensure proper rendering */
  flex-shrink: 0; /* Prevent flexbox from shrinking */
}

/* Rarity-based borders - Always visible */
.rule-card--legendary {
  border-color: rgb(234 179 8 / 0.6) !important;
}

.rule-card--court {
  border-color: rgb(168 85 247 / 0.6) !important;
}

/* Basic - split into common (1-5) and magical (6-10) */
.rule-card--common-basic {
  border-color: rgb(107 114 128 / 0.6) !important; /* Grey for common */
}

.rule-card--magical-basic {
  border-color: rgb(59 130 246 / 0.6) !important; /* Blue for magical */
}

.rule-card--clickable:hover {
  transform: translateY(-4px);
  border-color: var(--color-border-accent);
}

.rule-card--legendary.rule-card--clickable:hover {
  border-color: rgb(234 179 8 / 1) !important; /* Brighter gold on hover */
  box-shadow: 0 6px 20px rgba(234, 179, 8, 0.4);
}

.rule-card--court.rule-card--clickable:hover {
  border-color: rgb(168 85 247 / 1) !important; /* Brighter purple on hover */
  box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4);
}

.rule-card--common-basic.rule-card--clickable:hover {
  border-color: rgb(107 114 128 / 1) !important; /* Brighter grey on hover */
  box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
}

.rule-card--magical-basic.rule-card--clickable:hover {
  border-color: rgb(59 130 246 / 1) !important; /* Brighter blue on hover */
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

/* Enabled state - let rarity colors show through */
.rule-card--enabled {
  /* Border color is set by rarity, not overridden here */
}

.rule-card--disabled {
  opacity: 0.6;
  filter: grayscale(0.5);
}

.rule-card--disabled .rule-card__overlay {
  background: rgba(0, 0, 0, 0.5);
}

/* Rule type tint overlay - colored fill */
.rule-card::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  transition: opacity 0.2s ease;
}

.rule-card--legendary::before {
  background: rgb(234 179 8 / 0.15); /* Gold/Yellow tint */
}

.rule-card--court::before {
  background: rgb(168 85 247 / 0.15); /* Purple tint */
}

.rule-card--common-basic::before {
  background: rgb(107 114 128 / 0.12); /* Grey tint */
}

.rule-card--magical-basic::before {
  background: rgb(59 130 246 / 0.15); /* Blue tint */
}

/* Anti-rule styling for full card mode */
.rule-card--anti-rule::before {
  background: rgb(220 38 38 / 0.2) !important; /* Red tint */
}

.rule-card--anti-rule.rule-card--enabled {
  border-color: rgb(220 38 38) !important; /* red-600 border */
}

.rule-card--anti-rule.rule-card--clickable:hover {
  border-color: rgb(185 28 28) !important; /* red-700 on hover */
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
}

.rule-card__image-wrapper {
  position: absolute;
  top: 0;
  left: 0;
  width: 100% !important;
  height: 100% !important;
  z-index: 0;
  overflow: hidden;
}

.rule-card__image {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover;
  display: block;
}

.rule-card__overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100% !important;
  height: 100% !important;
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
  z-index: 1;
}

.rule-card__placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100% !important;
  height: 100% !important;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg-tertiary);
  z-index: 0;
}

.rule-card__placeholder-icon {
  width: 3rem;
  height: 3rem;
  color: var(--color-text-muted);
}

.rule-card__content {
  position: relative;
  z-index: 2;
  width: 100% !important;
  height: 100% !important;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: clamp(7px, 0.71vw, 14px) clamp(9px, 0.93vw, 19px); /* Scales with viewport */
  color: var(--color-text-primary);
  justify-content: space-between;
  box-sizing: border-box;
}

.rule-card__name {
  font-size: clamp(10px, 1.07vw, 16px); /* Scales with viewport */
  font-weight: 700;
  line-height: 1.2;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  padding-top: clamp(4px, 0.43vw, 9px); /* Scales with viewport */
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  text-align: center;
  margin: 0;
  max-height: clamp(19px, 3.2vw, 38px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}


/* Legendary Rule Name Box (Premium) */
.rule-card__legendary-box {
  margin-top: auto;
  padding: clamp(6px, 0.57vw, 11px) clamp(7px, 0.71vw, 14px); /* Scales with viewport */
  background-color: rgba(0, 0, 0, 0.8);
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-radius: clamp(0.25rem, 0.375vw, 0.375rem); /* Scales with viewport */
  backdrop-filter: blur(8px);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: clamp(2px, 0.21vw, 4px); /* Scales with viewport */
  width: 100%;
  box-sizing: border-box;
}

.rule-card__legendary-name {
  font-size: clamp(9px, 1vw, 15px); /* Scales with viewport */
  font-weight: 700;
  color: #ffffff;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  text-align: center;
  line-height: 1.2;
  margin: 0;
  max-height: clamp(17px, 2.8vw, 33px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Legendary name in middle (for template designs with card image) */
.rule-card__legendary-middle {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 3;
  padding: clamp(6px, 0.57vw, 11px) clamp(7px, 0.71vw, 14px); /* Scales with viewport */
  background-color: rgba(0, 0, 0, 0.7);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: clamp(0.25rem, 0.375vw, 0.375rem); /* Scales with viewport */
  backdrop-filter: blur(8px);
  text-align: center;
  max-width: 85%; /* Scales with card width */
  box-sizing: border-box;
}

.rule-card__legendary-middle-name {
  font-size: clamp(10px, 1.07vw, 16px); /* Scales with viewport */
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  line-height: 1.2;
  word-break: break-word;
  overflow-wrap: break-word;
  margin: 0;
  max-height: clamp(19px, 3.2vw, 38px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Template Design Styles */
.rule-card--template {
  background-color: var(--color-bg-card);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.rule-card__template-content {
  position: relative;
  z-index: 2;
  width: 100% !important;
  height: 100% !important;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: clamp(7px, 0.71vw, 14px) clamp(9px, 0.93vw, 19px); /* Scales with viewport */
  gap: clamp(7px, 0.71vw, 14px); /* Scales with viewport */
  box-sizing: border-box;
}

.rule-card__template-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rule-card__template-icon {
  width: clamp(54px, 5.36vw, 107px); /* Scales with viewport (53.6% of card width) */
  height: clamp(54px, 5.36vw, 107px); /* Scales with viewport */
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-primary);
  opacity: 0.9;
  flex-shrink: 0;
}

.rule-card__template-icon-svg {
  width: 100%;
  height: 100%;
}

/* Prohibited overlay for template mode - Street Sign Style */
.rule-card__template-prohibited {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  z-index: 10;
}

.rule-card__template-prohibited-circle {
  position: absolute;
  width: 100%;
  height: 100%;
  border: clamp(3px, 0.4vw, 4px) solid rgb(220 38 38); /* red-600 */
  border-radius: 50%;
  background-color: rgba(220, 38, 38, 0.15);
  box-shadow: 0 0 0 clamp(1px, 0.13vw, 2px) rgba(0, 0, 0, 0.3);
}

.rule-card__template-prohibited-line {
  position: absolute;
  width: 120%;
  height: clamp(4px, 0.5vw, 5px);
  background-color: rgb(220 38 38); /* red-600 */
  transform: rotate(45deg);
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
}

.rule-card__template-name {
  font-size: clamp(9px, 1vw, 15px); /* Scales with viewport */
  font-weight: 700;
  color: var(--color-text-primary);
  text-align: center;
  line-height: 1.2;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  margin: 0;
  max-height: clamp(17px, 2.8vw, 33px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.rule-card__template-legendary-name {
  font-size: clamp(10px, 1.07vw, 16px); /* Scales with viewport */
  font-weight: 700;
  color: var(--color-text-primary);
  text-align: center;
  line-height: 1.2;
  margin-top: clamp(6px, 0.64vw, 13px); /* Scales with viewport */
  margin-bottom: 0;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-height: clamp(19px, 3.2vw, 38px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.rule-card__template-pickrate {
  margin-top: 0.25rem;
  padding: 0.25rem 0.5rem;
  background-color: rgba(6, 182, 212, 0.2);
  border: 1px solid rgba(6, 182, 212, 0.4);
  border-radius: 0.25rem;
  display: inline-block;
}

.rule-card__template-pickrate .rule-card__pickrate-label {
  color: var(--color-text-primary);
  text-shadow: none;
}

.rule-card__toggle-indicator {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  z-index: 4;
  background-color: rgba(0, 0, 0, 0.7);
  border-radius: 50%;
  padding: 0.25rem;
  backdrop-filter: blur(4px);
}

.rule-card__toggle-icon {
  width: 1.25rem;
  height: 1.25rem;
  transition: all 0.2s ease;
}

.rule-card__toggle-icon--enabled {
  color: var(--color-accent-primary);
}

.rule-card__toggle-icon--disabled {
  color: var(--color-text-muted);
}

/* ========== TEXT-ONLY MODE ========== */
.rule-card-text-only {
  position: relative;
  /* Match card visual dimensions - scales with viewport */
  width: clamp(80px, 8vw, 160px) !important;
  height: clamp(137px, calc(8vw * 1.714), 274px) !important; /* 12/7 ratio = 1.714 */
  min-width: 80px !important;
  max-width: 160px !important;
  min-height: 137px !important;
  max-height: 274px !important;
  border-radius: clamp(0.375rem, 0.5vw, 0.5rem); /* Scales with viewport */
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: var(--color-bg-card);
  border: 2px solid var(--color-border-secondary);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  display: block;
  flex-shrink: 0;
}

.rule-card-text-only--enabled {
  /* Enabled state - let rarity colors show through */
}

.rule-card-text-only--disabled {
  opacity: 0.6;
  filter: grayscale(0.5);
}

.rule-card-text-only--clickable {
  cursor: pointer;
}

.rule-card-text-only--clickable:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  transform: translateY(-2px);
}

/* Rarity borders - match card visual mode */
.rule-card-text-only--legendary {
  border-color: rgb(234 179 8 / 0.6) !important;
}

.rule-card-text-only--legendary::before {
  background: rgb(234 179 8 / 0.15); /* Gold/Yellow tint */
}

.rule-card-text-only--court {
  border-color: rgb(168 85 247 / 0.6) !important;
}

.rule-card-text-only--court::before {
  background: rgb(168 85 247 / 0.15); /* Purple tint */
}

.rule-card-text-only--common-basic {
  border-color: rgb(107 114 128 / 0.6) !important; /* Grey for common */
}

.rule-card-text-only--common-basic::before {
  background: rgb(107 114 128 / 0.12); /* Grey tint */
}

.rule-card-text-only--magical-basic {
  border-color: rgb(59 130 246 / 0.6) !important; /* Blue for magical */
}

.rule-card-text-only--magical-basic::before {
  background: rgb(59 130 246 / 0.15); /* Blue tint */
}

/* Rarity tint overlay */
.rule-card-text-only::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  transition: opacity 0.2s ease;
}

.rule-card-text-only__content {
  position: relative;
  z-index: 2;
  width: 100% !important;
  height: 100% !important;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: clamp(7px, 0.71vw, 14px) clamp(9px, 0.93vw, 19px); /* Scales with viewport */
  color: var(--color-text-primary);
  justify-content: space-between;
  box-sizing: border-box;
}

.rule-card-text-only__header {
  display: flex;
  flex-direction: column;
  gap: clamp(4px, 0.36vw, 7px); /* Scales with viewport */
}

.rule-card-text-only__name {
  font-size: clamp(10px, 1.07vw, 16px); /* Scales with viewport */
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1.2;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  text-align: center;
  margin: 0;
  max-height: clamp(19px, 3.2vw, 38px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}

.rule-card-text-only__type {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  white-space: nowrap;
}

.rule-card-text-only__type--legendary {
  background-color: rgba(234, 179, 8, 0.2);
  color: rgb(234 179 8);
  border: 1px solid rgba(234, 179, 8, 0.4);
}

.rule-card-text-only__type--court {
  background-color: rgba(168, 85, 247, 0.2);
  color: rgb(168 85 247);
  border: 1px solid rgba(168, 85, 247, 0.4);
}

.rule-card-text-only__type--basic {
  background-color: rgba(59, 130, 246, 0.2);
  color: rgb(59 130 246);
  border: 1px solid rgba(59, 130, 246, 0.4);
}

.rule-card-text-only__description {
  font-size: clamp(8px, 0.86vw, 12px); /* Scales with viewport */
  color: var(--color-text-secondary);
  line-height: 1.3;
  margin: 0;
  margin-top: clamp(4px, 0.36vw, 7px); /* Scales with viewport */
  max-height: clamp(25px, 4.2vw, 50px); /* Scales with viewport */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

.rule-card-text-only__toggle {
  position: absolute;
  top: clamp(7px, 0.71vw, 14px); /* Scales with viewport */
  right: clamp(7px, 0.71vw, 14px); /* Scales with viewport */
  z-index: 3;
}

/* ========== ICON-ONLY MODE ========== */
.rule-card-icon-only {
  background-color: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  padding: 1rem;
  transition: all 0.2s;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 120px;
}

.rule-card-icon-only--enabled {
  border-color: rgba(6, 182, 212, 0.5);
  background-color: rgba(6, 182, 212, 0.1);
}

.rule-card-icon-only--disabled {
  opacity: 0.5;
}

.rule-card-icon-only--clickable {
  cursor: pointer;
}

.rule-card-icon-only--clickable:hover {
  border-color: rgba(6, 182, 212, 0.7);
  transform: translateY(-2px);
}

/* Rarity borders for icon-only mode */
.rule-card-icon-only--legendary.rule-card-icon-only--enabled {
  border-color: rgb(234 179 8);
  background-color: rgba(234, 179, 8, 0.1);
}

.rule-card-icon-only--court.rule-card-icon-only--enabled {
  border-color: rgb(168 85 247);
  background-color: rgba(168, 85, 247, 0.1);
}

.rule-card-icon-only--magical-basic.rule-card-icon-only--enabled {
  border-color: rgb(59 130 246);
  background-color: rgba(59, 130, 246, 0.1);
}

.rule-card-icon-only--common-basic.rule-card-icon-only--enabled {
  border-color: rgb(107 114 128);
  background-color: rgba(107, 114, 128, 0.1);
}

.rule-card-icon-only__content {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rule-card-icon-only__icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rule-card-icon-only__icon {
  width: 4rem;
  height: 4rem;
  color: var(--color-text-primary);
}

/* Prohibited overlay for anti-rules - Street Sign Style */
.rule-card-icon-only__prohibited {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  z-index: 10;
}

.rule-card-icon-only__prohibited-circle {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 4px solid rgb(220 38 38); /* red-600 */
  border-radius: 50%;
  background-color: rgba(220, 38, 38, 0.15);
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.3);
}

.rule-card-icon-only__prohibited-line {
  position: absolute;
  width: 120%;
  height: 5px;
  background-color: rgb(220 38 38); /* red-600 */
  transform: rotate(45deg);
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
}

/* Light theme adjustments for prohibited overlay */
html.theme-light .rule-card-icon-only__prohibited-circle {
  border-color: rgb(185 28 28); /* red-700 - darker for contrast */
  background-color: rgba(220, 38, 38, 0.2);
}

html.theme-light .rule-card-icon-only__prohibited-line {
  background-color: rgb(185 28 28); /* red-700 - darker for contrast */
}

html.theme-light .rule-card-icon-text__prohibited-circle {
  border-color: rgb(185 28 28); /* red-700 - darker for contrast */
  background-color: rgba(220, 38, 38, 0.2);
}

html.theme-light .rule-card-icon-text__prohibited-line {
  background-color: rgb(185 28 28); /* red-700 - darker for contrast */
}

html.theme-light .rule-card__template-prohibited-circle {
  border-color: rgb(185 28 28); /* red-700 - darker for contrast */
  background-color: rgba(220, 38, 38, 0.2);
}

html.theme-light .rule-card__template-prohibited-line {
  background-color: rgb(185 28 28); /* red-700 - darker for contrast */
}

/* Anti-rule color scheme - red/forbidden theme */
.rule-card-icon-only--anti-rule.rule-card-icon-only--enabled {
  border-color: rgb(220 38 38) !important; /* red-600 */
  background-color: rgba(220, 38, 38, 0.15) !important;
}

.rule-card-icon-only--anti-rule.rule-card-icon-only--clickable:hover {
  border-color: rgb(185 28 28) !important; /* red-700 */
  background-color: rgba(220, 38, 38, 0.2) !important;
}

.rule-card-icon-only__placeholder {
  width: 4rem;
  height: 4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-muted);
}

.rule-card-icon-only__toggle {
  position: absolute;
  top: -0.5rem;
  right: -0.5rem;
}

/* ========== ICON + TEXT MODE ========== */
.rule-card-icon-text {
  background-color: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  padding: 1rem;
  transition: all 0.2s;
  position: relative;
}

.rule-card-icon-text--enabled {
  border-color: rgba(6, 182, 212, 0.5);
  background-color: rgba(6, 182, 212, 0.1);
}

.rule-card-icon-text--disabled {
  opacity: 0.5;
}

.rule-card-icon-text--clickable {
  cursor: pointer;
}

.rule-card-icon-text--clickable:hover {
  border-color: rgba(6, 182, 212, 0.7);
  transform: translateY(-2px);
}

/* Rarity borders for icon-text mode */
.rule-card-icon-text--legendary.rule-card-icon-text--enabled {
  border-color: rgb(234 179 8);
  background-color: rgba(234, 179, 8, 0.1);
}

.rule-card-icon-text--court.rule-card-icon-text--enabled {
  border-color: rgb(168 85 247);
  background-color: rgba(168, 85, 247, 0.1);
}

.rule-card-icon-text--magical-basic.rule-card-icon-text--enabled {
  border-color: rgb(59 130 246);
  background-color: rgba(59, 130, 246, 0.1);
}

.rule-card-icon-text--common-basic.rule-card-icon-text--enabled {
  border-color: rgb(107 114 128);
  background-color: rgba(107, 114, 128, 0.1);
}

/* Anti-rule color scheme for icon-text mode */
.rule-card-icon-text--anti-rule.rule-card-icon-text--enabled {
  border-color: rgb(220 38 38) !important; /* red-600 */
  background-color: rgba(220, 38, 38, 0.15) !important;
}

.rule-card-icon-text--anti-rule.rule-card-icon-text--clickable:hover {
  border-color: rgb(185 28 28) !important; /* red-700 */
  background-color: rgba(220, 38, 38, 0.2) !important;
}

/* Prohibited overlay for icon-text mode - Street Sign Style */
.rule-card-icon-text__prohibited {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  z-index: 10;
}

.rule-card-icon-text__prohibited-circle {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 3px solid rgb(220 38 38); /* red-600 */
  border-radius: 50%;
  background-color: rgba(220, 38, 38, 0.15);
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3);
}

.rule-card-icon-text__prohibited-line {
  position: absolute;
  width: 120%;
  height: 4px;
  background-color: rgb(220 38 38); /* red-600 */
  transform: rotate(45deg);
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
}

.rule-card-icon-text__content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.rule-card-icon-text__icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rule-card-icon-text__icon {
  width: 3rem;
  height: 3rem;
  color: var(--color-text-primary);
}

.rule-card-icon-text__placeholder {
  width: 3rem;
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-muted);
}

.rule-card-icon-text__text {
  text-align: center;
}

.rule-card-icon-text__name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-primary);
  line-height: 1.3;
  margin: 0;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-width: 100%;
}

.rule-card-icon-text__toggle {
  position: absolute;
  top: -0.5rem;
  right: -0.5rem;
}
</style>

