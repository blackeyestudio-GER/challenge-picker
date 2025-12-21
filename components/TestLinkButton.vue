<script setup lang="ts">
const props = defineProps<{
  baseUrl: string
  design: string
  label: string
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

const buttonStyle = computed(() => {
  if (copied.value) {
    return {
      backgroundColor: 'rgba(22, 163, 74, 0.2)',
      color: '#4ade80',
      border: '1px solid rgba(34, 197, 94, 0.5)',
      padding: '0.25rem 0.75rem',
      borderRadius: '0.25rem',
      fontSize: '0.875rem',
      fontWeight: '600',
      transition: 'all 0.3s',
      display: 'flex',
      alignItems: 'center',
      gap: '0.25rem',
      cursor: 'pointer'
    }
  }
  
  return {
    backgroundColor: 'rgba(6, 182, 212, 0.2)',
    color: '#06b6d4',
    border: '1px solid rgba(6, 182, 212, 0.5)',
    padding: '0.25rem 0.75rem',
    borderRadius: '0.25rem',
    fontSize: '0.875rem',
    fontWeight: '600',
    transition: 'all 0.3s',
    display: 'flex',
    alignItems: 'center',
    gap: '0.25rem',
    cursor: 'pointer'
  }
})

const handleHover = (e: MouseEvent, isHovering: boolean) => {
  if (copied.value) return
  
  const target = e.target as HTMLElement
  if (isHovering) {
    target.style.backgroundColor = 'rgba(6, 182, 212, 0.3)'
  } else {
    target.style.backgroundColor = 'rgba(6, 182, 212, 0.2)'
  }
}
</script>

<template>
  <button
    @click="copyToClipboard"
    @mouseenter="handleHover($event, true)"
    @mouseleave="handleHover($event, false)"
    :style="buttonStyle"
    :disabled="isTransitioning"
    title="Copy URL with this design for testing"
  >
    <Transition name="fade" mode="out-in">
      <Icon 
        v-if="copied" 
        name="heroicons:check-circle" 
        style="width: 1rem; height: 1rem"
        key="check"
      />
      <Icon 
        v-else 
        name="heroicons:link" 
        style="width: 1rem; height: 1rem"
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

