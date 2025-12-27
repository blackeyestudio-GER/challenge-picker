<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import type { AdminRuleset, CreateRulesetRequest, AdminRule } from '~/composables/useAdmin'
import { useAdmin } from '~/composables/useAdmin'
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

const { fetchAdminRules } = useAdmin()

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
    console.error('Failed to load rules:', err)
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
    alert('Please select at least one game')
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
  <div v-if="show" class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4" @click.self="handleClose">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full border border-gray-700 max-h-[90vh] flex flex-col">
      <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-white">
          {{ editingRuleset ? 'Edit Ruleset' : 'Create Ruleset' }}
        </h2>
        <button @click="handleClose" class="text-gray-400 hover:text-white">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4 overflow-y-auto">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
            placeholder="e.g., Classic Tank Controls"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
            placeholder="Describe what makes this ruleset unique..."
          ></textarea>
        </div>
        
        <!-- Games Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Games *
            <span class="text-xs text-gray-500 font-normal ml-2">(Select all games this ruleset applies to)</span>
          </label>
          
          <!-- Search Input -->
          <div class="mb-2">
            <input
              v-model="gameSearchQuery"
              type="text"
              placeholder="Search games..."
              class="w-full px-3 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
            />
          </div>
          
          <!-- Games Checkboxes -->
          <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-3 bg-gray-900 rounded-lg border border-gray-600">
            <label
              v-for="game in filteredGames"
              :key="game.id"
              class="flex items-center gap-2 cursor-pointer hover:bg-gray-800 p-2 rounded transition"
              :class="{ 'opacity-50': game.isActive === false }"
            >
              <input
                type="checkbox"
                :value="game.id"
                v-model="formData.gameIds"
                class="w-4 h-4 rounded bg-gray-900 border-gray-600 text-cyan focus:ring-cyan"
              />
              <span class="text-sm text-gray-300 truncate" :title="game.name">
                {{ game.name }}
                <span v-if="game.isActive === false" class="text-xs text-gray-500">(inactive)</span>
              </span>
            </label>
            <div v-if="filteredGames.length === 0" class="col-span-full text-center text-gray-500 text-sm py-4">
              No games found
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">
            {{ formData.gameIds?.length || 0 }} game{{ (formData.gameIds?.length || 0) !== 1 ? 's' : '' }} selected
            <span v-if="gameSearchQuery" class="text-gray-400">({{ filteredGames.length }} shown)</span>
          </p>
        </div>
        
        <!-- Rules Assignment -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Rules in this Ruleset
            <span class="text-xs text-gray-500 font-normal ml-2">(Select which rules are available in this ruleset)</span>
          </label>
          
          <!-- Search Input -->
          <div class="mb-2">
            <input
              v-model="ruleSearchQuery"
              type="text"
              placeholder="Search rules..."
              class="w-full px-3 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan text-sm"
            />
          </div>
          
          <!-- Rules Checkboxes with Nested Default Checkboxes -->
          <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto p-3 bg-gray-900 rounded-lg border border-gray-600">
            <div
              v-for="rule in filteredRules"
              :key="rule.id"
              class="hover:bg-gray-800 p-2 rounded transition"
            >
              <!-- Main Rule Checkbox -->
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="selectedRuleIds.has(rule.id)"
                  @change="toggleRuleSelection(rule.id)"
                  class="w-4 h-4 rounded bg-gray-900 border-gray-600 text-cyan focus:ring-cyan"
                />
                <div class="flex-1 text-sm">
                  <span class="text-gray-300">{{ rule.name }}</span>
                  <span 
                    class="ml-2 px-1.5 py-0.5 rounded text-xs font-medium"
                    :class="{
                      'bg-purple-900/50 text-purple-300': rule.ruleType === 'legendary',
                      'bg-yellow-900/50 text-yellow-300': rule.ruleType === 'court',
                      'bg-blue-900/50 text-blue-300': rule.ruleType === 'basic'
                    }"
                  >
                    {{ rule.ruleType }}
                  </span>
                  <span v-if="canBeDefault(rule)" class="ml-1 text-xs text-purple-400">
                    (permanent)
                  </span>
                </div>
              </label>
              
              <!-- Nested Default Checkbox (only for permanent legendary rules) -->
              <div v-if="selectedRuleIds.has(rule.id) && canBeDefault(rule)" class="ml-6 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="defaultRuleIds.has(rule.id)"
                    @change="defaultRuleIds.has(rule.id) ? defaultRuleIds.delete(rule.id) : defaultRuleIds.add(rule.id)"
                    class="w-4 h-4 rounded bg-gray-800 border-purple-600 text-purple-600 focus:ring-purple-500"
                  />
                  <span class="text-xs text-purple-300">
                    ⭐ Set as default (auto-start with playthrough)
                  </span>
                </label>
              </div>
            </div>
            
            <div v-if="filteredRules.length === 0" class="col-span-full text-center text-gray-500 text-sm py-4">
              No rules found
            </div>
          </div>
          
          <p class="text-xs text-gray-500 mt-1">
            {{ selectedRuleIds.size }} rule{{ selectedRuleIds.size !== 1 ? 's' : '' }} selected
            <span v-if="defaultRuleIds.size > 0" class="text-purple-400">
              ({{ defaultRuleIds.size }} default)
            </span>
            <span v-if="ruleSearchQuery" class="text-gray-400">
              ({{ filteredRules.length }} shown)
            </span>
          </p>
          <p class="text-xs text-amber-400 mt-1">
            💡 Tip: Only permanent legendary rules can be set as defaults. They'll be active from the start of every playthrough.
          </p>
        </div>
        
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
            {{ editingRuleset ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

