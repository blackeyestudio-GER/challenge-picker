<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AdminRuleset, CreateRulesetRequest } from '~/composables/useAdmin'
import { Icon } from '#components'
import TypeaheadSelect from '~/components/TypeaheadSelect.vue'

interface Game {
  id: number
  name: string
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

const formData = ref<CreateRulesetRequest & { id?: number }>({
  name: '',
  description: '',
  gameId: 0,
  isDefault: false
})

watch(() => props.editingRuleset, (ruleset) => {
  if (ruleset) {
    formData.value = {
      id: ruleset.id,
      name: ruleset.name,
      description: ruleset.description || '',
      gameId: ruleset.gameId,
      isDefault: ruleset.isDefault
    }
  } else {
    formData.value = {
      name: '',
      description: '',
      gameId: 0,
      isDefault: false
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
          {{ editingRuleset ? 'Edit Ruleset' : 'Create Ruleset' }}
        </h2>
        <button @click="handleClose" class="text-gray-400 hover:text-white">
          <Icon name="heroicons:x-mark" class="w-6 h-6" />
        </button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Game *</label>
          <TypeaheadSelect
            v-model="formData.gameId"
            :options="games"
            :disabled="!!editingRuleset"
            placeholder="Search for a game..."
            required
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan"
            placeholder="Enter ruleset name"
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
        
        <div class="flex items-center gap-3">
          <input
            v-model="formData.isDefault"
            type="checkbox"
            id="isDefault"
            class="w-4 h-4 rounded bg-gray-900 border-gray-600 text-cyan focus:ring-cyan"
          />
          <label for="isDefault" class="text-sm font-medium text-gray-300">
            Set as default ruleset for this game
          </label>
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
            {{ editingRuleset ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

