<template>
  <div class="min-h-screen flex flex-col" style="background-color: var(--color-bg-primary);">
    <NuxtLink class="skip-link" to="#main-content">
      Skip to main content
    </NuxtLink>
    <AppHeader />

    <main id="main-content" class="flex-1" tabindex="-1">
      <slot />
    </main>
    
    <AppFooter />
  </div>
</template>
