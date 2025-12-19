<script setup lang="ts">
const props = defineProps<{
  baseUrl: string
  design: string
  label: string
  color?: 'cyan' | 'magenta'
}>()

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
    alert('Failed to copy to clipboard')
  }
}

const buttonClasses = computed(() => {
  const base = 'px-3 py-1 rounded text-sm font-semibold transition-all duration-300 flex items-center gap-1 border'
  const colorClass = props.color || 'cyan'
  
  if (copied.value) {
    return `${base} bg-green-600/30 text-green-400 border-green-500/50`
  }
  
  return `${base} bg-${colorClass}/20 hover:bg-${colorClass}/30 text-${colorClass} border-${colorClass}/50`
})
</script>

<template>
  <button
    @click="copyToClipboard"
    :class="buttonClasses"
    :disabled="isTransitioning"
    title="Copy URL with this design for testing"
  >
    <Transition name="fade" mode="out-in">
      <Icon 
        v-if="copied" 
        name="heroicons:check-circle" 
        class="w-4 h-4"
        key="check"
      />
      <Icon 
        v-else 
        name="heroicons:link" 
        class="w-4 h-4"
        key="link"
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

