<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useIcons, type RuleIcon } from '~/composables/useIcons'
import { useAuth } from '~/composables/useAuth'
import { Icon } from '#components'
import AdminHeader from '~/components/admin/AdminHeader.vue'
import AdminSearchBar from '~/components/admin/AdminSearchBar.vue'
import AdminEmptyState from '~/components/admin/AdminEmptyState.vue'

definePageMeta({
  middleware: 'admin'
})

const { fetchIcons, loading } = useIcons()
const { token } = useAuth()

const icons = ref<RuleIcon[]>([])
const searchQuery = ref('')
const selectedCategory = ref<string>('all')

onMounted(async () => {
  await loadIcons()
})

const loadIcons = async () => {
  try {
    icons.value = await fetchIcons()
  } catch (err) {
    console.error('Failed to load icons:', err)
  }
}

const downloadIcons = async () => {
  const confirmed = confirm(
    'This will download/update all icons from game-icons.net.\n\n' +
    'To download icons, run this command in your terminal:\n\n' +
    'make download-icons\n\n' +
    'Or manually:\n' +
    'make shell\n' +
    'php bin/console app:download-game-icons --update-existing\n\n' +
    'After downloading, refresh this page to see the updated icons.'
  )
  
  if (confirmed) {
    // Just show instructions - actual download happens via CLI
    alert('Please run the command in your terminal, then refresh this page.')
  }
}

const getAuthHeader = () => {
  return {
    'Authorization': `Bearer ${token.value}`,
    'Content-Type': 'application/json'
  }
}

// Get unique categories
const categories = computed(() => {
  const cats = new Set(icons.value.map(icon => icon.category))
  return ['all', ...Array.from(cats).sort()]
})

// Filter icons
const filteredIcons = computed(() => {
  let filtered = icons.value

  // Filter by category
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(icon => icon.category === selectedCategory.value)
  }

  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(icon =>
      icon.displayName.toLowerCase().includes(query) ||
      icon.identifier.toLowerCase().includes(query) ||
      icon.tags?.some(tag => tag.toLowerCase().includes(query))
    )
  }

  return filtered
})

const getCategoryColor = (category: string): string => {
  const colors: Record<string, string> = {
    weapon: 'bg-red-500/20 text-red-300 border-red-500/50',
    movement: 'bg-green-500/20 text-green-300 border-green-500/50',
    resource: 'bg-blue-500/20 text-blue-300 border-blue-500/50',
    action: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/50',
    modifier: 'bg-purple-500/20 text-purple-300 border-purple-500/50'
  }
  return colors[category] || 'bg-gray-500/20 text-gray-300 border-gray-500/50'
}

const categoryLabels: Record<string, string> = {
  all: 'All Icons',
  weapon: 'Weapons',
  movement: 'Movement',
  resource: 'Resources',
  action: 'Actions',
  modifier: 'Modifiers'
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <AdminHeader
      title="Rule Icons"
      :description="`${icons.length} available icons`"
    />

    <!-- Download/Refresh Icons Button -->
    <div class="mb-6 flex items-center justify-between">
      <div class="text-sm text-gray-400">
        Icons are downloaded from game-icons.net GitHub repository
      </div>
      <button
        @click="downloadIcons"
        class="px-4 py-2 bg-cyan hover:bg-cyan-dark text-white rounded-lg transition-all flex items-center gap-2 font-semibold"
      >
        <Icon name="heroicons:information-circle" class="w-5 h-5" />
        How to Download Icons
      </button>
    </div>

    <!-- Category Filter -->
    <div class="mb-4 flex flex-wrap gap-2">
      <button
        v-for="category in categories"
        :key="category"
        @click="selectedCategory = category"
        :class="[
          'px-4 py-2 rounded-lg font-semibold transition-all',
          selectedCategory === category
            ? 'bg-cyan text-white'
            : 'bg-gray-800 text-gray-300 hover:bg-gray-700 border border-gray-700'
        ]"
      >
        {{ categoryLabels[category] || category }}
      </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-6">
      <AdminSearchBar
        v-model="searchQuery"
        placeholder="Search icons by name, identifier, or tags..."
      />
      <p class="text-gray-400 text-sm mt-2">
        Showing {{ filteredIcons.length }} of {{ icons.length }} icons
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan mb-4"></div>
      <p class="text-white">Loading icons...</p>
    </div>

    <!-- Empty State -->
    <AdminEmptyState
      v-else-if="filteredIcons.length === 0"
      icon="heroicons:photo"
      :message="searchQuery ? `No icons found matching &quot;${searchQuery}&quot;` : 'No icons available'"
      :search-query="searchQuery"
      @clear-search="searchQuery = ''"
    />

    <!-- Icons Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
      <div
        v-for="icon in filteredIcons"
        :key="icon.id"
        class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 hover:border-cyan rounded-lg p-4 flex flex-col items-center justify-center text-center transition-all group"
      >
        <!-- Icon SVG -->
        <div 
          class="w-12 h-12 mb-3 flex items-center justify-center transition-colors icon-svg-container text-gray-300 group-hover:text-cyan"
        >
          <div 
            v-if="icon.svgContent && icon.svgContent.trim() !== ''"
            v-html="icon.svgContent" 
            class="w-full h-full svg-wrapper"
          ></div>
          <!-- Fallback if SVG is missing -->
          <div v-else class="text-xs text-gray-500 flex items-center justify-center h-full">
            ?
          </div>
        </div>
        
        <!-- Icon Name -->
        <p class="text-sm font-semibold text-white mb-1 line-clamp-2">{{ icon.displayName }}</p>
        
        <!-- Category Badge -->
        <span
          :class="[
            'text-xs px-2 py-1 rounded-full border',
            getCategoryColor(icon.category)
          ]"
        >
          {{ icon.category }}
        </span>
        
        <!-- Identifier -->
        <p class="text-xs text-gray-500 mt-2 truncate w-full" :title="icon.identifier">
          {{ icon.identifier }}
        </p>
      </div>
    </div>

    <!-- Info Box -->
    <div class="mt-8 bg-blue-600/20 border border-blue-500 text-blue-300 p-4 rounded-lg">
      <div class="flex items-start gap-3">
        <Icon name="heroicons:information-circle" class="w-5 h-5 flex-shrink-0 mt-0.5" />
        <div class="text-sm">
          <p class="font-semibold mb-1">About Rule Icons</p>
          <p>These icons are used throughout the system to visualize rules. Icons are from game-icons.net and are available under CC BY 3.0 license.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.icon-svg-container {
  overflow: visible;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 48px;
  min-height: 48px;
}

.icon-svg-container :deep(div) {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-svg-container :deep(svg) {
  width: 100%;
  height: 100%;
  display: block;
  max-width: 100%;
  max-height: 100%;
  flex-shrink: 0;
  color: inherit;
}

/* Force all SVG elements to use currentColor - override any hardcoded colors */
.icon-svg-container :deep(svg),
.icon-svg-container :deep(svg *),
.icon-svg-container :deep(svg path),
.icon-svg-container :deep(svg circle),
.icon-svg-container :deep(svg rect),
.icon-svg-container :deep(svg polygon),
.icon-svg-container :deep(svg ellipse),
.icon-svg-container :deep(svg g),
.icon-svg-container :deep(svg g *),
.icon-svg-container :deep(svg g path),
.icon-svg-container :deep(svg g circle),
.icon-svg-container :deep(svg g rect),
.icon-svg-container :deep(svg g polygon),
.icon-svg-container :deep(svg use),
.icon-svg-container :deep(svg symbol) {
  fill: currentColor !important;
  stroke: currentColor !important;
  color: inherit !important;
}

/* Special handling for elements that should only have stroke */
.icon-svg-container :deep(svg line),
.icon-svg-container :deep(svg polyline),
.icon-svg-container :deep(svg g line),
.icon-svg-container :deep(svg g polyline) {
  stroke: currentColor !important;
  fill: none !important;
  stroke-width: inherit;
}


/* Fix for SVGs without viewBox */
.icon-svg-container :deep(svg:not([viewBox])) {
  viewBox: 0 0 512 512;
}

/* Ensure proper aspect ratio */
.icon-svg-container :deep(svg) {
  preserveAspectRatio: xMidYMid meet;
}

/* Wrapper for SVG content */
.svg-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.svg-wrapper :deep(svg) {
  width: 100% !important;
  height: 100% !important;
}

/* Force remove any fill="none" that might cause grey squares */
.icon-svg-container :deep(svg[fill="none"]),
.icon-svg-container :deep(svg *[fill="none"]) {
  fill: currentColor !important;
}

/* Ensure SVG is visible */
.icon-svg-container :deep(svg) {
  opacity: 1 !important;
  visibility: visible !important;
}
</style>
