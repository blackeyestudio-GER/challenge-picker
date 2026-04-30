<script setup lang="ts">
const props = defineProps<{
  url: string
  label: string
}>()

const { notifyApiError } = useNotify()

const copied = ref(false)
const isTransitioning = ref(false)

const copyToClipboard = async () => {
  if (copied.value || isTransitioning.value) return
  
  try {
    await navigator.clipboard.writeText(props.url)
    isTransitioning.value = true
    
    // Fade out, change state, fade in
    setTimeout(() => {
      copied.value = true
      isTransitioning.value = false
      
      // Reset after 2.5 seconds
      setTimeout(() => {
        isTransitioning.value = true
        setTimeout(() => {
          copied.value = false
          isTransitioning.value = false
        }, 300)
      }, 2500)
    }, 300)
  } catch (err) {
    console.error('Failed to copy:', err)
    notifyApiError(err, 'Failed to copy to clipboard')
  }
}

// Use theme-aware classes instead of inline styles
</script>

<template>
  <button
    type="button"
    class="btn btn-success"
    :disabled="isTransitioning"
    :aria-label="copied ? 'Copied to clipboard' : label"
    @click="copyToClipboard"
  >
    <Transition name="fade" mode="out-in">
      <Icon 
        v-if="copied" 
        key="check" 
        name="heroicons:check-circle"
        style="width: 1.25rem; height: 1.25rem"
      />
      <Icon 
        v-else 
        key="clipboard" 
        name="heroicons:clipboard-document"
        style="width: 1.25rem; height: 1.25rem"
      />
    </Transition>
    <Transition name="fade" mode="out-in">
      <span v-if="copied" key="copied">Copied!</span>
      <span v-else key="copy">{{ label }}</span>
    </Transition>
  </button>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

