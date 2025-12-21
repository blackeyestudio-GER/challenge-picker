<script setup lang="ts">
import type { Category } from '~/composables/useCategories'

interface Props {
  categories: readonly Category[]
  selectedCategories: Set<number>
  filterMode: 'AND' | 'OR'
  showRandomGames: boolean
  categoryGameCounts: Map<number, number>
}

interface Emits {
  (e: 'toggleCategory', categoryId: number): void
  (e: 'toggleFilterMode'): void
  (e: 'clearFilters'): void
  (e: 'rerollRandom'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Sort categories with selected ones first
const sortedCategories = computed(() => {
  return [...props.categories].sort((a, b) => {
    const aSelected = props.selectedCategories.has(a.id)
    const bSelected = props.selectedCategories.has(b.id)
    
    if (aSelected && !bSelected) return -1
    if (!aSelected && bSelected) return 1
    return 0
  })
})
</script>

<template>
  <div class="bg-gray-800/60 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <!-- Title with Clear Button -->
      <div class="flex items-center gap-3">
        <h3 class="text-white text-lg font-semibold">Filter by Tags</h3>
        <button
          v-if="selectedCategories.size > 0 || showRandomGames"
          @click="emit('clearFilters')"
          class="text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all rounded-full p-1"
          title="Clear all filters"
        >
          <Icon name="heroicons:x-mark" class="w-5 h-5" />
        </button>
      </div>
      
      <!-- Filter Mode Toggle (AND/OR) -->
      <div v-if="selectedCategories.size > 1" class="flex items-center gap-2">
        <span class="text-xs text-gray-400">Match:</span>
        <button
          @click="emit('toggleFilterMode')"
          :class="[
            'relative inline-flex h-6 w-24 items-center rounded-full transition-colors',
            filterMode === 'OR' ? 'bg-cyan' : 'bg-magenta'
          ]"
        >
          <span
            :class="[
              'flex h-4 w-10 transform rounded-full bg-white transition-transform text-xs font-bold items-center justify-center',
              filterMode === 'OR' ? 'translate-x-1' : 'translate-x-[52px]'
            ]"
          >
            {{ filterMode }}
          </span>
        </button>
        <span class="text-xs text-gray-400">
          {{ filterMode === 'OR' ? '(Any tag)' : '(All tags)' }}
        </span>
      </div>
    </div>

    <!-- Category Chips -->
    <div class="flex flex-wrap gap-2 mb-4">
      <!-- Random Filter Indicator (shown first if active) -->
      <button
        v-if="showRandomGames"
        @click="emit('rerollRandom')"
        class="px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg shadow-purple/50 border-2 border-transparent flex items-center gap-2 hover:from-purple-700 hover:to-pink-700 transition-all transform hover:scale-105 cursor-pointer"
        title="Click to re-roll 5 random games"
      >
        <Icon name="heroicons:sparkles" class="w-4 h-4" />
        <span>Random (5 games)</span>
        <Icon name="heroicons:arrow-path" class="w-3 h-3 opacity-75" />
      </button>
      
      <!-- Category Filter Buttons (selected ones appear first) -->
      <button
        v-for="category in sortedCategories"
        :key="category.id"
        @click="emit('toggleCategory', category.id)"
        :class="[
          'px-4 py-2 rounded-full text-sm font-medium transition-all transform hover:scale-105 border-2',
          selectedCategories.has(category.id)
            ? 'bg-gradient-to-r from-cyan to-magenta text-white shadow-lg shadow-cyan/50 border-transparent'
            : 'bg-gray-700 text-gray-300 hover:bg-gray-600 border-gray-600'
        ]"
      >
        {{ category.name }}
        <span class="ml-2 text-xs opacity-75">({{ categoryGameCounts.get(category.id) || 0 }})</span>
      </button>
    </div>
  </div>
</template>

