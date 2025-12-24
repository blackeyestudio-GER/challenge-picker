<script setup lang="ts">
import { onMounted } from 'vue'

definePageMeta({
  layout: false
})

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
    const response = await $fetch(`/api/user/connect/discord/callback?code=${code}&state=${state}`)
    
    // If we got here, backend processed it - check if it returned HTML or we need to handle it
    // Since backend returns HTML with postMessage, we'll let it handle the message
    // This page is just a fallback
    
  } catch (error: any) {
    console.error('Discord callback error:', error)
    
    if (window.opener) {
      window.opener.postMessage({ 
        type: 'discord_login_error', 
        message: error.data?.message || 'Login failed' 
      }, '*')
      setTimeout(() => window.close(), 1000)
    } else {
      navigateTo('/login')
    }
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan mx-auto mb-4"></div>
      <p class="text-white">Processing Discord login...</p>
    </div>
  </div>
</template>

