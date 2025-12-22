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
const showModal = ref(false)
const editingRuleset = ref<AdminRuleset | null>(null)

onMounted(async () => {
  await Promise.all([loadGames(), loadRulesets()])
})

const loadGames = async () => {
  try {
    games.value = await fetchAdminGames()
  } catch (err) {
    console.error('Failed to load games:', err)
  }
}

const loadRulesets = async () => {
  try {
    rulesets.value = await fetchAdminRulesets()
  } catch (err) {
    console.error('Failed to load rulesets:', err)
  }
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
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Game</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Description</th>
            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Rules</th>
            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Default</th>
            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ruleset in rulesets" :key="ruleset.id" class="border-b border-gray-700 hover:bg-gray-700/50 transition">
            <td class="px-6 py-4 text-white font-medium">{{ ruleset.name }}</td>
            <td class="px-6 py-4 text-gray-300">{{ ruleset.gameName }}</td>
            <td class="px-6 py-4 text-gray-300 text-sm">{{ ruleset.description || '-' }}</td>
            <td class="px-6 py-4 text-center text-gray-300">{{ ruleset.ruleCount }}</td>
            <td class="px-6 py-4 text-center">
              <span v-if="ruleset.isDefault" class="text-green-400">✓</span>
              <span v-else class="text-gray-500">-</span>
            </td>
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

