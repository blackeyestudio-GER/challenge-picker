<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '#components'
import { useTheme } from '~/composables/useTheme'

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
}

const props = defineProps<Props>()

const emit = defineEmits<{
  toggle: [ruleId: number, difficultyLevel: number]
}>()

const { getRuleTypeBadgeClass, getRuleTypeName } = useTheme()

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
      'rule-card--default': isDefault
    }"
    @click="handleToggle"
  >
    <!-- Card Image Background -->
    <div v-if="cardImageUrl" class="rule-card__image-wrapper">
      <img :src="cardImageUrl" :alt="ruleName" class="rule-card__image" />
      <div class="rule-card__overlay" />
    </div>
    <div v-else class="rule-card__placeholder">
      <Icon name="heroicons:sparkles" class="rule-card__placeholder-icon" />
    </div>

    <!-- Card Content -->
    <div class="rule-card__content">
      <!-- Rule Type Badge -->
      <div class="rule-card__badge-wrapper">
        <span :class="getRuleTypeBadgeClass(ruleType)" class="rule-card__type-badge">
          {{ getRuleTypeName(ruleType) }}
        </span>
        <span v-if="isDefault" class="rule-card__default-badge">Default</span>
      </div>

      <!-- Rule Name -->
      <h3 class="rule-card__name">{{ ruleName }}</h3>

      <!-- Rule Description (if available) -->
      <p v-if="ruleDescription" class="rule-card__description">{{ ruleDescription }}</p>

      <!-- Difficulty Level Info -->
      <div class="rule-card__difficulty-info">
        <span class="rule-card__difficulty-label">Level {{ difficultyLevel }}</span>
        <div v-if="durationSeconds || amount" class="rule-card__difficulty-details">
          <span v-if="durationSeconds" class="rule-card__duration">
            {{ formatDuration(durationSeconds) }}
          </span>
          <span v-if="amount" class="rule-card__amount">
            {{ amount }}x
          </span>
        </div>
      </div>

      <!-- Rule Icon (if available) -->
      <div v-if="iconIdentifier" class="rule-card__icon-wrapper">
        <!-- Icon would be rendered here - need to fetch from RuleIcon -->
        <div class="rule-card__icon-placeholder" :style="iconStyle">
          <Icon name="heroicons:sparkles" />
        </div>
      </div>
    </div>

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
}

.rule-card__badge-wrapper {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.rule-card__type-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.rule-card__default-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  background-color: var(--color-accent-secondary);
  color: #ffffff;
}

.rule-card__name {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  line-height: 1.2;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}

.rule-card__description {
  font-size: 0.75rem;
  line-height: 1.4;
  margin-bottom: auto;
  opacity: 0.9;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
}

.rule-card__difficulty-info {
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.rule-card__difficulty-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
}

.rule-card__difficulty-details {
  display: flex;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.rule-card__duration,
.rule-card__amount {
  padding: 0.125rem 0.375rem;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 0.25rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
}

.rule-card__icon-wrapper {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 3;
  opacity: 0.3;
}

.rule-card__icon-placeholder {
  width: 4rem;
  height: 4rem;
  display: flex;
  align-items: center;
  justify-content: center;
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

