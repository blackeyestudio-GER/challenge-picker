<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import type { AdminRuleset, CreateRulesetRequest, AdminRule } from '~/composables/useAdmin'
import { useAdmin } from '~/composables/useAdmin'
import { useTheme } from '~/composables/useTheme'
import { Icon } from '#components'

interface Game {
  id: number
  name: string
  isCategoryRepresentative?: boolean
  isActive?: boolean
}

interface Props {
  show: boolean
  editingRuleset: AdminRuleset | null
  games: Game[]
  loading?: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'submit', data: CreateRulesetRequest & { id?: number }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const { warning, notifyApiError } = useNotify()

const { fetchAdminRules } = useAdmin()
const { getRuleTypeBadge } = useTheme()

const gameSearchQuery = ref('')
const ruleSearchQuery = ref('')
const allRules = ref<AdminRule[]>([])
const selectedRuleIds = ref<Set<number>>(new Set())
const defaultRuleIds = ref<Set<number>>(new Set())

const formData = ref<CreateRulesetRequest & { id?: number }>({
  name: '',
  description: '',
  gameIds: []
})

// Filter out category representative games and apply search
const filteredGames = computed(() => {
  const realGames = props.games.filter(g => !g.isCategoryRepresentative)
  
  if (!gameSearchQuery.value.trim()) {
    return realGames
  }
  
  const query = gameSearchQuery.value.toLowerCase()
  return realGames.filter(game =>
    game.name.toLowerCase().includes(query)
  )
})

// Filter rules by search query
const filteredRules = computed(() => {
  if (!ruleSearchQuery.value.trim()) {
    return allRules.value
  }
  
  const query = ruleSearchQuery.value.toLowerCase()
  return allRules.value.filter(rule =>
    rule.name.toLowerCase().includes(query)
  )
})

// Load all rules for default rule selection
const loadRules = async () => {
  try {
    const response = await fetchAdminRules()
    allRules.value = response.rules
  } catch (err) {
    notifyApiError(err, 'Failed to load rules')
  }
}

onMounted(() => {
  loadRules()
})

watch(() => props.editingRuleset, (ruleset) => {
  gameSearchQuery.value = '' // Clear search
  ruleSearchQuery.value = '' // Clear search
  selectedRuleIds.value = new Set()
  defaultRuleIds.value = new Set()
  
  if (ruleset) {
    formData.value = {
      id: ruleset.id,
      name: ruleset.name,
      description: ruleset.description || '',
      gameIds: ruleset.games.map(g => g.id)
    }
    // TODO: Load assigned rules and default flags from ruleset
    // For now, load from defaultRules property
    if (ruleset.defaultRules) {
      ruleset.defaultRules.forEach(r => {
        selectedRuleIds.value.add(r.id)
        defaultRuleIds.value.add(r.id)
      })
    }
  } else {
    formData.value = {
      name: '',
      description: '',
      gameIds: []
    }
  }
}, { immediate: true })

// Helper to check if a rule can be marked as default
const canBeDefault = (rule: AdminRule): boolean => {
  // Only permanent legendary rules (no duration AND no amount) can be defaults
  return rule.ruleType === 'legendary' && 
         rule.difficultyLevels.length > 0 && 
         rule.difficultyLevels[0].durationMinutes === null &&
         rule.difficultyLevels[0].amount === null
}

// When a rule is unchecked, also uncheck its default status
const toggleRuleSelection = (ruleId: number) => {
  if (selectedRuleIds.value.has(ruleId)) {
    selectedRuleIds.value.delete(ruleId)
    defaultRuleIds.value.delete(ruleId)
  } else {
    selectedRuleIds.value.add(ruleId)
  }
}

const handleSubmit = () => {
  if (formData.value.gameIds.length === 0) {
    warning('Please select at least one game')
    return
  }
  
  // Build rules array with default flags
  // TODO: Need to add tarot card assignment and position
  // For now, this is commented out until backend is updated
  /*
  const rules = Array.from(selectedRuleIds.value).map((ruleId, index) => ({
    ruleId,
    tarotCardIdentifier: 'the-fool', // Placeholder
    position: index,
    isDefault: defaultRuleIds.value.has(ruleId)
  }))
  
  emit('submit', {
    ...formData.value,
    rules
  })
  */
  
  // For now, just emit without rules
  emit('submit', formData.value)
}

const handleClose = () => {
  emit('close')
}
</script>

<template>
  <div v-if="show" class="admin-modal-backdrop z-50" @click.self="handleClose">
    <div class="admin-modal-surface max-w-2xl flex flex-col">
      <div class="admin-modal-header">
        <h2 class="admin-modal-title">{{ editingRuleset ? 'Edit Ruleset' : 'Create Ruleset' }}</h2>
        <button class="admin-modal-close" @click="handleClose">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>

      <form class="admin-modal-body space-y-4 overflow-y-auto" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            placeholder="e.g., Classic Tank Controls"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            class="w-full px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            placeholder="Describe what makes this ruleset unique..."
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">
            Games *
            <span class="text-xs text-[var(--color-text-muted)] font-normal ml-2">(Select all games this ruleset applies to)</span>
          </label>
          <div class="mb-2">
            <input
              v-model="gameSearchQuery"
              type="text"
              placeholder="Search games..."
              class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
            >
          </div>
          <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-3 rounded-xl bg-[var(--color-bg-overlay)] border border-[var(--color-border-secondary)]">
            <label
              v-for="game in filteredGames"
              :key="game.id"
              class="flex items-center gap-2 cursor-pointer hover:bg-[var(--color-bg-card-hover)] p-2 rounded-lg transition"
              :class="{ 'opacity-50': game.isActive === false }"
            >
              <input
                v-model="formData.gameIds"
                type="checkbox"
                :value="game.id"
                class="w-4 h-4 rounded bg-[var(--color-bg-card)] border-[var(--color-border-secondary)] text-[var(--color-accent-primary)] focus:ring-[var(--color-accent-primary)]"
              >
              <span class="text-sm text-[var(--color-text-secondary)] truncate" :title="game.name">
                {{ game.name }}
                <span v-if="game.isActive === false" class="text-xs text-[var(--color-text-muted)]">(inactive)</span>
              </span>
            </label>
            <div v-if="filteredGames.length === 0" class="col-span-full text-center text-[var(--color-text-muted)] text-sm py-4">
              No games found
            </div>
          </div>
          <p class="text-xs text-[var(--color-text-muted)] mt-1">
            {{ formData.gameIds?.length || 0 }} game{{ (formData.gameIds?.length || 0) !== 1 ? 's' : '' }} selected
            <span v-if="gameSearchQuery" class="text-[var(--color-text-secondary)]">({{ filteredGames.length }} shown)</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">
            Rules in this Ruleset
            <span class="text-xs text-[var(--color-text-muted)] font-normal ml-2">(Select which rules are available in this ruleset)</span>
          </label>
          <div class="mb-2">
            <input
              v-model="ruleSearchQuery"
              type="text"
              placeholder="Search rules..."
              class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)] text-sm"
            >
          </div>
          <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto p-3 rounded-xl bg-[var(--color-bg-overlay)] border border-[var(--color-border-secondary)]">
            <div
              v-for="rule in filteredRules"
              :key="rule.id"
              class="hover:bg-[var(--color-bg-card-hover)] p-2 rounded-lg transition"
            >
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="selectedRuleIds.has(rule.id)"
                  class="w-4 h-4 rounded bg-[var(--color-bg-card)] border-[var(--color-border-secondary)] text-[var(--color-accent-primary)] focus:ring-[var(--color-accent-primary)]"
                  @change="toggleRuleSelection(rule.id)"
                >
                <div class="flex-1 text-sm">
                  <span class="text-[var(--color-text-secondary)]">{{ rule.name }}</span>
                  <span class="ml-2 px-1.5 py-0.5 rounded text-xs font-medium" :class="getRuleTypeBadge(rule.ruleType)">
                    {{ rule.ruleType }}
                  </span>
                  <span v-if="canBeDefault(rule)" class="ml-1 text-xs text-[var(--status-pending-text)]">(permanent)</span>
                </div>
              </label>
              <div v-if="selectedRuleIds.has(rule.id) && canBeDefault(rule)" class="ml-6 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="defaultRuleIds.has(rule.id)"
                    class="w-4 h-4 rounded bg-[var(--color-bg-card)] border-[var(--color-accent-secondary)] text-[var(--color-accent-secondary)] focus:ring-[var(--color-accent-secondary)]"
                    @change="defaultRuleIds.has(rule.id) ? defaultRuleIds.delete(rule.id) : defaultRuleIds.add(rule.id)"
                  >
                  <span class="text-xs text-[var(--color-accent-secondary)]">⭐ Set as default (auto-start with playthrough)</span>
                </label>
              </div>
            </div>
            <div v-if="filteredRules.length === 0" class="col-span-full text-center text-[var(--color-text-muted)] text-sm py-4">
              No rules found
            </div>
          </div>
          <p class="text-xs text-[var(--color-text-muted)] mt-1">
            {{ selectedRuleIds.size }} rule{{ selectedRuleIds.size !== 1 ? 's' : '' }} selected
            <span v-if="defaultRuleIds.size > 0" class="text-[var(--color-accent-secondary)]">({{ defaultRuleIds.size }} default)</span>
            <span v-if="ruleSearchQuery" class="text-[var(--color-text-secondary)]">({{ filteredRules.length }} shown)</span>
          </p>
          <p class="text-xs text-[var(--status-pending-text)] mt-1">
            💡 Tip: Only permanent legendary rules can be set as defaults. They'll be active from the start of every playthrough.
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-border-secondary)]">
          <button type="button" class="btn btn-secondary px-6 py-2" @click="handleClose">Cancel</button>
          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary px-6 py-2 font-bold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ editingRuleset ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
