<script setup lang="ts">
definePageMeta({
  layout: false
})

const { callbackError, bootstrap } = useDiscordCallbackPage()

onMounted(async () => {
  await bootstrap()
})
</script>

<template>
  <div class="auth-page">
    <div class="auth-page__background"/>
    <div class="auth-page__content">
      <div class="auth-page__form-card text-center">
        <div v-if="callbackError" class="auth-page__message auth-page__message--error">
          {{ callbackError }}
        </div>
        <template v-else>
          <div class="discord-callback__spinner"/>
          <p class="auth-page__subtitle">Processing Discord login...</p>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.discord-callback__spinner {
  width: 3rem;
  height: 3rem;
  margin: 0 auto 1rem;
  border: 3px solid color-mix(in srgb, var(--color-text-tertiary) 35%, transparent);
  border-top-color: var(--color-accent-primary);
  border-radius: 9999px;
  animation: discord-callback-spin 1s linear infinite;
}

@keyframes discord-callback-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
