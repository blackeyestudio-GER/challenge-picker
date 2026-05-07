<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAdmin, type AdminRuleset, type CreateRulesetRequest, type AdminRule } from '~/composables/useAdmin'
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
const { success, notifyApiError } = useNotify()
const { getRuleTypeBadge } = useTheme()

const games = ref<{ id: number; name: string }[]>([])
const allRulesets = ref<AdminRuleset[]>([])
const allRules = ref<AdminRule[]>([])
const showModal = ref(false)
const editingRuleset = ref<AdminRuleset | null>(null)
const searchQuery = ref('')
const errorMessage = ref('')

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
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to load games')
  }
}

const loadRulesets = async () => {
  errorMessage.value = ''
  try {
    allRulesets.value = await fetchAdminRulesets()
  } catch (err: unknown) {
    errorMessage.value = 'Failed to load rulesets'
    notifyApiError(err, 'Failed to load rulesets')
  }
}

const loadAllRules = async () => {
  try {
    const response = await fetchAdminRules(1, 1000)
    allRules.value = response.rules
  } catch (err: unknown) {
    notifyApiError(err, 'Failed to load rules')
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
    success(editingRuleset.value ? 'Ruleset updated' : 'Ruleset created')
  } catch (err) {
    notifyApiError(err, 'Failed to save ruleset')
  }
}

const handleDelete = async (ruleset: AdminRuleset) => {
  if (!confirm(`Are you sure you want to delete "${ruleset.name}"?`)) return
  
  try {
    await deleteRuleset(ruleset.id)
    await loadRulesets()
    success('Ruleset deleted')
  } catch (err) {
    notifyApiError(err, 'Failed to delete ruleset')
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
      <div v-if="hasActiveFilters" class="flex items-center gap-3 px-4 py-2 admin-panel text-sm whitespace-nowrap">
        <Icon name="heroicons:funnel" class="w-5 h-5 text-[var(--color-accent-primary)]" />
        <span class="admin-text-muted">
          <span class="font-semibold text-[var(--color-text-primary)]">{{ filteredRulesets.length }}</span> 
          {{ filteredRulesets.length === 1 ? 'result' : 'results' }}
        </span>
      </div>
    </div>

    <!-- Rule Type Filters -->
    <div v-if="allRules.length > 0" class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <Icon name="heroicons:funnel" class="w-5 h-5 admin-text-subtle" />
          <h3 class="text-sm font-semibold admin-text-muted">
            Filter by Rules
            <span class="admin-text-subtle text-xs font-normal ml-2">
              ({{ selectedRuleIds.size }} selected)
            </span>
          </h3>
        </div>
        <button
          v-if="selectedRuleIds.size > 0"
          class="admin-badge admin-badge--danger px-3 py-1.5 text-xs flex items-center gap-1"
          @click="clearRuleFilters"
        >
          <Icon name="heroicons:x-mark" class="w-4 h-4" />
          Clear All
        </button>
      </div>

      <!-- Rules by Type -->
      <div class="space-y-3">
        <!-- Legendary Rules -->
        <div v-if="legendaryRules.length > 0" class="admin-panel overflow-hidden">
          <button
            class="w-full px-4 py-3 admin-rulesets-page__section admin-rulesets-page__section--legendary transition flex items-center justify-between group"
            @click="showLegendaryRules = !showLegendaryRules"
          >
            <div class="flex items-center gap-2">
              <Icon name="heroicons:star" class="w-5 h-5 text-[var(--color-accent-secondary)]" />
              <h4 class="text-sm font-semibold text-[var(--color-accent-secondary)] uppercase">
                Legendary Rules
              </h4>
              <span class="text-xs text-[var(--color-accent-secondary)]/70">
                ({{ legendaryRules.filter(r => selectedRuleIds.has(r.id)).length }} / {{ legendaryRules.length }} selected)
              </span>
            </div>
            <Icon 
              :name="showLegendaryRules ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-[var(--color-accent-secondary)] transition" 
            />
          </button>
          <div v-if="showLegendaryRules" class="p-4 admin-rulesets-page__section-body">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="rule in legendaryRules"
                :key="rule.id"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                  selectedRuleIds.has(rule.id)
                    ? 'bg-[var(--color-accent-secondary)] border-[var(--color-accent-secondary)] text-white shadow-lg'
                    : 'bg-[color-mix(in_srgb,var(--color-accent-secondary)_12%,transparent)] border-[color-mix(in_srgb,var(--color-accent-secondary)_34%,transparent)] text-[var(--color-accent-secondary)] hover:bg-[color-mix(in_srgb,var(--color-accent-secondary)_18%,transparent)] hover:border-[var(--color-accent-secondary)]'
                ]"
                @click="toggleRule(rule.id)"
              >
                {{ rule.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Court Rules -->
        <div v-if="courtRules.length > 0" class="admin-panel overflow-hidden">
          <button
            class="w-full px-4 py-3 admin-rulesets-page__section admin-rulesets-page__section--court transition flex items-center justify-between group"
            @click="showCourtRules = !showCourtRules"
          >
            <div class="flex items-center gap-2">
              <Icon name="heroicons:user-group" class="w-5 h-5 text-[var(--status-pending-text)]" />
              <h4 class="text-sm font-semibold text-[var(--status-pending-text)] uppercase">
                Court Rules
              </h4>
              <span class="text-xs text-[var(--status-pending-text)]/70">
                ({{ courtRules.filter(r => selectedRuleIds.has(r.id)).length }} / {{ courtRules.length }} selected)
              </span>
            </div>
            <Icon 
              :name="showCourtRules ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-[var(--status-pending-text)] transition" 
            />
          </button>
          <div v-if="showCourtRules" class="p-4 admin-rulesets-page__section-body">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="rule in courtRules"
                :key="rule.id"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                  selectedRuleIds.has(rule.id)
                    ? 'bg-[var(--status-pending-text)] border-[var(--status-pending-text)] text-white shadow-lg'
                    : 'bg-[color-mix(in_srgb,var(--status-pending-text)_12%,transparent)] border-[color-mix(in_srgb,var(--status-pending-text)_34%,transparent)] text-[var(--status-pending-text)] hover:bg-[color-mix(in_srgb,var(--status-pending-text)_18%,transparent)] hover:border-[var(--status-pending-text)]'
                ]"
                @click="toggleRule(rule.id)"
              >
                {{ rule.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Basic Rules -->
        <div v-if="basicRules.length > 0" class="admin-panel overflow-hidden">
          <button
            class="w-full px-4 py-3 admin-rulesets-page__section admin-rulesets-page__section--basic transition flex items-center justify-between group"
            @click="showBasicRules = !showBasicRules"
          >
            <div class="flex items-center gap-2">
              <Icon name="heroicons:squares-2x2" class="w-5 h-5 text-[var(--color-accent-primary)]" />
              <h4 class="text-sm font-semibold text-[var(--color-accent-primary)] uppercase">
                Basic Rules
              </h4>
              <span class="text-xs text-[var(--color-accent-primary)]/70">
                ({{ basicRules.filter(r => selectedRuleIds.has(r.id)).length }} / {{ basicRules.length }} selected)
              </span>
            </div>
            <Icon 
              :name="showBasicRules ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-[var(--color-accent-primary)] transition" 
            />
          </button>
          <div v-if="showBasicRules" class="p-4 admin-rulesets-page__section-body">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="rule in basicRules"
                :key="rule.id"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                  selectedRuleIds.has(rule.id)
                    ? 'bg-[var(--color-accent-primary)] border-[var(--color-accent-primary)] text-white shadow-lg'
                    : 'bg-[color-mix(in_srgb,var(--color-accent-primary)_12%,transparent)] border-[color-mix(in_srgb,var(--color-accent-primary)_34%,transparent)] text-[var(--color-accent-primary)] hover:bg-[color-mix(in_srgb,var(--color-accent-primary)_18%,transparent)] hover:border-[var(--color-accent-primary)]'
                ]"
                @click="toggleRule(rule.id)"
              >
                {{ rule.name }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading && allRulesets.length === 0" message="Loading rulesets..." />

    <ErrorState v-else-if="errorMessage && allRulesets.length === 0" :message="errorMessage" />

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
        class="admin-panel admin-panel--interactive p-6 flex flex-col justify-between"
      >
        <!-- Card Header -->
        <div class="mb-4">
          <h3 class="text-xl font-bold text-[var(--color-text-primary)] mb-2">{{ ruleset.name }}</h3>
          <p v-if="ruleset.description" class="text-sm admin-text-muted mb-3">
            {{ ruleset.description }}
          </p>

          <!-- Games -->
          <div class="mb-3">
            <h4 class="text-xs font-semibold admin-text-subtle uppercase mb-2">Games ({{ ruleset.games.length }})</h4>
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="game in ruleset.games.slice(0, 3)" 
                :key="game.id"
                class="inline-block px-2 py-1 admin-badge admin-badge--secondary"
              >
                {{ game.name }}
              </span>
              <span
                v-if="ruleset.games.length > 3"
                class="inline-block px-2 py-1 admin-badge admin-badge--secondary"
              >
                +{{ ruleset.games.length - 3 }} more
              </span>
              <span v-if="ruleset.games.length === 0" class="admin-text-subtle text-xs italic">
                No games assigned
              </span>
            </div>
          </div>

          <!-- Default Rules -->
          <div class="mb-3">
            <h4 class="text-xs font-semibold admin-text-subtle uppercase mb-2">
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
                class="inline-block px-2 py-1 admin-badge admin-badge--secondary"
              >
                +{{ ruleset.defaultRules.length - 4 }} more
              </span>
              <span v-if="!Array.isArray(ruleset.defaultRules) || ruleset.defaultRules.length === 0" class="admin-text-subtle text-xs italic">
                No default rules
              </span>
            </div>
          </div>

          <!-- Stats -->
          <div class="flex items-center gap-4 text-sm admin-text-muted mt-3">
            <div class="flex items-center gap-1">
              <Icon name="heroicons:rectangle-stack" class="w-4 h-4" />
              <span>{{ ruleset.ruleCount }} total rules</span>
            </div>
          </div>
        </div>

        <!-- Card Actions -->
        <div class="flex gap-2 pt-4 border-t border-[var(--color-border-secondary)]">
          <button
            class="btn btn-primary flex-1 flex items-center justify-center gap-2"
            @click="openEditModal(ruleset)"
          >
            <Icon name="heroicons:pencil" class="w-4 h-4" />
            Edit
          </button>
          <button
            class="btn btn-danger"
            @click="handleDelete(ruleset)"
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

<style scoped>
.admin-rulesets-page__section {
  background-color: color-mix(in srgb, var(--color-bg-overlay) 88%, transparent);
}

.admin-rulesets-page__section--legendary {
  border-bottom: 1px solid color-mix(in srgb, var(--color-accent-secondary) 22%, var(--color-border-secondary));
}

.admin-rulesets-page__section--court {
  border-bottom: 1px solid color-mix(in srgb, var(--status-pending-border) 62%, var(--color-border-secondary));
}

.admin-rulesets-page__section--basic {
  border-bottom: 1px solid color-mix(in srgb, var(--status-completed-border) 62%, var(--color-border-secondary));
}

.admin-rulesets-page__section-body {
  background-color: color-mix(in srgb, var(--color-bg-card) 95%, transparent);
}
</style>
