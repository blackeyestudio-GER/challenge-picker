<script setup lang="ts">
definePageMeta({
  middleware: 'admin'
})
</script>

<template>
  <div class="admin-dashboard">
    <!-- Header -->
    <div class="admin-dashboard__header">
      <h1 class="admin-dashboard__title">
        Admin Dashboard
      </h1>
      <p class="admin-dashboard__description">Manage your platform content and settings</p>
    </div>

    <!-- Admin Cards Grid -->
    <div class="admin-dashboard__cards">
      <!-- Categories -->
      <NuxtLink
        to="/admin/categories"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #2563eb, #06b6d4);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:folder" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Categories</h3>
        <p class="admin-dashboard__card-description">Organize games into categories</p>
      </NuxtLink>

      <!-- Games -->
      <NuxtLink
        to="/admin/games"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #9333ea, #ec4899);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:puzzle-piece" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Games</h3>
        <p class="admin-dashboard__card-description">Manage game library</p>
      </NuxtLink>

      <!-- Rulesets -->
      <NuxtLink
        to="/admin/rulesets"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #16a34a, #10b981);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:document-duplicate" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Rulesets</h3>
        <p class="admin-dashboard__card-description">Create challenge rulesets</p>
      </NuxtLink>

      <!-- Rules -->
      <NuxtLink
        to="/admin/rules"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #ea580c, #ef4444);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:list-bullet" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Rules</h3>
        <p class="admin-dashboard__card-description">Define individual rules</p>
      </NuxtLink>

      <!-- Card Designs -->
      <NuxtLink
        to="/admin/designs"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #4f46e5, #9333ea);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:paint-brush" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Card Designs</h3>
        <p class="admin-dashboard__card-description">Manage Tarot card sets</p>
      </NuxtLink>

      <!-- Icons -->
      <NuxtLink
        to="/admin/icons"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #ca8a04, #ea580c);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:photo" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Icons</h3>
        <p class="admin-dashboard__card-description">Browse rule icons</p>
      </NuxtLink>

      <!-- Shop -->
      <NuxtLink
        to="/admin/shop"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #db2777, #e11d48);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:shopping-bag" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Shop</h3>
        <p class="admin-dashboard__card-description">Manage shop settings</p>
      </NuxtLink>

      <!-- Features -->
      <NuxtLink
        to="/admin/features"
        class="admin-dashboard__card"
        style="background: linear-gradient(to bottom right, #0d9488, #06b6d4);"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:cog-6-tooth" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Features</h3>
        <p class="admin-dashboard__card-description">Toggle platform features</p>
      </NuxtLink>
    </div>

    <!-- Quick Stats -->
    <div class="admin-dashboard__stats">
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:folder" class="admin-dashboard__stat-icon" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Categories</p>
            <p class="admin-dashboard__stat-value">--</p>
          </div>
        </div>
      </div>
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:puzzle-piece" class="admin-dashboard__stat-icon admin-dashboard__stat-icon--purple" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Games</p>
            <p class="admin-dashboard__stat-value">--</p>
          </div>
        </div>
      </div>
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:document-duplicate" class="admin-dashboard__stat-icon admin-dashboard__stat-icon--green" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Rulesets</p>
            <p class="admin-dashboard__stat-value">--</p>
          </div>
        </div>
      </div>
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:list-bullet" class="admin-dashboard__stat-icon admin-dashboard__stat-icon--orange" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Rules</p>
            <p class="admin-dashboard__stat-value">--</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

