<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { extractErrorMessage } from '~/utils/errorHandler'

definePageMeta({
  layout: false
})

const callbackError = ref('')

onMounted(async () => {
  // Get the code and state from URL
  const route = useRoute()
  const code = route.query.code as string
  const state = route.query.state as string

  if (!code) {
    // No code, close or redirect
    if (window.opener) {
      window.opener.postMessage({ type: 'discord_login_error', message: 'Authorization cancelled' }, '*')
      window.close()
    } else {
      navigateTo('/login')
    }
    return
  }

  try {
    // Exchange the code for user data via backend
    await $fetch(`/api/user/connect/discord/callback?code=${code}&state=${state}`)
    
    // If we got here, backend processed it - check if it returned HTML or we need to handle it
    // Since backend returns HTML with postMessage, we'll let it handle the message
    // This page is just a fallback
    
  } catch (error: unknown) {
    if (window.opener) {
      window.opener.postMessage({ 
        type: 'discord_login_error', 
        message: extractErrorMessage(error, 'Login failed')
      }, '*')
      setTimeout(() => window.close(), 1000)
    } else {
      callbackError.value = extractErrorMessage(error, 'Discord login failed')
    }
  }
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
