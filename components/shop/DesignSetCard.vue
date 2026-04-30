<script setup lang="ts">
import type { DesignSetShopItem } from '~/composables/useShop'

interface Props {
  designSet: DesignSetShopItem
}

interface Emits {
  (e: 'buy', id: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const typeLabel = computed(() => {
  return props.designSet.type === 'full' ? 'Full Artwork' : 'Template'
})

const priceDisplay = computed(() => {
  if (!props.designSet.is_premium) {
    return 'FREE'
  }
  return `$${props.designSet.price}`
})

/** Up to 4 card art thumbnails from API (falls back to legacy single preview_image). */
const previewTiles = computed((): string[] => {
  const fromList = props.designSet.preview_images?.filter((s) => s && s.length > 0) ?? []
  if (fromList.length > 0) {
    return fromList.slice(0, 4)
  }
  const single = props.designSet.preview_image
  return single ? [single] : []
})
</script>

<template>
  <div class="design-set-card bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-xl overflow-hidden hover:border-cyan transition-all duration-300 flex flex-col">
    <!-- Card art preview (product) -->
    <div class="relative w-full shrink-0">
      <DesignSetPreviewMosaic
        :images="previewTiles"
        :alt-prefix="designSet.name"
        variant="hero"
      />
      <span
        v-if="designSet.is_premium"
        class="absolute top-2 right-2 px-2 py-1 text-xs font-bold rounded bg-amber-500/95 text-white shadow pointer-events-none"
      >
        PREMIUM
      </span>
      <span
        v-else
        class="absolute top-2 right-2 px-2 py-1 text-xs font-bold rounded bg-green-600/95 text-white shadow pointer-events-none"
      >
        FREE
      </span>
    </div>

    <!-- Header -->
    <div class="p-4 border-b border-gray-700 flex-1 flex flex-col">
      <div class="flex items-start justify-between gap-2 mb-2">
        <h3 class="text-xl font-bold text-white leading-tight">{{ designSet.name }}</h3>
      </div>

      <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
        <Icon name="heroicons:squares-2x2" class="w-4 h-4 shrink-0" aria-hidden="true" />
        <span>{{ typeLabel }}</span>
        <span v-if="designSet.theme" class="ml-auto px-2 py-0.5 bg-gray-700 rounded text-xs truncate max-w-[40%]">
          {{ designSet.theme }}
        </span>
      </div>

      <p v-if="designSet.description" class="text-gray-300 text-sm line-clamp-3">
        {{ designSet.description }}
      </p>
    </div>

    <!-- Footer -->
    <div class="px-4 pb-4 pt-2 mt-auto">
      <div class="flex items-center justify-between gap-2 flex-wrap">
        <div class="text-2xl font-bold">
          <span
            :class="[
              designSet.is_premium
                ? 'text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500'
                : 'text-green-400'
            ]"
          >
            {{ priceDisplay }}
          </span>
        </div>

        <div v-if="designSet.owned" class="flex items-center gap-2 px-4 py-2 bg-green-500/20 text-green-400 rounded-lg border border-green-500/30">
          <Icon name="heroicons:check-circle" class="w-5 h-5 shrink-0" aria-hidden="true" />
          <span class="font-semibold">Owned</span>
        </div>

        <button
          v-else-if="designSet.is_premium"
          type="button"
          class="px-6 py-2 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg hover:shadow-lg hover:scale-105 transition-all"
          @click="emit('buy', designSet.id)"
        >
          Buy Now
        </button>

        <div v-else class="flex items-center gap-2 px-4 py-2 bg-green-500/20 text-green-400 rounded-lg border border-green-500/30">
          <Icon name="heroicons:check-circle" class="w-5 h-5 shrink-0" aria-hidden="true" />
          <span class="font-semibold">Available</span>
        </div>
      </div>
    </div>
  </div>
</template>
