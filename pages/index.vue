<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: 'landing'
})

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
  <div class="min-h-screen bg-gray-900 relative overflow-hidden">
    <!-- Animated background gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-cyan/30 via-gray-900 to-magenta/30"></div>
    <div class="absolute inset-0 bg-gradient-to-tr from-magenta/20 via-transparent to-cyan/20 animate-pulse"></div>

    <!-- Hero Section -->
    <div class="min-h-screen flex items-center justify-center px-4 py-12 relative z-10">
      <div class="max-w-5xl w-full">
        <!-- Main Content -->
        <div class="text-center text-white mb-12">
          <div class="inline-block mb-4">
            <span class="px-4 py-2 bg-gradient-to-r from-cyan to-magenta rounded-full text-sm font-bold text-white shadow-lg">
              🎮 LIVE NOW • Stream Challenges Made Easy
            </span>
          </div>
          
          <h1 class="text-6xl md:text-8xl font-black mb-6 bg-gradient-to-r from-cyan via-white to-magenta bg-clip-text text-transparent drop-shadow-2xl leading-tight">
            Level Up Your<br/>Stream Game
          </h1>
          
          <p class="text-2xl md:text-3xl mb-6 text-white font-bold">
            Interactive Challenge System for ANY Game
          </p>
          
          <p class="text-lg md:text-xl mb-12 text-gray-300 max-w-3xl mx-auto leading-relaxed">
            Configure custom rules LIVE from any device. No downloads, no setup—just pure browser-based power. 
            Create dynamic playthroughs that keep your audience hooked and your stream fresh. 
            <span class="text-cyan font-semibold">Every game. Every platform. Every stream.</span>
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <NuxtLink
              to="/register"
              class="w-full sm:w-auto px-10 py-5 bg-gradient-to-r from-cyan to-magenta text-white text-lg font-black rounded-xl shadow-2xl hover:shadow-cyan/50 hover:scale-105 transition transform"
            >
              🚀 Start Streaming Now
            </NuxtLink>
            <NuxtLink
              to="/login"
              class="w-full sm:w-auto px-10 py-5 bg-gray-800/80 backdrop-blur-sm text-white text-lg font-bold rounded-xl border-2 border-gray-600 hover:border-cyan transition hover:bg-gray-700/80"
            >
              Sign In
            </NuxtLink>
          </div>
          
          <p class="text-sm text-gray-400">
            ✨ Free to start • No credit card required • Set up in under 2 minutes
          </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-20">
          <div class="bg-gray-800/60 backdrop-blur-md rounded-2xl p-8 text-white border-2 border-gray-700 hover:border-cyan transition-all hover:scale-105 transform">
            <div class="w-14 h-14 bg-gradient-to-br from-cyan to-cyan-dark rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan/50">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold mb-3">Universal Game Support</h3>
            <p class="text-gray-300 leading-relaxed">Works with ANY game on ANY platform. Minecraft, Valorant, Dark Souls, or your indie favorite—we've got you covered.</p>
          </div>

          <div class="bg-gray-800/60 backdrop-blur-md rounded-2xl p-8 text-white border-2 border-gray-700 hover:border-magenta transition-all hover:scale-105 transform">
            <div class="w-14 h-14 bg-gradient-to-br from-magenta to-magenta-dark rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-magenta/50">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold mb-3">Live Configuration</h3>
            <p class="text-gray-300 leading-relaxed">Control your stream challenges in real-time from your phone, tablet, or laptop. No app install required—just a browser.</p>
          </div>

          <div class="bg-gray-800/60 backdrop-blur-md rounded-2xl p-8 text-white border-2 border-gray-700 hover:border-cyan transition-all hover:scale-105 transform">
            <div class="w-14 h-14 bg-gradient-to-br from-cyan to-magenta rounded-xl flex items-center justify-center mb-4 shadow-lg">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold mb-3">Instant OBS Integration</h3>
            <p class="text-gray-300 leading-relaxed">Drop in custom overlays with zero hassle. Beautiful, responsive browser sources that wow your audience.</p>
          </div>
        </div>

        <!-- Social Proof / Status -->
        <div class="text-center mt-16">
          <div class="inline-flex items-center px-6 py-3 bg-cyan/20 backdrop-blur-sm border-2 border-cyan/50 rounded-full text-cyan-100 text-base font-semibold shadow-lg shadow-cyan/20">
            <span class="w-3 h-3 bg-cyan rounded-full mr-3 animate-pulse"></span>
            Zero Setup • Pure Browser Power • Cross-Platform
          </div>
        </div>
      </div>
    </div>
  </div>
</template>