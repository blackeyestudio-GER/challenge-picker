<template>
  <div class="bg-gray-900/50 border border-gray-600 rounded-lg p-4">
    <div class="flex items-start justify-between mb-2">
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-white">
          {{ title }}
        </h3>
        <p class="text-sm text-gray-400 mt-1">
          {{ description }}
        </p>
      </div>
      <span
        v-if="method"
        :class="[
          'px-2 py-1 text-xs font-semibold rounded',
          method === 'POST' ? 'bg-green-900 text-green-200' : 'bg-blue-900 text-blue-200'
        ]"
      >
        {{ method }}
      </span>
    </div>

    <!-- URL Display -->
    <div class="mt-3 flex gap-2">
      <input
        :value="url"
        readonly
        class="flex-1 bg-gray-950 border border-gray-700 rounded px-3 py-2 text-gray-300 font-mono text-sm"
        @click="selectAll"
      />
      <button
        @click="copyUrl"
        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold transition text-sm"
      >
        {{ copied ? '✓' : 'Copy' }}
      </button>
    </div>

    <!-- Auth Required Badge -->
    <div v-if="requiresAuth" class="mt-2 flex items-center gap-2 text-xs text-yellow-400">
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
      </svg>
      <span>Requires Authorization header with your token</span>
    </div>

    <!-- Example Output -->
    <div v-if="exampleOutput" class="mt-3">
      <p class="text-xs font-semibold text-gray-500 mb-1">Example Output:</p>
      <pre class="bg-gray-950 border border-gray-700 rounded px-3 py-2 text-gray-300 text-xs whitespace-pre-wrap">{{ exampleOutput }}</pre>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  title: string
  description: string
  url: string
  method?: string
  requiresAuth?: boolean
  exampleOutput?: string
}

const props = defineProps<Props>()

const copied = ref(false)

const copyUrl = async () => {
  await navigator.clipboard.writeText(props.url)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

const selectAll = (event: Event) => {
  const input = event.target as HTMLInputElement
  input.select()
}
</script>

