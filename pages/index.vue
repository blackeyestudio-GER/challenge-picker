<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

const { isAuthenticated, loadAuth } = useAuth()

onMounted(() => {
  loadAuth()
  
  // Redirect if already authenticated
  if (isAuthenticated.value) {
    navigateTo('/dashboard')
  }
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500">
    <!-- Hero Section -->
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
      <div class="max-w-4xl w-full">
        <!-- Main Content -->
        <div class="text-center text-white mb-12">
          <h1 class="text-5xl md:text-7xl font-bold mb-6 drop-shadow-lg">
            Challenge Picker
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-blue-100">
            Secure user authentication and profile management
          </p>
          <p class="text-lg md:text-xl mb-12 text-white/90 max-w-2xl mx-auto">
            Built with Symfony backend and Nuxt frontend, featuring JWT authentication and image upload
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <NuxtLink
              to="/register"
              class="w-full sm:w-auto px-8 py-4 bg-white text-purple-600 font-bold rounded-xl shadow-2xl hover:shadow-3xl hover:scale-105 transition transform"
            >
              Get Started
            </NuxtLink>
            <NuxtLink
              to="/login"
              class="w-full sm:w-auto px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-xl border-2 border-white/30 hover:bg-white/20 transition"
            >
              Sign In
            </NuxtLink>
          </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 text-white border border-white/20">
            <div class="w-12 h-12 bg-blue-400 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Secure Authentication</h3>
            <p class="text-blue-100">JWT token-based auth with email/password and OAuth support</p>
          </div>

          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 text-white border border-white/20">
            <div class="w-12 h-12 bg-purple-400 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Profile Management</h3>
            <p class="text-purple-100">Edit profile info and upload avatar with automatic resizing</p>
          </div>

          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 text-white border border-white/20">
            <div class="w-12 h-12 bg-pink-400 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Mobile Friendly</h3>
            <p class="text-pink-100">Fully responsive design that works on all devices</p>
          </div>
        </div>

        <!-- Status Badge -->
        <div class="text-center mt-12">
          <div class="inline-flex items-center px-4 py-2 bg-green-500/20 backdrop-blur-sm border border-green-400/30 rounded-full text-green-100 text-sm">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
            Symfony + Nuxt + JWT • Fully Functional
          </div>
        </div>
      </div>
    </div>
  </div>
</template>