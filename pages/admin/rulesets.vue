<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdmin, type AdminGame, type AdminRuleset, type CreateRulesetRequest, type UpdateRulesetRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import RulesetFormModal from '~/components/modal/RulesetFormModal.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminGames, fetchAdminRulesets, createRuleset, updateRuleset, deleteRuleset, loading } = useAdmin()

const games = ref<AdminGame[]>([])
const rulesets = ref<AdminRuleset[]>([])
const allRulesets = ref<AdminRuleset[]>([])
const showModal = ref(false)
const editingRuleset = ref<AdminRuleset | null>(null)
const searchQuery = ref('')

onMounted(async () => {
  await Promise.all([loadGames(), loadRulesets()])
})

const loadGames = async () => {
  try {
    const response = await fetchAdminGames()
    games.value = response.games
  } catch (err) {
    console.error('Failed to load games:', err)
  }
}

const loadRulesets = async () => {
  try {
    allRulesets.value = await fetchAdminRulesets()
    filterRulesets()
  } catch (err) {
    console.error('Failed to load rulesets:', err)
  }
}

const filterRulesets = () => {
  if (!searchQuery.value.trim()) {
    rulesets.value = allRulesets.value
    return
  }
  
  const query = searchQuery.value.toLowerCase()
  rulesets.value = allRulesets.value.filter(ruleset => {
    const nameMatch = ruleset.name.toLowerCase().includes(query)
    const descMatch = ruleset.description?.toLowerCase().includes(query)
    const gameMatch = ruleset.games.some(game => game.name.toLowerCase().includes(query))
    return nameMatch || descMatch || gameMatch
  })
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
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
          Admin - Rulesets
        </h1>
        <p class="text-gray-300">Manage rulesets for games</p>
      </div>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-2"
      >
        <Icon name="heroicons:plus" class="w-5 h-5" />
        Create Ruleset
      </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-6">
      <div class="relative">
        <Icon name="heroicons:magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input
          v-model="searchQuery"
          @input="filterRulesets"
          type="text"
          placeholder="Search rulesets by name, description, or game..."
          class="w-full pl-12 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-cyan transition"
        />
        <button
          v-if="searchQuery"
          @click="searchQuery = ''; filterRulesets()"
          class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition"
        >
          <Icon name="heroicons:x-mark" class="w-5 h-5" />
        </button>
      </div>
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
        class="px-4 py-2 bg-cyan text-white rounded-lg font-semibold"
      >
        Rulesets
      </NuxtLink>
      <NuxtLink
        to="/admin/rules"
        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
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

    <!-- Search Results Info -->
    <div v-if="searchQuery && !loading" class="mb-4 text-gray-300">
      Found {{ rulesets.length }} ruleset{{ rulesets.length === 1 ? '' : 's' }}
      <button @click="searchQuery = ''; filterRulesets()" class="ml-2 text-cyan hover:underline">
        Clear search
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
      <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Rulesets Table -->
    <div v-else class="bg-gray-800/80 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-900 border-b border-gray-700">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Name</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Games</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Default Rules</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Description</th>
            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Rules</th>
            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ruleset in rulesets" :key="ruleset.id" class="border-b border-gray-700 hover:bg-gray-700/50 transition">
            <td class="px-6 py-4 text-white font-medium">{{ ruleset.name }}</td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="game in ruleset.games" 
                  :key="game.id"
                  class="inline-block px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded"
                  :title="game.name"
                >
                  {{ game.name }}
                </span>
                <span v-if="ruleset.games.length === 0" class="text-gray-500 text-sm">No games</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="rule in ruleset.defaultRules" 
                  :key="rule.id"
                  class="inline-block px-2 py-1 text-xs rounded"
                  :class="{
                    'bg-purple-900/50 text-purple-300': rule.ruleType === 'legendary',
                    'bg-yellow-900/50 text-yellow-300': rule.ruleType === 'court',
                    'bg-blue-900/50 text-blue-300': rule.ruleType === 'basic'
                  }"
                  :title="`${rule.name} (${rule.ruleType})`"
                >
                  {{ rule.name }}
                </span>
                <span v-if="ruleset.defaultRules?.length === 0" class="text-gray-500 text-sm">None</span>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-300 text-sm">{{ ruleset.description || '-' }}</td>
            <td class="px-6 py-4 text-center text-gray-300">{{ ruleset.ruleCount }}</td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openEditModal(ruleset)"
                  class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(ruleset)"
                  class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="rulesets.length === 0" class="text-center py-12 text-gray-400">
        No rulesets found. Create your first ruleset!
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

