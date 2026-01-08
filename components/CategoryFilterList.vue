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
  <div class="category-filter-list">
    <!-- Header -->
    <div class="category-filter-list__header">
      <!-- Title with Clear Button -->
      <div class="category-filter-list__title-group">
        <h3 class="category-filter-list__title">Filter by Tags</h3>
        <button
          v-if="selectedCategories.size > 0 || showRandomGames"
          @click="emit('clearFilters')"
          class="category-filter-list__clear-button"
          title="Clear all filters"
        >
          <Icon name="heroicons:x-mark" class="category-filter-list__clear-icon" />
        </button>
      </div>
      
      <!-- Filter Mode Toggle (AND/OR) -->
      <div v-if="selectedCategories.size > 1" class="category-filter-list__mode-group">
        <span class="category-filter-list__mode-label">Match:</span>
        <button
          @click="emit('toggleFilterMode')"
          :class="[
            'category-filter-list__mode-toggle',
            filterMode === 'OR' ? 'category-filter-list__mode-toggle--or' : 'category-filter-list__mode-toggle--and'
          ]"
        >
          <span
            :class="[
              'category-filter-list__mode-slider',
              filterMode === 'OR' ? 'category-filter-list__mode-slider--or' : 'category-filter-list__mode-slider--and'
            ]"
          >
            {{ filterMode }}
          </span>
        </button>
        <span class="category-filter-list__mode-hint">
          {{ filterMode === 'OR' ? '(Any tag)' : '(All tags)' }}
        </span>
      </div>
    </div>

    <!-- Category Chips -->
    <div class="category-filter-list__chips">
      <!-- Random Filter Indicator (shown first if active) -->
      <button
        v-if="showRandomGames"
        @click="emit('rerollRandom')"
        class="category-filter-list__chip category-filter-list__chip--random"
        title="Click to re-roll 5 random games"
      >
        <Icon name="heroicons:sparkles" class="category-filter-list__chip-icon" />
        <span>Random (5 games)</span>
        <Icon name="heroicons:arrow-path" class="category-filter-list__chip-icon--small" />
      </button>
      
      <!-- Category Filter Buttons (selected ones appear first) -->
      <button
        v-for="category in sortedCategories"
        :key="category.id"
        @click="emit('toggleCategory', category.id)"
        :class="[
          'category-filter-list__chip',
          selectedCategories.has(category.id)
            ? 'category-filter-list__chip--selected'
            : 'category-filter-list__chip--unselected'
        ]"
      >
        {{ category.name }}
        <span class="category-filter-list__chip-count">({{ categoryGameCounts.get(category.id) || 0 }})</span>
      </button>
    </div>
  </div>
</template>

