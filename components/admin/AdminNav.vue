<script setup lang="ts">
interface Props {
  activePage: 'categories' | 'games' | 'rulesets' | 'rules' | 'designs' | 'icons' | 'shop' | 'features'
}

const props = defineProps<Props>()

const tabs = [
  { id: 'categories', label: 'Categories', path: '/admin/categories' },
  { id: 'games', label: 'Games', path: '/admin/games' },
  { id: 'rulesets', label: 'Rulesets', path: '/admin/rulesets' },
  { id: 'rules', label: 'Rules', path: '/admin/rules' },
  { id: 'designs', label: 'Card Designs', path: '/admin/designs' },
  { id: 'icons', label: 'Icons', path: '/admin/icons' },
  { id: 'shop', label: 'Shop', path: '/admin/shop' },
  { id: 'features', label: 'Features', path: '/admin/features' },
]
</script>

<template>
  <div class="flex gap-3 mb-6 flex-wrap">
    <NuxtLink
      v-for="tab in tabs"
      :key="tab.id"
      :to="tab.path"
      :class="[
        'px-4 py-2 rounded-lg transition',
        activePage === tab.id
          ? 'bg-cyan text-white font-semibold'
          : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
      ]"
    >
      {{ tab.label }}
    </NuxtLink>
  </div>
</template>

