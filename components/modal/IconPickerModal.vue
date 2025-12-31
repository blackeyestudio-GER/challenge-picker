<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useIcons, type RuleIcon } from '~/composables/useIcons'

interface Props {
  show: boolean
  currentIcon?: string | null
  currentColor?: string | null
  currentBrightness?: number | null
  currentOpacity?: number | null
}

interface Emits {
  (e: 'close'): void
  (e: 'select', data: {
    iconIdentifier: string
    color: string
    brightness: number
    opacity: number
  }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { fetchRuleIcons, loading } = useIcons()
const icons = ref<RuleIcon[]>([])
const searchQuery = ref('')
const selectedCategory = ref('all')
const selectedIcon = ref<string | null>(props.currentIcon || null)

// Icon customization
const iconColor = ref(props.currentColor || '#FFFFFF')
const iconBrightness = ref(props.currentBrightness || 1.0)
const iconOpacity = ref(props.currentOpacity || 1.0)

const categories = computed(() => {
  const uniqueCategories = new Set(icons.value.map(icon => icon.category))
  return ['all', ...Array.from(uniqueCategories)].sort()
})

const filteredIcons = computed(() => {
  let filtered = icons.value

  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(icon => icon.category === selectedCategory.value)
  }

  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(icon =>
      icon.displayName.toLowerCase().includes(query) ||
      icon.identifier.toLowerCase().includes(query) ||
      icon.category.toLowerCase().includes(query) ||
      icon.tags?.some(tag => tag.toLowerCase().includes(query))
    )
  }

  return filtered
})

onMounted(async () => {
  try {
    icons.value = await fetchRuleIcons()
  } catch (err) {
    console.error('Failed to load icons:', err)
  }
})

const selectIcon = (iconIdentifier: string) => {
  selectedIcon.value = iconIdentifier
}

const confirmSelection = () => {
  if (selectedIcon.value) {
    emit('select', {
      iconIdentifier: selectedIcon.value,
      color: iconColor.value,
      brightness: iconBrightness.value,
      opacity: iconOpacity.value
    })
    emit('close')
  }
}

const selectedIconData = computed(() => {
  return icons.value.find(i => i.identifier === selectedIcon.value)
})

const previewStyle = computed(() => ({
  color: iconColor.value,
  filter: `brightness(${iconBrightness.value}) opacity(${iconOpacity.value})`
}))

const clearSelection = () => {
  selectedIcon.value = null
}

const cancel = () => {
  emit('close')
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" @click="cancel"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative bg-gray-900 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-700">
        <!-- Header -->
        <div class="sticky top-0 bg-gray-900 border-b border-gray-700 px-6 py-4 z-10">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta">
              Select Icon
            </h2>
            <button
              @click="cancel"
              class="text-gray-400 hover:text-white transition"
            >
              <Icon name="heroicons:x-mark" class="w-6 h-6" />
            </button>
          </div>

          <!-- Search and Filters -->
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search icons..."
              class="flex-1 px-4 py-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan"
            />
            <select
              v-model="selectedCategory"
              class="px-4 py-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan"
            >
              <option value="all">All Categories</option>
              <option v-for="category in categories.filter(c => c !== 'all')" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>

          <!-- Current Selection -->
          <div v-if="selectedIcon" class="mt-3 p-3 bg-gray-800 rounded-lg">
            <div class="flex items-center gap-3 mb-4">
              <div 
                class="w-8 h-8 flex items-center justify-center icon-preview"
                v-html="selectedIconData?.svgContent"
                :style="previewStyle"
              ></div>
              <span class="text-white font-semibold">{{ selectedIconData?.displayName }}</span>
              <button
                @click="clearSelection"
                class="ml-auto text-gray-400 hover:text-white transition"
              >
                <Icon name="heroicons:x-mark" class="w-5 h-5" />
              </button>
            </div>

            <!-- Customization Controls -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Color -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Color
                </label>
                <div class="flex gap-2 items-center">
                  <input
                    type="color"
                    v-model="iconColor"
                    class="w-12 h-10 rounded cursor-pointer border border-gray-600"
                  />
                  <input
                    type="text"
                    v-model="iconColor"
                    placeholder="#FFFFFF"
                    class="flex-1 px-3 py-2 rounded bg-gray-900 text-white text-sm border border-gray-700 focus:outline-none focus:ring-1 focus:ring-cyan"
                  />
                </div>
              </div>

              <!-- Brightness -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Brightness: {{ iconBrightness.toFixed(2) }}
                </label>
                <input
                  type="range"
                  v-model.number="iconBrightness"
                  min="0"
                  max="2"
                  step="0.05"
                  class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-cyan"
                />
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                  <span>Dark</span>
                  <span>Normal</span>
                  <span>Bright</span>
                </div>
              </div>

              <!-- Opacity -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Opacity: {{ Math.round(iconOpacity * 100) }}%
                </label>
                <input
                  type="range"
                  v-model.number="iconOpacity"
                  min="0"
                  max="1"
                  step="0.05"
                  class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-cyan"
                />
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                  <span>Transparent</span>
                  <span>Opaque</span>
                </div>
              </div>
            </div>

            <!-- Reset Button -->
            <button
              @click="iconColor = '#FFFFFF'; iconBrightness = 1.0; iconOpacity = 1.0"
              class="mt-3 text-sm text-gray-400 hover:text-white transition"
            >
              Reset to defaults
            </button>
          </div>
        </div>

        <!-- Icon Grid -->
        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 240px);">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
            <p class="text-white mt-4">Loading icons...</p>
          </div>

          <!-- Icon Grid -->
          <div v-else-if="filteredIcons.length > 0" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
            <button
              v-for="icon in filteredIcons"
              :key="icon.id"
              @click="selectIcon(icon.identifier)"
              :class="[
                'group relative aspect-square rounded-lg p-3 transition-all',
                'flex flex-col items-center justify-center text-center',
                'border-2',
                selectedIcon === icon.identifier
                  ? 'bg-cyan/20 border-cyan'
                  : 'bg-gray-800/80 border-gray-700 hover:border-cyan hover:bg-gray-800'
              ]"
              :title="icon.displayName"
            >
              <div class="w-8 h-8 mb-1 flex items-center justify-center" v-html="icon.svgContent"></div>
              <p class="text-xs text-gray-400 group-hover:text-white truncate w-full">
                {{ icon.displayName }}
              </p>

              <!-- Selected Checkmark -->
              <div v-if="selectedIcon === icon.identifier" class="absolute top-1 right-1 w-5 h-5 bg-cyan rounded-full flex items-center justify-center">
                <Icon name="heroicons:check" class="w-3 h-3 text-white" />
              </div>
            </button>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12 text-gray-400">
            <Icon name="heroicons:magnifying-glass" class="w-16 h-16 mx-auto mb-4 opacity-50" />
            <p>No icons found matching your criteria.</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-900 border-t border-gray-700 px-6 py-4 flex justify-between items-center">
          <div class="text-sm text-gray-400">
            {{ filteredIcons.length }} icon{{ filteredIcons.length !== 1 ? 's' : '' }} found
          </div>
          <div class="flex gap-3">
            <button
              @click="cancel"
              class="px-6 py-2 rounded-lg bg-gray-700 text-white hover:bg-gray-600 transition"
            >
              Cancel
            </button>
            <button
              @click="confirmSelection"
              :disabled="!selectedIcon"
              :class="[
                'px-6 py-2 rounded-lg font-semibold transition',
                selectedIcon
                  ? 'bg-gradient-to-r from-cyan to-magenta text-white hover:shadow-lg'
                  : 'bg-gray-700 text-gray-500 cursor-not-allowed'
              ]"
            >
              Select Icon
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.icon-preview :deep(svg),
.icon-preview :deep(svg *) {
  fill: currentColor !important;
  stroke: currentColor !important;
}
</style>

