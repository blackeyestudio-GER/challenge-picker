<script setup lang="ts">
</script>

<template>
  <footer class="bg-gray-900/95 backdrop-blur-md border-t border-gray-700 mt-auto">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Left side - Branding -->
        <div class="text-center sm:text-left">
          <p class="text-gray-400 text-sm">
            Made with <span class="text-red-500">❤</span> by 
            <span class="font-semibold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta">Blackeye1987</span>
          </p>
        </div>

        <!-- Right side - Support Links -->
        <div class="flex items-center gap-4">
          <span class="text-gray-400 text-sm hidden sm:inline">Support the project:</span>
          
          <!-- Patreon Link -->
          <a
            href="https://www.patreon.com/c/Blackeye1987"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003"/>
            </svg>
            <span>Patreon</span>
          </a>

          <!-- Ko-fi Link -->
          <a
            href="https://ko-fi.com/blackeye1987"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M23.881 8.948c-.773-4.085-4.859-4.593-4.859-4.593H.723c-.604 0-.679.798-.679.798s-.082 7.324-.022 11.822c.164 2.424 2.586 2.672 2.586 2.672s8.267-.023 11.966-.049c2.438-.426 2.683-2.566 2.658-3.734 4.352.24 7.422-2.831 6.649-6.916zm-11.062 3.511c-1.246 1.453-4.011 3.976-4.011 3.976s-.121.119-.31.023c-.076-.057-.108-.09-.108-.09-.443-.441-3.368-3.049-4.034-3.954-.709-.965-1.041-2.7-.091-3.71.951-1.01 3.005-1.086 4.363.407 0 0 1.565-1.782 3.468-.963 1.904.82 1.832 3.011.723 4.311z"/>
            </svg>
            <span>Ko-fi</span>
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Ensure smooth transitions */
a {
  transition: all 0.3s ease;
}
</style>
