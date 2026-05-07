<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdminStats, type AdminStats } from '~/composables/useAdminStats'

definePageMeta({
  middleware: 'admin'
})

const { fetchAdminStats, loading, error } = useAdminStats()
const stats = ref<AdminStats | null>(null)

onMounted(async () => {
  try {
    stats.value = await fetchAdminStats()
  } catch {
    // Error state is provided by the composable and rendered below.
  }
})
</script>

<template>
  <div class="admin-dashboard">
    <section class="admin-dashboard__hero">
      <article class="admin-dashboard__hero-card admin-dashboard__hero-card--primary">
        <p class="admin-dashboard__eyebrow">Administration</p>
        <h1 class="page-title">
          Admin Dashboard
        </h1>
        <p class="page-description">Manage platform content, features and supporting operations.</p>
      </article>

      <article class="admin-dashboard__hero-card admin-dashboard__hero-card--status">
        <span class="admin-dashboard__hero-label">Admin scope</span>
        <strong class="admin-dashboard__hero-value">Core management</strong>
        <div class="admin-dashboard__hero-pills">
          <span class="admin-dashboard__hero-pill">Content</span>
          <span class="admin-dashboard__hero-pill">Commerce</span>
          <span class="admin-dashboard__hero-pill">Features</span>
        </div>
      </article>
    </section>

    <!-- Admin Cards Grid -->
    <div class="admin-dashboard__cards">
      <!-- Categories -->
      <NuxtLink
        to="/admin/categories"
        class="admin-dashboard__card admin-dashboard__card--variant-1"
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
        class="admin-dashboard__card admin-dashboard__card--variant-2"
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
        class="admin-dashboard__card admin-dashboard__card--variant-3"
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
        class="admin-dashboard__card admin-dashboard__card--variant-4"
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
        class="admin-dashboard__card admin-dashboard__card--variant-5"
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
        class="admin-dashboard__card admin-dashboard__card--variant-1"
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
        class="admin-dashboard__card admin-dashboard__card--variant-2"
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
        class="admin-dashboard__card admin-dashboard__card--variant-3"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:cog-6-tooth" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Features</h3>
        <p class="admin-dashboard__card-description">Toggle platform features</p>
      </NuxtLink>

      <!-- Component Test -->
      <NuxtLink
        to="/admin/component-test"
        class="admin-dashboard__card admin-dashboard__card--variant-4"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:beaker" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Component Test</h3>
        <p class="admin-dashboard__card-description">Visual reference for all UI components</p>
      </NuxtLink>

      <!-- Payout Requests -->
      <NuxtLink
        to="/admin/payouts"
        class="admin-dashboard__card admin-dashboard__card--variant-1"
      >
        <div class="admin-dashboard__card-icon-wrapper">
          <Icon name="heroicons:banknotes" class="admin-dashboard__card-icon" />
        </div>
        <h3 class="admin-dashboard__card-title">Payout Requests</h3>
        <p class="admin-dashboard__card-description">Manage artist payout requests</p>
      </NuxtLink>
    </div>

    <LoadingState v-if="loading && !stats" message="Loading admin overview..." />

    <ErrorState v-else-if="error && !stats" :message="error" />

    <!-- Quick Stats -->
    <!-- Stats Section (Hidden on Mobile) -->
    <div v-else class="admin-dashboard__stats hidden md:grid">
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:folder" class="admin-dashboard__stat-icon" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Categories</p>
            <p class="admin-dashboard__stat-value">
              <span v-if="loading">...</span>
              <span v-else-if="stats">{{ stats.categories }}</span>
              <span v-else>--</span>
            </p>
          </div>
        </div>
      </div>
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:puzzle-piece" class="admin-dashboard__stat-icon admin-dashboard__stat-icon--purple" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Games</p>
            <p class="admin-dashboard__stat-value">
              <span v-if="loading">...</span>
              <span v-else-if="stats">{{ stats.games }}</span>
              <span v-else>--</span>
            </p>
          </div>
        </div>
      </div>
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:document-duplicate" class="admin-dashboard__stat-icon admin-dashboard__stat-icon--green" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Rulesets</p>
            <p class="admin-dashboard__stat-value">
              <span v-if="loading">...</span>
              <span v-else-if="stats">{{ stats.rulesets }}</span>
              <span v-else>--</span>
            </p>
          </div>
        </div>
      </div>
      <div class="admin-dashboard__stat-card">
        <div class="admin-dashboard__stat-content">
          <Icon name="heroicons:list-bullet" class="admin-dashboard__stat-icon admin-dashboard__stat-icon--orange" />
          <div class="admin-dashboard__stat-info">
            <p class="admin-dashboard__stat-label">Rules</p>
            <p class="admin-dashboard__stat-value">
              <span v-if="loading">...</span>
              <span v-else-if="stats">{{ stats.rules }}</span>
              <span v-else>--</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
