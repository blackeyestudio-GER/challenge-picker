<script setup lang="ts">
const props = defineProps<{
  baseUrl: string
  design: string
  label: string
}>()

const { notifyApiError } = useNotify()

const copied = ref(false)
const isTransitioning = ref(false)

const copyToClipboard = async () => {
  if (copied.value || isTransitioning.value) return
  
  const url = `${props.baseUrl}?design=${props.design}`
  
  try {
    await navigator.clipboard.writeText(url)
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
const buttonClasses = computed(() => {
  if (copied.value) {
    return 'btn btn-success btn-sm'
  }
  return 'btn btn-secondary btn-sm'
})
</script>

<template>
  <button
    type="button"
    :class="buttonClasses"
    :disabled="isTransitioning"
    title="Copy URL with this design for testing"
    :aria-label="copied ? 'Copied URL to clipboard' : `${label}: copy test URL`"
    @click="copyToClipboard"
  >
    <Transition name="fade" mode="out-in">
      <Icon 
        v-if="copied" 
        key="check" 
        name="heroicons:check-circle"
        style="width: 1rem; height: 1rem"
      />
      <Icon 
        v-else 
        key="link" 
        name="heroicons:link"
        style="width: 1rem; height: 1rem"
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

