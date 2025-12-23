<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useAdmin, type AdminRule, type CreateRuleRequest, type UpdateRuleRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import RuleFormModal from '~/components/modal/RuleFormModal.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminRules, createRule, updateRule, deleteRule, loading } = useAdmin()

const rules = ref<AdminRule[]>([])
const showModal = ref(false)
const editingRule = ref<AdminRule | null>(null)
const searchQuery = ref('')
const currentPage = ref(1)
const limit = ref(20)
const totalPages = ref(1)
const totalRules = ref(0)

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
  await loadRules()
})

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
  return labels[type] || type
}

const getRuleTypeBadgeColor = (type: string): string => {
  const colors: Record<string, string> = {
    basic: 'bg-blue-500/20 text-blue-400',
    court: 'bg-purple-500/20 text-purple-400',
    legendary: 'bg-yellow-500/20 text-yellow-400'
  }
  return colors[type] || 'bg-gray-500/20 text-gray-400'
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
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
          Admin - Rules
        </h1>
        <p class="text-gray-300">Manage rules for rulesets</p>
      </div>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-2"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Rule
      </button>
    </div>

    <!-- Admin Navigation -->
    <div class="flex gap-3 mb-6">
      <NuxtLink
        to="/admin/categories"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Categories
      </NuxtLink>
      <NuxtLink
        to="/admin/games"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Games
      </NuxtLink>
      <NuxtLink
        to="/admin/rulesets"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Rulesets
      </NuxtLink>
      <NuxtLink
        to="/admin/rules"
        class="px-4 py-2 bg-cyan text-white rounded-lg font-semibold"
      >
        Rules
      </NuxtLink>
      <NuxtLink
        to="/admin/designs"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
      >
        Card Designs
      </NuxtLink>
    </div>

    <!-- Search Bar -->
    <div class="mb-6">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search rules by name or description..."
          class="w-full px-4 py-3 pl-12 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan"
        />
        <Icon name="heroicons:magnifying-glass" class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" />
      </div>
      <p class="text-sm text-gray-400 mt-2">
        Showing {{ rules.length }} of {{ totalRules }} rule{{ totalRules !== 1 ? 's' : '' }}
        <span v-if="searchQuery" class="text-cyan"> (filtered)</span>
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Rules Table -->
    <div v-else class="bg-gray-800/80 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-900 border-b border-gray-700">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Name</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Type</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Difficulty Levels</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Description</th>
            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rule in rules" :key="rule.id" class="border-b border-gray-700 hover:bg-gray-700/50 transition">
            <td class="px-6 py-4 text-white font-medium">{{ rule.name }}</td>
            <td class="px-6 py-4">
              <span :class="['px-2 py-1 rounded text-xs font-semibold', getRuleTypeBadgeColor(rule.ruleType)]">
                {{ getRuleTypeLabel(rule.ruleType) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="level in rule.difficultyLevels"
                  :key="level.difficultyLevel"
                  class="px-2 py-1 bg-gray-700 text-gray-300 rounded text-xs"
                  :title="`Level ${level.difficultyLevel}: ${formatDuration(level.durationMinutes)}`"
                >
                  L{{ level.difficultyLevel }}: {{ formatDuration(level.durationMinutes) }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-300 text-sm">
              <div class="max-w-xs truncate" :title="rule.description || ''">
                {{ rule.description || '-' }}
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openEditModal(rule)"
                  class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(rule)"
                  class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="rules.length === 0" class="text-center py-12 text-gray-400">
        <span v-if="searchQuery">No rules found matching "{{ searchQuery }}"</span>
        <span v-else>No rules found. Create your first rule!</span>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-400">
        Page {{ currentPage }} of {{ totalPages }}
      </div>
      
      <div class="flex gap-2">
        <button
          @click="goToPage(1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 rounded-lg bg-gray-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-600 transition"
        >
          First
        </button>
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 rounded-lg bg-gray-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-600 transition"
        >
          Previous
        </button>
        
        <div class="flex gap-1">
          <button
            v-for="page in getVisiblePages()"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 rounded-lg transition',
              page === currentPage
                ? 'bg-cyan text-white font-bold'
                : 'bg-gray-700 text-white hover:bg-gray-600'
            ]"
          >
            {{ page }}
          </button>
        </div>
        
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 rounded-lg bg-gray-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-600 transition"
        >
          Next
        </button>
        <button
          @click="goToPage(totalPages)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 rounded-lg bg-gray-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-600 transition"
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


