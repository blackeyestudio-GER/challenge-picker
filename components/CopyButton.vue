<script setup lang="ts">
const props = defineProps<{
  url: string
  label: string
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

const buttonStyle = computed(() => {
  return {
    backgroundColor: '#16a34a',
    color: 'white',
    padding: '0.5rem 1rem',
    borderRadius: '0.5rem',
    fontWeight: '600',
    transition: 'all 0.3s',
    display: 'flex',
    alignItems: 'center',
    gap: '0.5rem',
    border: 'none',
    cursor: 'pointer'
  }
})

const handleHover = (e: MouseEvent, isHovering: boolean) => {
  const target = e.target as HTMLElement
  if (isHovering && !copied.value) {
    target.style.backgroundColor = '#15803d'
  } else if (!isHovering && !copied.value) {
    target.style.backgroundColor = '#16a34a'
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
  >
    <Transition name="fade" mode="out-in">
      <Icon 
        v-if="copied" 
        name="heroicons:check-circle" 
        style="width: 1.25rem; height: 1.25rem"
        key="check"
      />
      <Icon 
        v-else 
        name="heroicons:clipboard-document" 
        style="width: 1.25rem; height: 1.25rem"
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

