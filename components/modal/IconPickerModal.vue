<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useIcons, type RuleIcon } from '~/composables/useIcons'

interface Props {
  show: boolean
  currentIcon?: string | null
}

interface Emits {
  (e: 'close'): void
  (e: 'select', iconIdentifier: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { fetchRuleIcons, loading } = useIcons()
const icons = ref<RuleIcon[]>([])
const searchQuery = ref('')
const selectedCategory = ref('all')
const selectedIcon = ref<string | null>(props.currentIcon || null)

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
  } catch {
    icons.value = []
  }
})

const selectIcon = (iconIdentifier: string) => {
  selectedIcon.value = iconIdentifier
}

const confirmSelection = () => {
  if (selectedIcon.value) {
    emit('select', selectedIcon.value)
    emit('close')
  }
}

const selectedIconData = computed(() => {
  return icons.value.find(i => i.identifier === selectedIcon.value)
})

const clearSelection = () => {
  selectedIcon.value = null
}

const cancel = () => {
  emit('close')
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" @click="cancel"/>

    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative admin-modal-surface max-w-4xl w-full max-h-[95vh] flex flex-col">
        <div class="flex-shrink-0 border-b border-[var(--color-border-secondary)] px-6 py-4">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-[var(--color-text-primary)]">Select Icon</h2>
            <button class="admin-modal-close" @click="cancel">
              <Icon name="heroicons:x-mark" class="w-6 h-6" />
            </button>
          </div>

          <div class="flex flex-col sm:flex-row gap-3">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search icons..."
              class="flex-1 px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            >
            <select
              v-model="selectedCategory"
              class="px-4 py-2 rounded-xl bg-[var(--color-bg-card)] text-[var(--color-text-primary)] border border-[var(--color-border-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-primary)]"
            >
              <option value="all">All Categories</option>
              <option v-for="category in categories.filter(c => c !== 'all')" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>

          <div v-if="selectedIcon" class="mt-3 p-3 bg-[var(--color-bg-overlay)] rounded-xl border border-[var(--color-border-secondary)]">
            <div class="flex items-center gap-3">
              <!-- eslint-disable vue/no-v-html -->
              <div 
                class="w-8 h-8 flex items-center justify-center icon-preview"
                v-html="selectedIconData?.svgContent"
              />
              <!-- eslint-enable vue/no-v-html -->
              <span class="text-[var(--color-text-primary)] font-semibold flex-1">{{ selectedIconData?.displayName }}</span>
              <button
                class="text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] transition"
                @click="clearSelection"
              >
                <Icon name="heroicons:x-mark" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 min-h-0">
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[var(--color-accent-primary)]"/>
            <p class="text-[var(--color-text-primary)] mt-4">Loading icons...</p>
          </div>

          <div v-else-if="filteredIcons.length > 0" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
            <button
              v-for="icon in filteredIcons"
              :key="icon.id"
              :class="[
                'group relative aspect-square rounded-lg p-3 transition-all',
                'flex flex-col items-center justify-center text-center',
                'border-2',
                selectedIcon === icon.identifier
                  ? 'bg-[color-mix(in_srgb,var(--color-accent-primary)_12%,transparent)] border-[var(--color-accent-primary)]'
                  : 'bg-[var(--color-bg-card)] border-[var(--color-border-secondary)] hover:border-[var(--color-accent-primary)] hover:bg-[var(--color-bg-card-hover)]'
              ]"
              :title="icon.displayName"
              @click="selectIcon(icon.identifier)"
            >
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div class="w-8 h-8 mb-1 flex items-center justify-center" v-html="icon.svgContent"/>
              <p class="text-xs text-[var(--color-text-muted)] group-hover:text-[var(--color-text-primary)] truncate w-full">
                {{ icon.displayName }}
              </p>

              <div v-if="selectedIcon === icon.identifier" class="absolute top-1 right-1 w-5 h-5 bg-[var(--color-accent-primary)] rounded-full flex items-center justify-center">
                <Icon name="heroicons:check" class="w-3 h-3 text-white" />
              </div>
            </button>
          </div>

          <div v-else class="text-center py-12 text-[var(--color-text-muted)]">
            <Icon name="heroicons:magnifying-glass" class="w-16 h-16 mx-auto mb-4 opacity-50" />
            <p>No icons found matching your criteria.</p>
          </div>
        </div>

        <div class="flex-shrink-0 border-t border-[var(--color-border-secondary)] px-6 py-4 flex justify-between items-center">
          <div class="text-sm text-[var(--color-text-muted)]">
            {{ filteredIcons.length }} icon{{ filteredIcons.length !== 1 ? 's' : '' }} found
          </div>
          <div class="flex gap-3">
            <button class="btn btn-secondary px-6 py-2" @click="cancel">
              Cancel
            </button>
            <button
              :disabled="!selectedIcon"
              :class="[
                'px-6 py-2 rounded-lg font-semibold transition',
                selectedIcon
                  ? 'btn btn-primary'
                  : 'bg-[var(--color-bg-overlay)] text-[var(--color-text-muted)] cursor-not-allowed'
              ]"
              @click="confirmSelection"
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
