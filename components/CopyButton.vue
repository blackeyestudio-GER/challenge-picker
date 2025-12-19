<script setup lang="ts">
const props = defineProps<{
  url: string
  label: string
  variant?: 'primary' | 'success'
}>()

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
    alert('Failed to copy to clipboard')
  }
}

const buttonClasses = computed(() => {
  const base = 'px-4 py-2 rounded-lg font-semibold transition-all duration-300 flex items-center gap-2'
  
  if (copied.value) {
    return `${base} bg-green-600 hover:bg-green-700 text-white`
  }
  
  if (props.variant === 'success') {
    return `${base} bg-green-600 hover:bg-green-700 text-white`
  }
  
  return `${base} bg-green-600 hover:bg-green-700 text-white`
})
</script>

<template>
  <button
    @click="copyToClipboard"
    :class="buttonClasses"
    :disabled="isTransitioning"
  >
    <Transition name="fade" mode="out-in">
      <Icon 
        v-if="copied" 
        name="heroicons:check-circle" 
        class="w-5 h-5"
        key="check"
      />
      <Icon 
        v-else 
        name="heroicons:clipboard-document" 
        class="w-5 h-5"
        key="clipboard"
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

