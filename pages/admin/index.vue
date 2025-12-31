<script setup lang="ts">
definePageMeta({
  middleware: 'admin'
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">
        Admin Dashboard
      </h1>
      <p class="text-gray-300">Manage your platform content and settings</p>
    </div>

    <!-- Admin Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Categories -->
      <NuxtLink
        to="/admin/categories"
        class="bg-gradient-to-br from-blue-600 to-cyan-600 text-white rounded-xl shadow-lg hover:shadow-cyan/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-blue/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:folder" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Categories</h3>
        <p class="text-sm text-blue-100">Organize games into categories</p>
      </NuxtLink>

      <!-- Games -->
      <NuxtLink
        to="/admin/games"
        class="bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-xl shadow-lg hover:shadow-purple/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-purple/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:puzzle-piece" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Games</h3>
        <p class="text-sm text-purple-100">Manage game library</p>
      </NuxtLink>

      <!-- Rulesets -->
      <NuxtLink
        to="/admin/rulesets"
        class="bg-gradient-to-br from-green-600 to-emerald-600 text-white rounded-xl shadow-lg hover:shadow-green/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-green/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:document-duplicate" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Rulesets</h3>
        <p class="text-sm text-green-100">Create challenge rulesets</p>
      </NuxtLink>

      <!-- Rules -->
      <NuxtLink
        to="/admin/rules"
        class="bg-gradient-to-br from-orange-600 to-red-600 text-white rounded-xl shadow-lg hover:shadow-orange/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-orange/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:list-bullet" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Rules</h3>
        <p class="text-sm text-orange-100">Define individual rules</p>
      </NuxtLink>

      <!-- Card Designs -->
      <NuxtLink
        to="/admin/designs"
        class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-xl shadow-lg hover:shadow-indigo/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-indigo/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:paint-brush" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Card Designs</h3>
        <p class="text-sm text-indigo-100">Manage Tarot card sets</p>
      </NuxtLink>

      <!-- Icons -->
      <NuxtLink
        to="/admin/icons"
        class="bg-gradient-to-br from-yellow-600 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-yellow/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-yellow/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:photo" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Icons</h3>
        <p class="text-sm text-yellow-100">Browse rule icons</p>
      </NuxtLink>

      <!-- Shop -->
      <NuxtLink
        to="/admin/shop"
        class="bg-gradient-to-br from-pink-600 to-rose-600 text-white rounded-xl shadow-lg hover:shadow-pink/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-pink/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:shopping-bag" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Shop</h3>
        <p class="text-sm text-pink-100">Manage shop settings</p>
      </NuxtLink>

      <!-- Features -->
      <NuxtLink
        to="/admin/features"
        class="bg-gradient-to-br from-teal-600 to-cyan-600 text-white rounded-xl shadow-lg hover:shadow-teal/30 transition-all p-6 flex flex-col items-center justify-center text-center border border-teal/20 hover:scale-105 transform"
      >
        <div class="bg-white/20 rounded-full p-4 mb-4">
          <Icon name="heroicons:cog-6-tooth" class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold mb-2">Features</h3>
        <p class="text-sm text-teal-100">Toggle platform features</p>
      </NuxtLink>
    </div>

    <!-- Quick Stats (Optional) -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <Icon name="heroicons:folder" class="w-6 h-6 text-cyan" />
          <div>
            <p class="text-sm text-gray-400">Categories</p>
            <p class="text-2xl font-bold text-white">--</p>
          </div>
        </div>
      </div>
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <Icon name="heroicons:puzzle-piece" class="w-6 h-6 text-purple-500" />
          <div>
            <p class="text-sm text-gray-400">Games</p>
            <p class="text-2xl font-bold text-white">--</p>
          </div>
        </div>
      </div>
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <Icon name="heroicons:document-duplicate" class="w-6 h-6 text-green-500" />
          <div>
            <p class="text-sm text-gray-400">Rulesets</p>
            <p class="text-2xl font-bold text-white">--</p>
          </div>
        </div>
      </div>
      <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <Icon name="heroicons:list-bullet" class="w-6 h-6 text-orange-500" />
          <div>
            <p class="text-sm text-gray-400">Rules</p>
            <p class="text-2xl font-bold text-white">--</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

