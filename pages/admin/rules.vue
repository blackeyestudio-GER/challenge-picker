<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useAdmin, type AdminRule, type CreateRuleRequest, type UpdateRuleRequest } from '~/composables/useAdmin'
import { useTheme } from '~/composables/useTheme'
import { useIcons, type RuleIcon } from '~/composables/useIcons'
import { Icon } from '#components'
import RuleFormModal from '~/components/modal/RuleFormModal.vue'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import AdminSearchBar from '~/components/admin/AdminSearchBar.vue'
import AdminAddCard from '~/components/admin/AdminAddCard.vue'
import AdminEmptyState from '~/components/admin/AdminEmptyState.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminRules, createRule, updateRule, deleteRule, loading } = useAdmin()
const { getRuleTypeBadgeClass, getRuleTypeName } = useTheme()
const { fetchIcons, loading: iconsLoading } = useIcons()

const rules = ref<AdminRule[]>([])
const icons = ref<RuleIcon[]>([])
const iconsMap = computed<Map<string, RuleIcon>>(() => {
  const map = new Map()
  icons.value.forEach(icon => {
    map.set(icon.identifier, icon)
  })
  return map
})
const showModal = ref(false)
const editingRule = ref<AdminRule | null>(null)
const searchQuery = ref('')
const currentPage = ref(1)
const limit = ref(20)
const totalPages = ref(1)
const totalRules = ref(0)

// Detect if rule is an anti-rule
const isAntiRule = (ruleName: string): boolean => {
  const name = ruleName.toLowerCase().trim()
  return name.startsWith('no ') || name.includes(' no ')
}

// Debounce search
let searchTimeout: NodeJS.Timeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1 // Reset to page 1 on search
    loadRules()
  }, 300)
})

onMounted(async () => {
  await Promise.all([
    loadRules(),
    loadIcons()
  ])
})

const loadIcons = async () => {
  try {
    icons.value = await fetchIcons()
  } catch (err) {
    console.error('Failed to load icons:', err)
  }
}

const loadRules = async () => {
  try {
    const response = await fetchAdminRules(currentPage.value, limit.value, searchQuery.value)
    rules.value = response.rules
    totalPages.value = response.pagination.totalPages
    totalRules.value = response.pagination.total
  } catch (err) {
    console.error('Failed to load rules:', err)
  }
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadRules()
  }
}

const getRuleTypeLabel = (type: string): string => {
  const labels: Record<string, string> = {
    basic: 'Basic (Numbers)',
    court: 'Court (Faces)',
    legendary: 'Legendary (Major)'
  }
  return labels[type.toLowerCase()] || getRuleTypeName(type)
}


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

const getVisiblePages = (): number[] => {
  const pages: number[] = []
  const maxVisible = 5
  
  if (totalPages.value <= maxVisible) {
    for (let i = 1; i <= totalPages.value; i++) {
      pages.push(i)
    }
  } else {
    const start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
    const end = Math.min(totalPages.value, start + maxVisible - 1)
    
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
  }
  
  return pages
}

const openCreateModal = () => {
  editingRule.value = null
  showModal.value = true
}

const openEditModal = (rule: AdminRule) => {
  editingRule.value = rule
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingRule.value = null
}

const handleModalSubmit = async (data: CreateRuleRequest & { id?: number }) => {
  try {
    if (editingRule.value) {
      await updateRule(editingRule.value.id, data)
    } else {
      await createRule(data)
      // Reset to first page when creating new rule
      currentPage.value = 1
    }
    await loadRules()
    closeModal()
  } catch (err) {
    console.error('Failed to save rule:', err)
    alert('Failed to save rule')
  }
}

const handleDelete = async (rule: AdminRule) => {
  if (!confirm(`Are you sure you want to delete "${rule.name}"?`)) return
  
  try {
    await deleteRule(rule.id)
    await loadRules()
  } catch (err) {
    console.error('Failed to delete rule:', err)
    alert('Failed to delete rule')
  }
}

const emptyStateMessage = computed(() => {
  if (searchQuery.value) {
    return `No rules found matching "${searchQuery.value}"`
  }
  return 'No rules found. Create your first rule!'
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <AdminHeader
      title="Rules"
      description="Manage rules for rulesets"
    />

    <!-- Search Bar -->
    <div class="mb-6">
      <AdminSearchBar
        v-model="searchQuery"
        placeholder="Search rules by name or description..."
      />
      <p v-if="totalRules > 0 && !loading" class="mt-2 text-sm text-gray-400">
        Showing {{ rules.length }} of {{ totalRules }} rule{{ totalRules !== 1 ? 's' : '' }}
        <span v-if="searchQuery" class="text-gray-500"> (filtered)</span>
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan mb-4"></div>
      <p class="text-white">Loading rules...</p>
    </div>

    <!-- Empty State (only when no rules and no search) -->
    <AdminEmptyState
      v-else-if="!loading && rules.length === 0 && !searchQuery"
      icon="heroicons:sparkles"
      :message="emptyStateMessage"
      :search-query="searchQuery"
      @clear-search="searchQuery = ''; loadRules()"
    />

    <!-- Rules Grid (always show when not loading, even if empty with search) -->
    <div v-else-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Add New Card (Always First) -->
      <AdminAddCard
        label="Add New Rule"
        @click="openCreateModal"
      />

      <!-- Rule Cards -->
      <div
        v-for="rule in rules"
        :key="rule.id"
        class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 hover:border-cyan rounded-xl p-6 flex flex-col justify-between transition-all min-h-[200px]"
      >
        <div>
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3 flex-1">
              <div v-if="rule.iconIdentifier" class="flex-shrink-0 relative">
                <!-- Icon Container -->
                <div 
                  class="w-8 h-8 flex items-center justify-center admin-rule-icon-container"
                  style="color: #FFFFFF;"
                >
                  <div 
                    v-if="iconsMap.get(rule.iconIdentifier)?.svgContent"
                    v-html="iconsMap.get(rule.iconIdentifier)!.svgContent"
                    class="w-full h-full"
                  ></div>
                  <div v-else class="text-gray-500 text-xs">?</div>
                </div>
                
                <!-- Prohibited Badge Overlay for Anti-Rules -->
                <div 
                  v-if="isAntiRule(rule.name)" 
                  class="absolute -top-1 -right-1 bg-red-600 rounded-full p-0.5 flex items-center justify-center shadow-lg z-10"
                >
                  <Icon
                    name="heroicons:no-symbol"
                    class="w-3 h-3 text-white"
                  />
                </div>
              </div>
              <div v-else class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-gray-700 rounded text-gray-500 text-xs">
                ?
              </div>
              <h3 class="text-xl font-bold text-white flex-1">{{ rule.name }}</h3>
            </div>
            <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getRuleTypeBadgeClass(rule.ruleType)]">
              {{ getRuleTypeLabel(rule.ruleType) }}
            </span>
          </div>

          <p v-if="rule.description" class="text-gray-400 text-sm mb-4 line-clamp-2">
            {{ rule.description }}
          </p>
          <p v-else class="text-gray-500 text-sm italic mb-4">No description</p>

          <div v-if="rule.difficultyLevels.length > 0" class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Difficulty Levels:</div>
            <div class="flex flex-wrap gap-1">
              <span
                v-for="level in rule.difficultyLevels"
                :key="level.difficultyLevel"
                class="px-2 py-1 bg-gray-700 text-gray-300 rounded text-xs"
                :title="'Level ' + level.difficultyLevel + ': ' + formatDuration(level.durationSeconds || 0)"
              >
                L{{ level.difficultyLevel }}: {{ formatDuration(level.durationSeconds || 0) }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex gap-2 mt-4">
          <button
            @click="openEditModal(rule)"
            class="flex-1 px-4 py-2 bg-cyan hover:bg-cyan-dark text-white rounded-lg transition-all flex items-center justify-center gap-2 font-semibold"
          >
            <Icon name="heroicons:pencil" class="w-4 h-4" />
            Edit
          </button>
          <button
            @click="handleDelete(rule)"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all flex items-center justify-center gap-2 font-semibold"
          >
            <Icon name="heroicons:trash" class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && totalPages > 1" class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="text-sm text-gray-400">
        Page {{ currentPage }} of {{ totalPages }}
      </div>
      
      <div class="flex items-center gap-2">
        <button
          @click="goToPage(1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold"
        >
          First
        </button>
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold"
        >
          Previous
        </button>
        
        <div class="flex items-center gap-1">
          <button
            v-for="page in getVisiblePages()"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 rounded-lg transition-all text-sm font-semibold',
              page === currentPage
                ? 'bg-cyan text-white'
                : 'bg-gray-800 hover:bg-gray-700 text-white'
            ]"
          >
            {{ page }}
          </button>
        </div>
        
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold"
        >
          Next
        </button>
        <button
          @click="goToPage(totalPages)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold"
        >
          Last
        </button>
      </div>
    </div>

    <!-- Rule Form Modal -->
    <RuleFormModal
      :show="showModal"
      :editing-rule="editingRule"
      :loading="loading"
      @close="closeModal"
      @submit="handleModalSubmit"
    />
  </div>
</template>

<style scoped>
.admin-rule-icon-container {
  overflow: visible;
  display: flex;
  align-items: center;
  justify-content: center;
}

.admin-rule-icon-container :deep(svg) {
  width: 100%;
  height: 100%;
  display: block;
  max-width: 100%;
  max-height: 100%;
  flex-shrink: 0;
  color: inherit;
}

/* Force all SVG elements to use currentColor */
.admin-rule-icon-container :deep(svg),
.admin-rule-icon-container :deep(svg *),
.admin-rule-icon-container :deep(svg path),
.admin-rule-icon-container :deep(svg circle),
.admin-rule-icon-container :deep(svg rect),
.admin-rule-icon-container :deep(svg polygon),
.admin-rule-icon-container :deep(svg ellipse),
.admin-rule-icon-container :deep(svg g),
.admin-rule-icon-container :deep(svg g *),
.admin-rule-icon-container :deep(svg g path),
.admin-rule-icon-container :deep(svg g circle),
.admin-rule-icon-container :deep(svg g rect) {
  fill: currentColor !important;
  stroke: currentColor !important;
  color: inherit !important;
}

/* Special handling for elements that should only have stroke */
.admin-rule-icon-container :deep(svg line),
.admin-rule-icon-container :deep(svg polyline),
.admin-rule-icon-container :deep(svg g line),
.admin-rule-icon-container :deep(svg g polyline) {
  stroke: currentColor !important;
  fill: none !important;
}
</style>

