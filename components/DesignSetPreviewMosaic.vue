<script setup lang="ts">
/**
 * Tarot card preview strip: first image full-bleed, or up to four tiles in a 2×2 grid.
 */
const props = withDefaults(
  defineProps<{
    images: string[]
    altPrefix: string
    /** hero = shop / purchases; compact = preferences row */
    variant?: 'hero' | 'compact'
  }>(),
  { variant: 'hero' }
)

const tiles = computed(() => props.images.filter((s) => s && s.length > 0).slice(0, 4))

const rootClass = computed(() =>
  props.variant === 'compact'
    ? 'design-set-preview-mosaic design-set-preview-mosaic--compact'
    : 'design-set-preview-mosaic design-set-preview-mosaic--hero'
)
</script>

<template>
  <div :class="rootClass" class="bg-gray-900 overflow-hidden">
    <template v-if="tiles.length === 0">
      <div class="design-set-preview-mosaic__empty">
        <Icon name="heroicons:photo" class="design-set-preview-mosaic__empty-icon" aria-hidden="true" />
        <span class="design-set-preview-mosaic__empty-text">No preview yet</span>
      </div>
    </template>
    <img
      v-else-if="tiles.length === 1"
      :src="tiles[0]"
      :alt="`Preview: ${altPrefix}`"
      class="design-set-preview-mosaic__single"
    />
    <div v-else class="design-set-preview-mosaic__grid">
      <img
        v-for="(src, i) in tiles"
        :key="i"
        :src="src"
        :alt="`Preview ${i + 1}: ${altPrefix}`"
        class="design-set-preview-mosaic__cell"
      />
    </div>
  </div>
</template>

<style scoped>
.design-set-preview-mosaic--hero {
  position: relative;
  width: 100%;
  aspect-ratio: 3 / 4;
}

.design-set-preview-mosaic--compact {
  width: 100%;
  max-height: 11rem;
  aspect-ratio: 3 / 4;
  max-width: 8rem;
  margin-left: auto;
  margin-right: auto;
  border-radius: 0.5rem;
}

.design-set-preview-mosaic__empty {
  width: 100%;
  height: 100%;
  min-height: 8rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  text-align: center;
}

.design-set-preview-mosaic__empty-icon {
  width: 2.5rem;
  height: 2.5rem;
  color: rgb(75 85 99);
  margin-bottom: 0.5rem;
}

.design-set-preview-mosaic__empty-text {
  font-size: 0.875rem;
  color: rgb(107 114 128);
}

.design-set-preview-mosaic__single {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.design-set-preview-mosaic__grid {
  width: 100%;
  height: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 2px;
  background: rgb(3 7 18);
  padding: 2px;
  box-sizing: border-box;
}

.design-set-preview-mosaic__cell {
  width: 100%;
  height: 100%;
  min-height: 0;
  object-fit: cover;
  display: block;
}
</style>
