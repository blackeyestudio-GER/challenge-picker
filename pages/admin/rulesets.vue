<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAdmin, type AdminRuleset, type CreateRulesetRequest, type UpdateRulesetRequest, type AdminRule } from '~/composables/useAdmin'
import { useTheme } from '~/composables/useTheme'
import { Icon } from '#components'
import RulesetFormModal from '~/components/modal/RulesetFormModal.vue'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import AdminSearchBar from '~/components/admin/AdminSearchBar.vue'
import AdminAddCard from '~/components/admin/AdminAddCard.vue'
import AdminEmptyState from '~/components/admin/AdminEmptyState.vue'
import AdminAutocomplete from '~/components/admin/AdminAutocomplete.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchGameNames, fetchAdminRulesets, fetchAdminRules, createRuleset, updateRuleset, deleteRuleset, loading } = useAdmin()
const { getRuleTypeBadge } = useTheme()

const games = ref<{ id: number; name: string }[]>([])
const allRulesets = ref<AdminRuleset[]>([])
const allRules = ref<AdminRule[]>([])
const showModal = ref(false)
const editingRuleset = ref<AdminRuleset | null>(null)
const searchQuery = ref('')

// Advanced filters
const selectedRuleIds = ref<Set<number>>(new Set())
const selectedGameId = ref<number | null>(null)
const showLegendaryRules = ref(false)
const showCourtRules = ref(false)
const showBasicRules = ref(false)

onMounted(async () => {
  await Promise.all([loadGames(), loadRulesets(), loadAllRules()])
})


const loadGames = async () => {
  try {
    // Load ALL games (no pagination) for autocomplete
    games.value = await fetchGameNames()
  } catch (err) {
    console.error('Failed to load games:', err)
  }
}

const loadRulesets = async () => {
  try {
    allRulesets.value = await fetchAdminRulesets()
  } catch (err) {
    console.error('Failed to load rulesets:', err)
  }
}

const loadAllRules = async () => {
  try {
    const response = await fetchAdminRules(1, 1000)
    allRules.value = response.rules
  } catch (err) {
    console.error('Failed to load rules:', err)
  }
}

const filteredRulesets = computed(() => {
  let filtered = allRulesets.value

  // Text search filter (name and description only)
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(ruleset => {
      const nameMatch = ruleset.name.toLowerCase().includes(query)
      const descMatch = ruleset.description?.toLowerCase().includes(query)
      return nameMatch || descMatch
    })
  }

  // Rule filter (must include ALL selected rules)
  if (selectedRuleIds.value.size > 0) {
    filtered = filtered.filter(ruleset => {
      const defaultRules = Array.isArray(ruleset.defaultRules) ? ruleset.defaultRules : []
      const rulesetRuleIds = new Set(defaultRules.map(r => r.id))
      return Array.from(selectedRuleIds.value).every(ruleId => rulesetRuleIds.has(ruleId))
    })
  }

  // Game filter
  if (selectedGameId.value !== null) {
    filtered = filtered.filter(ruleset => 
      ruleset.games.some(game => game.id === selectedGameId.value)
    )
  }

  return filtered
})


const hasActiveFilters = computed(() => {
  return searchQuery.value.trim() !== '' || 
         selectedRuleIds.value.size > 0 || 
         selectedGameId.value !== null
})

// Group rules by type
const legendaryRules = computed(() => allRules.value.filter(r => r.ruleType === 'legendary'))
const courtRules = computed(() => allRules.value.filter(r => r.ruleType === 'court'))
const basicRules = computed(() => allRules.value.filter(r => r.ruleType === 'basic'))

const toggleRule = (ruleId: number) => {
  const newSet = new Set(selectedRuleIds.value)
  if (newSet.has(ruleId)) {
    newSet.delete(ruleId)
  } else {
    newSet.add(ruleId)
  }
  selectedRuleIds.value = newSet
}

const clearRuleFilters = () => {
  selectedRuleIds.value = new Set()
}

const openCreateModal = () => {
  editingRuleset.value = null
  showModal.value = true
}

const openEditModal = (ruleset: AdminRuleset) => {
  editingRuleset.value = ruleset
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingRuleset.value = null
}

const handleModalSubmit = async (data: CreateRulesetRequest & { id?: number }) => {
  try {
    if (editingRuleset.value) {
      await updateRuleset(editingRuleset.value.id, data)
    } else {
      await createRuleset(data)
    }
    await loadRulesets()
    closeModal()
  } catch (err) {
    console.error('Failed to save ruleset:', err)
    alert('Failed to save ruleset')
  }
}

const handleDelete = async (ruleset: AdminRuleset) => {
  if (!confirm(`Are you sure you want to delete "${ruleset.name}"?`)) return
  
  try {
    await deleteRuleset(ruleset.id)
    await loadRulesets()
  } catch (err) {
    console.error('Failed to delete ruleset:', err)
    alert('Failed to delete ruleset')
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <AdminHeader 
      title="Manage Rulesets"
      description="Create and manage rule collections for games"
    />

    <!-- Search Bar -->
    <AdminSearchBar
      v-model="searchQuery"
      placeholder="Search rulesets by name or description..."
    />

    <!-- Filters Row -->
    <div class="mb-6 flex gap-4 items-end">
      <!-- Game Filter -->
      <div class="flex-1 min-w-[300px]">
        <AdminAutocomplete
          v-model="selectedGameId"
          :options="games"
          label="Filter by Game"
          placeholder="Type to search games..."
          :nullable="true"
          all-option-label="All Games"
          empty-message="No games found"
        />
      </div>

      <!-- Active Filters Summary -->
      <div v-if="hasActiveFilters" class="flex items-center gap-3 px-4 py-2 bg-gray-800/50 border border-cyan/30 rounded-lg text-sm whitespace-nowrap">
        <Icon name="heroicons:funnel" class="w-5 h-5 text-cyan" />
        <span class="text-gray-300">
          <span class="font-semibold text-white">{{ filteredRulesets.length }}</span> 
          {{ filteredRulesets.length === 1 ? 'result' : 'results' }}
        </span>
      </div>
    </div>

    <!-- Rule Type Filters -->
    <div v-if="allRules.length > 0" class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <Icon name="heroicons:funnel" class="w-5 h-5 text-gray-400" />
          <h3 class="text-sm font-semibold text-gray-300">
            Filter by Rules
            <span class="text-gray-500 text-xs font-normal ml-2">
              ({{ selectedRuleIds.size }} selected)
            </span>
          </h3>
        </div>
        <button
          v-if="selectedRuleIds.size > 0"
          @click="clearRuleFilters"
          class="px-3 py-1.5 text-xs bg-red-600/20 border border-red-600 rounded text-red-400 hover:bg-red-600/30 transition flex items-center gap-1"
        >
          <Icon name="heroicons:x-mark" class="w-4 h-4" />
          Clear All
        </button>
      </div>

      <!-- Rules by Type -->
      <div class="space-y-3">
        <!-- Legendary Rules -->
        <div v-if="legendaryRules.length > 0" class="border border-purple-700/30 rounded-lg overflow-hidden">
          <button
            @click="showLegendaryRules = !showLegendaryRules"
            class="w-full px-4 py-3 bg-purple-900/20 hover:bg-purple-900/30 transition flex items-center justify-between group"
          >
            <div class="flex items-center gap-2">
              <Icon name="heroicons:star" class="w-5 h-5 text-purple-400" />
              <h4 class="text-sm font-semibold text-purple-300 uppercase">
                Legendary Rules
              </h4>
              <span class="text-xs text-purple-400/70">
                ({{ legendaryRules.filter(r => selectedRuleIds.has(r.id)).length }} / {{ legendaryRules.length }} selected)
              </span>
            </div>
            <Icon 
              :name="showLegendaryRules ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-purple-400 transition" 
            />
          </button>
          <div v-if="showLegendaryRules" class="p-4 bg-purple-900/10">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="rule in legendaryRules"
                :key="rule.id"
                @click="toggleRule(rule.id)"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                  selectedRuleIds.has(rule.id)
                    ? 'bg-purple-600 border-purple-400 text-white shadow-lg shadow-purple-500/50'
                    : 'bg-purple-900/30 border-purple-700/50 text-purple-300 hover:bg-purple-900/50 hover:border-purple-600'
                ]"
              >
                {{ rule.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Court Rules -->
        <div v-if="courtRules.length > 0" class="border border-yellow-700/30 rounded-lg overflow-hidden">
          <button
            @click="showCourtRules = !showCourtRules"
            class="w-full px-4 py-3 bg-yellow-900/20 hover:bg-yellow-900/30 transition flex items-center justify-between group"
          >
            <div class="flex items-center gap-2">
              <Icon name="heroicons:user-group" class="w-5 h-5 text-yellow-400" />
              <h4 class="text-sm font-semibold text-yellow-300 uppercase">
                Court Rules
              </h4>
              <span class="text-xs text-yellow-400/70">
                ({{ courtRules.filter(r => selectedRuleIds.has(r.id)).length }} / {{ courtRules.length }} selected)
              </span>
            </div>
            <Icon 
              :name="showCourtRules ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-yellow-400 transition" 
            />
          </button>
          <div v-if="showCourtRules" class="p-4 bg-yellow-900/10">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="rule in courtRules"
                :key="rule.id"
                @click="toggleRule(rule.id)"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                  selectedRuleIds.has(rule.id)
                    ? 'bg-yellow-600 border-yellow-400 text-white shadow-lg shadow-yellow-500/50'
                    : 'bg-yellow-900/30 border-yellow-700/50 text-yellow-300 hover:bg-yellow-900/50 hover:border-yellow-600'
                ]"
              >
                {{ rule.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Basic Rules -->
        <div v-if="basicRules.length > 0" class="border border-blue-700/30 rounded-lg overflow-hidden">
          <button
            @click="showBasicRules = !showBasicRules"
            class="w-full px-4 py-3 bg-blue-900/20 hover:bg-blue-900/30 transition flex items-center justify-between group"
          >
            <div class="flex items-center gap-2">
              <Icon name="heroicons:squares-2x2" class="w-5 h-5 text-blue-400" />
              <h4 class="text-sm font-semibold text-blue-300 uppercase">
                Basic Rules
              </h4>
              <span class="text-xs text-blue-400/70">
                ({{ basicRules.filter(r => selectedRuleIds.has(r.id)).length }} / {{ basicRules.length }} selected)
              </span>
            </div>
            <Icon 
              :name="showBasicRules ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-blue-400 transition" 
            />
          </button>
          <div v-if="showBasicRules" class="p-4 bg-blue-900/10">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="rule in basicRules"
                :key="rule.id"
                @click="toggleRule(rule.id)"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                  selectedRuleIds.has(rule.id)
                    ? 'bg-blue-600 border-blue-400 text-white shadow-lg shadow-blue-500/50'
                    : 'bg-blue-900/30 border-blue-700/50 text-blue-300 hover:bg-blue-900/50 hover:border-blue-600'
                ]"
              >
                {{ rule.name }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Empty State -->
    <AdminEmptyState
      v-else-if="allRulesets.length === 0"
      icon="heroicons:rectangle-stack"
      message="No rulesets created yet. Create your first ruleset to get started!"
      button-text="Create Ruleset"
      @button-click="openCreateModal"
    />

    <!-- No Results State -->
    <AdminEmptyState
      v-else-if="filteredRulesets.length === 0"
      icon="heroicons:magnifying-glass"
      message="No rulesets match your filters. Try adjusting your search criteria."
    />

    <!-- Rulesets Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Add New Card -->
      <AdminAddCard
        title="Create New Ruleset"
        description="Add a new rule collection"
        icon="heroicons:plus"
        @click="openCreateModal"
      />

      <!-- Ruleset Cards -->
      <div
        v-for="ruleset in filteredRulesets"
        :key="ruleset.id"
        class="bg-gradient-to-br from-gray-800/90 to-gray-900/90 backdrop-blur-sm rounded-lg border border-gray-700 hover:border-cyan transition-all p-6 flex flex-col justify-between"
      >
        <!-- Card Header -->
        <div class="mb-4">
          <h3 class="text-xl font-bold text-white mb-2">{{ ruleset.name }}</h3>
          <p v-if="ruleset.description" class="text-sm text-gray-400 mb-3">
            {{ ruleset.description }}
          </p>

          <!-- Games -->
          <div class="mb-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Games ({{ ruleset.games.length }})</h4>
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="game in ruleset.games.slice(0, 3)" 
                :key="game.id"
                class="inline-block px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded"
              >
                {{ game.name }}
              </span>
              <span
                v-if="ruleset.games.length > 3"
                class="inline-block px-2 py-1 bg-gray-700 text-gray-400 text-xs rounded"
              >
                +{{ ruleset.games.length - 3 }} more
              </span>
              <span v-if="ruleset.games.length === 0" class="text-gray-500 text-xs italic">
                No games assigned
              </span>
            </div>
          </div>

          <!-- Default Rules -->
          <div class="mb-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">
              Default Rules ({{ Array.isArray(ruleset.defaultRules) ? ruleset.defaultRules.length : 0 }})
            </h4>
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="rule in (Array.isArray(ruleset.defaultRules) ? ruleset.defaultRules.slice(0, 4) : [])" 
                :key="rule.id"
                class="inline-block px-2 py-1 text-xs rounded"
                :class="getRuleTypeBadge(rule.ruleType)"
              >
                {{ rule.name }}
              </span>
              <span
                v-if="Array.isArray(ruleset.defaultRules) && ruleset.defaultRules.length > 4"
                class="inline-block px-2 py-1 bg-gray-700 text-gray-400 text-xs rounded"
              >
                +{{ ruleset.defaultRules.length - 4 }} more
              </span>
              <span v-if="!Array.isArray(ruleset.defaultRules) || ruleset.defaultRules.length === 0" class="text-gray-500 text-xs italic">
                No default rules
              </span>
            </div>
          </div>

          <!-- Stats -->
          <div class="flex items-center gap-4 text-sm text-gray-400 mt-3">
            <div class="flex items-center gap-1">
              <Icon name="heroicons:rectangle-stack" class="w-4 h-4" />
              <span>{{ ruleset.ruleCount }} total rules</span>
            </div>
          </div>
        </div>

        <!-- Card Actions -->
        <div class="flex gap-2 pt-4 border-t border-gray-700">
          <button
            @click="openEditModal(ruleset)"
            class="flex-1 px-4 py-2 bg-cyan hover:bg-cyan-dark text-white rounded-lg transition-all flex items-center justify-center gap-2"
          >
            <Icon name="heroicons:pencil" class="w-4 h-4" />
            Edit
          </button>
          <button
            @click="handleDelete(ruleset)"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all"
          >
            <Icon name="heroicons:trash" class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Ruleset Form Modal -->
    <RulesetFormModal
      :show="showModal"
      :editing-ruleset="editingRuleset"
      :games="games"
      :loading="loading"
      @close="closeModal"
      @submit="handleModalSubmit"
    />
  </div>
</template>

