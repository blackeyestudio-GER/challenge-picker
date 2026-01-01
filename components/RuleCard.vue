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
  iconColor: string | null
  iconBrightness: number | null
  iconOpacity: number | null
  isEnabled: boolean
  isDefault: boolean
  canToggle: boolean
  pickrate: number // Percentage chance (0-100)
  isPremiumDesign: boolean // True if user has premium/full design set
}

const props = defineProps<Props>()

const emit = defineEmits<{
  toggle: [ruleId: number, difficultyLevel: number]
}>()

const cardImageUrl = computed(() => {
  if (props.cardImageBase64) {
    return `data:image/jpeg;base64,${props.cardImageBase64}`
  }
  return null
})

const iconStyle = computed(() => {
  const style: Record<string, string> = {}
  if (props.iconColor) {
    style.color = props.iconColor
  }
  if (props.iconBrightness !== null) {
    style.filter = `brightness(${props.iconBrightness})`
  }
  if (props.iconOpacity !== null) {
    style.opacity = String(props.iconOpacity)
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
  <div
    class="rule-card"
    :class="{
      'rule-card--enabled': isEnabled,
      'rule-card--disabled': !isEnabled,
      'rule-card--clickable': canToggle,
      'rule-card--default': isDefault,
      'rule-card--template': isTemplateDesign,
      [`rule-card--${ruleType}`]: true
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
        <!-- Note: iconIdentifier is a database identifier, not a Nuxt Icon name -->
        <!-- For now, we always show the fallback icon since Icon component can't resolve database identifiers -->
        <!-- TODO: Fetch icon SVG content from API and render directly -->
        <div class="rule-card__template-icon" :style="iconStyle">
          <Icon name="heroicons:sparkles" class="rule-card__template-icon-svg" />
        </div>

        <!-- Legendary Rule Name (in middle for template designs) -->
        <h3 v-if="ruleType === 'legendary' && !isPremiumDesign" class="rule-card__template-legendary-name">
          {{ ruleName }}
        </h3>

        <!-- Rule Name with Duration/Amount (below icon, or below legendary name) -->
        <h3 v-if="ruleType !== 'legendary' || isPremiumDesign" class="rule-card__template-name">{{ ruleTitle }}</h3>
      </div>
    </template>

    <!-- Toggle Indicator -->
    <div class="rule-card__toggle-indicator">
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
  aspect-ratio: 2 / 3; /* Tarot card proportions */
  border-radius: 0.5rem;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: var(--color-bg-card);
  border: 2px solid var(--color-border-secondary);
  /* Subtle rule type tint/shadow */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.rule-card--clickable:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
  border-color: var(--color-border-accent);
}

.rule-card--enabled {
  border-color: var(--color-accent-primary);
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.rule-card--disabled {
  opacity: 0.6;
  filter: grayscale(0.5);
}

.rule-card--disabled .rule-card__overlay {
  background: rgba(0, 0, 0, 0.5);
}

/* Rule type tint overlay (subtle) */
.rule-card::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  opacity: 0.12;
  transition: opacity 0.2s ease;
}

.rule-card--legendary::before {
  background: var(--color-accent-secondary);
}

.rule-card--court::before {
  background: var(--color-accent-primary);
}

.rule-card--basic::before {
  background: var(--color-accent-primary);
  opacity: 0.08;
}

.rule-card__image-wrapper {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.rule-card__image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.rule-card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
  z-index: 1;
}

.rule-card__placeholder {
  position: absolute;
  inset: 0;
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
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 1rem;
  color: var(--color-text-primary);
  justify-content: space-between;
}

.rule-card__name {
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  padding-top: 0.5rem;
}


/* Legendary Rule Name Box (Premium) */
.rule-card__legendary-box {
  margin-top: auto;
  padding: 0.75rem 1rem;
  background-color: rgba(0, 0, 0, 0.8);
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-radius: 0.5rem;
  backdrop-filter: blur(8px);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
}

.rule-card__legendary-name {
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  display: block;
  text-align: center;
  line-height: 1.3;
}

/* Legendary name in middle (for template designs with card image) */
.rule-card__legendary-middle {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 3;
  padding: 1rem;
  background-color: rgba(0, 0, 0, 0.7);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 0.5rem;
  backdrop-filter: blur(8px);
  text-align: center;
  max-width: 80%;
}

.rule-card__legendary-middle-name {
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  display: block;
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
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  gap: 1rem;
}

.rule-card__template-icon {
  width: 6rem;
  height: 6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-primary);
  opacity: 0.9;
}

.rule-card__template-icon-svg {
  width: 100%;
  height: 100%;
}

.rule-card__template-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text-primary);
  text-align: center;
  line-height: 1.3;
}

.rule-card__template-legendary-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-primary);
  text-align: center;
  line-height: 1.3;
  margin-top: 0.5rem;
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
</style>

