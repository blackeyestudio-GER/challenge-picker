<template>
  <div
    v-if="show"
    class="fixed inset-0 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
    style="z-index: 9999;"
    @click.self="close"
  >
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full border border-gray-700 shadow-2xl">
      <h2 class="text-2xl font-bold text-white mb-4">Share Challenge Link</h2>

      <p class="text-gray-300 mb-6">
        Share this link with anyone! They can click it to accept your challenge and get a copy of this playthrough.
      </p>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Challenge Link
        </label>
        <div class="flex gap-2">
          <input
            :value="challengeLink"
            type="text"
            readonly
            class="flex-1 px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:outline-none"
          />
          <button
            @click="copyLink"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2"
          >
            <svg v-if="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm">{{ copied ? 'Copied!' : 'Copy' }}</span>
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">
          Anyone with this link can accept your challenge, even if they don't have an account yet!
        </p>
      </div>

      <div class="flex gap-3">
        <button
          @click="close"
          class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  show: boolean
  playthroughUuid: string
}>()

const emit = defineEmits<{
  close: []
}>()

const config = useRuntimeConfig()
const copied = ref(false)

const challengeLink = computed(() => {
  return `${window.location.origin}/challenge/${props.playthroughUuid}`
})

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(challengeLink.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy link:', err)
  }
}

const close = () => {
  copied.value = false
  emit('close')
}

// Reset when modal is shown
watch(() => props.show, (newShow) => {
  if (newShow) {
    copied.value = false
  }
})
</script>

