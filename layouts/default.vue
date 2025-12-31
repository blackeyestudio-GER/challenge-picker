<template>
  <div class="min-h-screen flex flex-col" style="background-color: var(--color-bg-primary);">
    <AppHeader />
    
    <main class="flex-1">
      <slot />
    </main>
    
    <AppFooter />
  </div>
</template>
