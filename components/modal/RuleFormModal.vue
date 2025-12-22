<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AdminRule, CreateRuleRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import TypeaheadSelect from '~/components/TypeaheadSelect.vue'

interface Ruleset {
  id: number
  name: string
  gameName: string
}

interface Props {
  show: boolean
  editingRule: AdminRule | null
  rulesets: Ruleset[]
  loading?: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'submit', data: CreateRuleRequest & { id?: number }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formData = ref<CreateRuleRequest & { id?: number }>({
  name: '',
  description: '',
  durationMinutes: 60,
  rulesetIds: []
})

watch(() => props.editingRule, (rule) => {
  if (rule) {
    formData.value = {
      id: rule.id,
      name: rule.name,
      description: rule.description || '',
      durationMinutes: rule.durationMinutes || 60,
      rulesetIds: rule.rulesets.map(r => r.id)
    }
  } else {
    formData.value = {
      name: '',
      description: '',
      durationMinutes: 60,
      rulesetIds: []
    }
  }
}, { immediate: true })

const handleSubmit = () => {
  emit('submit', formData.value)
}

const handleClose = () => {
  emit('close')
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4" @click.self="handleClose">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full border border-gray-700">
      <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-white">
          {{ editingRule ? 'Edit Rule' : 'Create Rule' }}
        </h2>
        <button @click="handleClose" class="text-gray-400 hover:text-white">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
            placeholder="Enter rule name"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="3"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
            placeholder="Enter description"
          ></textarea>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Duration (minutes) *</label>
          <input
            v-model.number="formData.durationMinutes"
            type="number"
            required
            min="1"
            max="600"
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
            placeholder="60"
          />
        </div>
        
        <div v-if="!editingRule">
          <label class="block text-sm font-medium text-gray-300 mb-2">Rulesets (optional)</label>
          <p class="text-xs text-gray-400 mb-2">Select rulesets to associate with this rule. You can also add this rule to rulesets later.</p>
          <div class="space-y-2 max-h-48 overflow-y-auto bg-gray-900 rounded-lg p-3">
            <label v-for="ruleset in rulesets" :key="ruleset.id" class="flex items-center gap-2 p-2 hover:bg-gray-800 rounded cursor-pointer">
              <input
                type="checkbox"
                :value="ruleset.id"
                v-model="formData.rulesetIds"
                class="w-4 h-4 text-cyan bg-gray-800 border-gray-600 rounded focus:ring-cyan focus:ring-2"
              />
              <span class="text-sm text-gray-300">{{ ruleset.gameName }} - {{ ruleset.name }}</span>
            </label>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
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
            {{ editingRule ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

