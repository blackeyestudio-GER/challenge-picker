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
</script>

<template>
  <div class="design-set-card bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-xl overflow-hidden hover:border-cyan transition-all duration-300">
    <!-- Header -->
    <div class="p-4 border-b border-gray-700">
      <div class="flex items-start justify-between mb-2">
        <h3 class="text-xl font-bold text-white">{{ designSet.name }}</h3>
        <span 
          :class="[
            'px-2 py-1 text-xs font-semibold rounded',
            designSet.is_premium 
              ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white'
              : 'bg-green-500 text-white'
          ]"
        >
          {{ designSet.is_premium ? 'PREMIUM' : 'FREE' }}
        </span>
      </div>
      
      <div class="flex items-center gap-2 text-sm text-gray-400">
        <Icon name="heroicons:squares-2x2" class="w-4 h-4" />
        <span>{{ typeLabel }}</span>
        <span v-if="designSet.theme" class="ml-auto px-2 py-0.5 bg-gray-700 rounded text-xs">
          {{ designSet.theme }}
        </span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <p v-if="designSet.description" class="text-gray-300 text-sm mb-4">
        {{ designSet.description }}
      </p>

      <!-- Placeholder for card preview -->
      <div class="bg-gray-900/50 rounded-lg p-6 mb-4 flex items-center justify-center">
        <Icon name="heroicons:sparkles" class="w-12 h-12 text-gray-600" />
        <span class="ml-2 text-gray-500 text-sm">Preview coming soon</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-4 pb-4">
      <div class="flex items-center justify-between">
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

        <!-- Owned Badge -->
        <div v-if="designSet.owned" class="flex items-center gap-2 px-4 py-2 bg-green-500/20 text-green-400 rounded-lg border border-green-500/30">
          <Icon name="heroicons:check-circle" class="w-5 h-5" />
          <span class="font-semibold">Owned</span>
        </div>

        <!-- Buy Button -->
        <button
          v-else-if="designSet.is_premium"
          @click="emit('buy', designSet.id)"
          class="px-6 py-2 bg-gradient-to-r from-cyan to-magenta text-white font-bold rounded-lg hover:shadow-lg hover:scale-105 transition-all"
        >
          Buy Now
        </button>

        <!-- Free Download (Owned by default) -->
        <div v-else class="flex items-center gap-2 px-4 py-2 bg-green-500/20 text-green-400 rounded-lg border border-green-500/30">
          <Icon name="heroicons:check-circle" class="w-5 h-5" />
          <span class="font-semibold">Available</span>
        </div>
      </div>
    </div>
  </div>
</template>

