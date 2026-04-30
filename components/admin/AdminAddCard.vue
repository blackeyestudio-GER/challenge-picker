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
    class="admin-add-card"
    @click="$emit('click')"
  >
    <div class="admin-add-card__icon-wrapper">
      <Icon name="heroicons:plus" class="admin-add-card__icon" />
    </div>
    <h3 class="admin-add-card__label">{{ label }}</h3>
  </button>
</template>

