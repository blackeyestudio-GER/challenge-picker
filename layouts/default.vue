<template>
  <div class="min-h-screen flex flex-col bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800">
    <AppHeader />
    
    <main class="flex-1">
      <slot />
    </main>
    
    <AppFooter />
  </div>
</template>
