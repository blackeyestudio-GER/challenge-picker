<script setup lang="ts">
import { Icon } from '#components'

defineProps<{
  label: string
}>()

defineEmits<{
  click: []
}>()
</script>

<template>
  <button
    @click="$emit('click')"
    class="bg-gray-800/50 border-2 border-dashed border-gray-600 hover:border-cyan hover:bg-gray-800 rounded-xl p-8 flex flex-col items-center justify-center text-center transition-all group min-h-[200px]"
  >
    <div class="w-16 h-16 rounded-full bg-gray-700 group-hover:bg-cyan/20 flex items-center justify-center mb-4 transition-all">
      <Icon name="heroicons:plus" class="w-8 h-8 text-gray-400 group-hover:text-cyan transition-colors" />
    </div>
    <h3 class="text-lg font-bold text-gray-400 group-hover:text-white transition-colors">{{ label }}</h3>
  </button>
</template>

