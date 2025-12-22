<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdmin, type AdminRuleset, type AdminRule, type CreateRuleRequest, type UpdateRuleRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import RuleFormModal from '~/components/modal/RuleFormModal.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminRulesets, fetchAdminRules, createRule, updateRule, deleteRule, loading } = useAdmin()

const rulesets = ref<AdminRuleset[]>([])
const rules = ref<AdminRule[]>([])
const showModal = ref(false)
const editingRule = ref<AdminRule | null>(null)

onMounted(async () => {
  await Promise.all([loadRulesets(), loadRules()])
})

const loadRulesets = async () => {
  try {
    rulesets.value = await fetchAdminRulesets()
  } catch (err) {
    console.error('Failed to load rulesets:', err)
  }
}

const loadRules = async () => {
  try {
    rules.value = await fetchAdminRules()
  } catch (err) {
    console.error('Failed to load rules:', err)
  }
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
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Duration</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Rulesets</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Description</th>
            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rule in rules" :key="rule.id" class="border-b border-gray-700 hover:bg-gray-700/50 transition">
            <td class="px-6 py-4 text-white font-medium">{{ rule.name }}</td>
            <td class="px-6 py-4 text-gray-300">{{ rule.durationMinutes }}m</td>
            <td class="px-6 py-4">
              <div v-if="rule.rulesets.length > 0" class="flex flex-wrap gap-1">
                <span
                  v-for="ruleset in rule.rulesets"
                  :key="ruleset.id"
                  class="px-2 py-1 bg-cyan/20 text-cyan rounded text-xs"
                >
                  {{ ruleset.name }}
                </span>
              </div>
              <span v-else class="text-gray-500 text-sm italic">No rulesets</span>
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
        No rules found. Create your first rule!
      </div>
    </div>

    <!-- Rule Form Modal -->
    <RuleFormModal
      :show="showModal"
      :editing-rule="editingRule"
      :rulesets="rulesets"
      :loading="loading"
      @close="closeModal"
      @submit="handleModalSubmit"
    />
  </div>
</template>

