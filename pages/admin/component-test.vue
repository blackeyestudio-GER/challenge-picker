<script setup lang="ts">
import { Icon } from '#components'
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

definePageMeta({
  middleware: 'admin'
})

const { currentTheme, availableThemes, switchTheme } = useThemeSwitcher()
const activeFilter = ref('all')
const filters = [
  { id: 'all', label: 'All', count: 42 },
  { id: 'active', label: 'Active', count: 12 },
  { id: 'pending', label: 'Pending', count: 8 },
  { id: 'completed', label: 'Completed', count: 22 }
]

const handleThemeSelect = (themeName: string) => {
  switchTheme(themeName as 'default' | 'light')
}

// Get 5 main colors for each theme (predefined)
const getThemeColors = (themeName: string): string[] => {
  const themeColors: Record<string, string[]> = {
    default: [
      '#111212',  // bg-primary (dark)
      '#06b6d4',  // accent-primary (cyan)
      '#d946ef',  // accent-secondary (magenta)
      '#eab308',  // legendary (gold)
      '#a855f7',  // court (purple)
    ],
    light: [
      '#f8f9fa',  // bg-primary (soft off-white)
      '#155e75',  // accent-primary-dark (dark cyan for text)
      '#86198f',  // accent-secondary-dark (dark magenta for text)
      '#b8860b',  // legendary (dark amber)
      '#9333ea',  // court (purple)
    ],
  }
  return themeColors[themeName] || themeColors.default
}

// Get badge background color for each theme
const getThemeBadgeBgColor = (themeName: string): string => {
  const badgeColors: Record<string, string> = {
    default: 'rgba(6, 182, 212, 0.2)',  // cyan with opacity for dark theme
    light: 'rgba(165, 243, 252, 0.25)',   // pastel cyan for light theme
  }
  return badgeColors[themeName] || badgeColors.default
}

// Get badge text color for each theme
const getThemeBadgeTextColor = (themeName: string): string => {
  const textColors: Record<string, string> = {
    default: '#06b6d4',  // bright cyan for dark theme
    light: '#155e75',    // darker cyan for light theme (better contrast)
  }
  return textColors[themeName] || textColors.default
}

// Get badge border color for each theme
const getThemeBadgeBorderColor = (themeName: string): string => {
  const borderColors: Record<string, string> = {
    default: 'rgba(6, 182, 212, 0.5)',  // cyan border for dark theme
    light: 'rgba(165, 243, 252, 0.6)',   // pastel cyan border for light theme
  }
  return borderColors[themeName] || borderColors.default
}
</script>

<template>
  <div class="admin-dashboard">
    <!-- Theme Switcher at Top (from themes page) -->
    <div class="mb-6">
      <h3 class="subsection-title mb-4">Switch Theme</h3>
      <div class="themes-page__grid">
        <div
          v-for="theme in availableThemes"
          :key="theme.name"
          :class="[
            'themes-page__card',
            currentTheme === theme.name 
              ? 'themes-page__card--active' 
              : 'themes-page__card--inactive'
          ]"
          @click="handleThemeSelect(theme.name)"
        >
          <!-- Color Stripes -->
          <div class="themes-page__card-stripes">
            <div
              v-for="(color, index) in getThemeColors(theme.name)"
              :key="index"
              :style="{ backgroundColor: color }"
              class="themes-page__card-stripe"
            />
          </div>

          <!-- Content -->
          <div class="themes-page__card-content">
            <div class="themes-page__card-header">
              <h3 class="themes-page__card-title">{{ theme.label }}</h3>
              <Icon
                v-if="currentTheme === theme.name"
                name="heroicons:check-circle"
                class="themes-page__card-check"
              />
            </div>
            <p class="themes-page__card-description">{{ theme.description }}</p>
            
            <!-- Selected Badge -->
            <div
              v-if="currentTheme === theme.name"
              class="themes-page__card-badge"
              :style="{
                backgroundColor: getThemeBadgeBgColor(theme.name),
                color: getThemeBadgeTextColor(theme.name),
                borderColor: getThemeBadgeBorderColor(theme.name)
              }"
            >
              <Icon name="heroicons:check" class="themes-page__card-badge-icon" />
              <span>Active</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Component Test Page</h1>
      <p class="page-description">
        Visual reference for all unified components. Switch themes using the button above to verify consistency.
      </p>
    </div>

    <!-- Headlines Section -->
    <div class="section-divider">
      <h2 class="section-divider__title">Headlines</h2>
    </div>

    <div class="page-card space-y-6">
      <div>
        <h1 class="page-title">Page Title (h1)</h1>
        <p class="text-sm text-theme-muted mt-2">.page-title - Main page headline</p>
      </div>

      <div>
        <p class="page-description">Page Description - Subtitle below page title</p>
        <p class="text-sm text-theme-muted mt-2">.page-description</p>
      </div>

      <div>
        <h2 class="section-title">Section Title (h2)</h2>
        <p class="text-sm text-theme-muted mt-2">.section-title - Major section divider</p>
      </div>

      <div>
        <h3 class="subsection-title">Subsection Title (h3)</h3>
        <p class="text-sm text-theme-muted mt-2">.subsection-title - Minor section divider</p>
      </div>

      <div class="page-card">
        <h3 class="card-title">Card Title (h3)</h3>
        <p class="card-subtitle">Card Subtitle - Description within cards</p>
        <p class="text-sm text-theme-muted mt-2">.card-title and .card-subtitle</p>
      </div>
    </div>

    <!-- Cards Section -->
    <div class="section-divider">
      <h2 class="section-divider__title">Cards</h2>
    </div>

    <div class="page-card space-y-6">
      <div>
        <h3 class="subsection-title">Unified Card Classes (New Standard)</h3>
        <p class="text-theme-secondary mb-4">These are the new unified classes that should be used going forward:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Page Card -->
          <div class="page-card">
            <h3 class="card-title">Page Card</h3>
            <p class="card-subtitle">Standard content card (.page-card)</p>
            <p class="text-sm text-theme-secondary mt-2">
              Hover to see border and shadow effects.
            </p>
          </div>

          <!-- Content Card -->
          <div class="content-card">
            <h3 class="card-title">Content Card</h3>
            <p class="card-subtitle">Clickable card (.content-card)</p>
            <p class="text-sm text-theme-secondary mt-2">
              Hover to see lift effect.
            </p>
          </div>

          <!-- Feature Card -->
          <div class="feature-card">
            <div class="feature-card__icon-wrapper">
              <Icon name="heroicons:sparkles" class="feature-card__icon" />
            </div>
            <h3 class="feature-card__title">Feature Card</h3>
            <p class="feature-card__description">
              Card with icon (.feature-card)
            </p>
          </div>

          <!-- Stat Card -->
          <div class="stat-card">
            <div class="stat-card__value">1,234</div>
            <div class="stat-card__label">Total Items</div>
          </div>

          <div class="stat-card">
            <div class="stat-card__value">56</div>
            <div class="stat-card__label">Active</div>
          </div>

          <div class="stat-card">
            <div class="stat-card__value">89%</div>
            <div class="stat-card__label">Success Rate</div>
          </div>
        </div>
      </div>

      <div>
        <h3 class="subsection-title">Actual Cards Used in System</h3>
        <p class="text-theme-secondary mb-4">These are the actual card classes currently used in pages:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Admin Dashboard Card -->
          <NuxtLink
            to="/admin"
            class="admin-dashboard__card admin-dashboard__card--variant-1"
          >
            <div class="admin-dashboard__card-icon-wrapper">
              <Icon name="heroicons:beaker" class="admin-dashboard__card-icon" />
            </div>
            <h3 class="admin-dashboard__card-title">Admin Card</h3>
            <p class="admin-dashboard__card-description">.admin-dashboard__card</p>
          </NuxtLink>

          <!-- Play Page Card -->
          <div class="play-page__card rounded-2xl p-4 border">
            <h3 class="play-page__card-title">Play Page Card</h3>
            <p class="play-page__card-subtitle">.play-page__card (used in play pages)</p>
          </div>

          <!-- Dashboard Action Card -->
          <div class="dashboard-page__action-card dashboard-page__action-card--cyan">
            <div class="dashboard-page__action-icon-wrapper">
              <Icon name="heroicons:play-circle" class="dashboard-page__action-icon" />
            </div>
            <div class="dashboard-page__action-content">
              <h3 class="dashboard-page__action-title">Action Card</h3>
              <p class="dashboard-page__action-description">.dashboard-page__action-card</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Buttons Section -->
    <div class="section-divider">
      <h2 class="section-divider__title">Buttons</h2>
    </div>

    <div class="page-card space-y-6">
      <div>
        <h3 class="subsection-title">Button Usage</h3>
        <p class="text-theme-secondary mb-4">
          Buttons can be used with <code>.btn</code> base class (recommended) or without it. 
          The base class provides consistent padding, sizing, and hover effects.
        </p>
      </div>

      <!-- Button Variants -->
      <div>
        <h3 class="subsection-title">Button Variants (with .btn base class)</h3>
        <div class="flex flex-wrap gap-3 mt-4">
          <button class="btn btn-primary">Primary</button>
          <button class="btn btn-secondary">Secondary</button>
          <button class="btn btn-success">Success</button>
          <button class="btn btn-danger">Danger</button>
          <button class="btn btn-warning">Warning</button>
        </div>
      </div>

      <!-- Button Variants without base class -->
      <div>
        <h3 class="subsection-title">Button Variants (without .btn base class)</h3>
        <p class="text-sm text-theme-muted mb-2">These also work but won't have the base styling:</p>
        <div class="flex flex-wrap gap-3 mt-4">
          <button class="btn-primary">Primary</button>
          <button class="btn-secondary">Secondary</button>
          <button class="btn-success">Success</button>
          <button class="btn-danger">Danger</button>
          <button class="btn-warning">Warning</button>
        </div>
      </div>

      <!-- Button Sizes -->
      <div>
        <h3 class="subsection-title">Button Sizes</h3>
        <div class="flex flex-wrap items-center gap-3 mt-4">
          <button class="btn btn-primary btn-sm">Small</button>
          <button class="btn btn-primary">Default</button>
          <button class="btn btn-primary btn-lg">Large</button>
        </div>
      </div>

      <!-- Full Width Button -->
      <div>
        <h3 class="subsection-title">Full Width Button</h3>
        <button class="btn btn-primary btn-full mt-4">Full Width Button</button>
      </div>

      <!-- Disabled Buttons -->
      <div>
        <h3 class="subsection-title">Disabled State</h3>
        <div class="flex flex-wrap gap-3 mt-4">
          <button class="btn btn-primary" disabled>Disabled Primary</button>
          <button class="btn btn-secondary" disabled>Disabled Secondary</button>
        </div>
      </div>

      <!-- Buttons with Icons -->
      <div>
        <h3 class="subsection-title">Buttons with Icons</h3>
        <div class="flex flex-wrap gap-3 mt-4">
          <button class="btn btn-primary">
            <Icon name="heroicons:plus" class="w-5 h-5" />
            Add Item
          </button>
          <button class="btn btn-secondary">
            <Icon name="heroicons:pencil" class="w-5 h-5" />
            Edit
          </button>
          <button class="btn btn-danger">
            <Icon name="heroicons:trash" class="w-5 h-5" />
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="section-divider">
      <h2 class="section-divider__title">Filters</h2>
    </div>

    <div class="page-card space-y-6">
      <div>
        <h3 class="subsection-title">Unified Filter Classes (New Standard)</h3>
        <p class="text-theme-secondary mb-4">These are the new unified filter classes for future use:</p>
        <div class="filter-list">
          <button
            v-for="filter in filters"
            :key="filter.id"
            :class="[
              'filter-item',
              { 'filter-item--active': activeFilter === filter.id }
            ]"
            @click="activeFilter = filter.id"
          >
            {{ filter.label }}
            <span class="filter-badge">{{ filter.count }}</span>
          </button>
        </div>
      </div>

      <div>
        <h3 class="subsection-title">Actual Filter Component</h3>
        <p class="text-theme-secondary mb-4">The actual filter component used in the system is <code>CategoryFilterList</code> with <code>.category-filter-list__chip</code> classes.</p>
        <p class="text-sm text-theme-muted">See <code>components/CategoryFilterList.vue</code> for the actual implementation.</p>
      </div>
    </div>

    <!-- Theme Colors Section -->
    <div class="section-divider">
      <h2 class="section-divider__title">Theme Colors</h2>
    </div>

    <div class="page-card">
      <h3 class="subsection-title mb-4">Background Colors</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div>
          <div class="h-20 rounded-lg bg-theme-primary border border-theme-primary mb-2"/>
          <p class="text-sm text-theme-secondary">Primary BG</p>
        </div>
        <div>
          <div class="h-20 rounded-lg bg-theme-secondary border border-theme-secondary mb-2"/>
          <p class="text-sm text-theme-secondary">Secondary BG</p>
        </div>
        <div>
          <div class="h-20 rounded-lg bg-theme-tertiary border border-theme-tertiary mb-2"/>
          <p class="text-sm text-theme-secondary">Tertiary BG</p>
        </div>
        <div>
          <div class="h-20 rounded-lg bg-theme-card border border-theme-primary mb-2"/>
          <p class="text-sm text-theme-secondary">Card BG</p>
        </div>
      </div>

      <h3 class="subsection-title mb-4">Text Colors</h3>
      <div class="space-y-2">
        <p class="text-theme-primary">Primary Text (.text-theme-primary)</p>
        <p class="text-theme-secondary">Secondary Text (.text-theme-secondary)</p>
        <p class="text-theme-tertiary">Tertiary Text (.text-theme-tertiary)</p>
        <p class="text-theme-muted">Muted Text (.text-theme-muted)</p>
      </div>

      <h3 class="subsection-title mb-4 mt-6">Border Colors</h3>
      <div class="space-y-2">
        <div class="p-4 border-2 border-theme-primary rounded-lg">
          <p class="text-sm text-theme-secondary">Primary Border (.border-theme-primary)</p>
        </div>
        <div class="p-4 border-2 border-theme-secondary rounded-lg">
          <p class="text-sm text-theme-secondary">Secondary Border (.border-theme-secondary)</p>
        </div>
        <div class="p-4 border-2 border-theme-accent rounded-lg">
          <p class="text-sm text-theme-secondary">Accent Border (.border-theme-accent)</p>
        </div>
      </div>
    </div>

    <!-- Layout Examples -->
    <div class="section-divider">
      <h2 class="section-divider__title">Layout Examples</h2>
    </div>

    <!-- Page Header Example -->
    <div class="page-card">
      <h3 class="subsection-title mb-4">Page Header Example</h3>
      <div class="page-header border-b border-theme-primary pb-4">
        <h1 class="page-title">Example Page</h1>
        <p class="page-description">This is how a page header looks</p>
      </div>
    </div>

    <!-- Section Divider Example -->
    <div class="page-card mt-6">
      <h3 class="subsection-title mb-4">Section Divider Example</h3>
      <div class="section-divider">
        <h2 class="section-divider__title">New Section</h2>
      </div>
      <p class="text-theme-secondary mt-4">Content after section divider...</p>
    </div>

    <!-- Card Grid Example -->
    <div class="page-card mt-6">
      <h3 class="subsection-title mb-4">Card Grid Example</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="content-card">
          <h3 class="card-title">Card 1</h3>
          <p class="card-subtitle">Content card example</p>
        </div>
        <div class="content-card">
          <h3 class="card-title">Card 2</h3>
          <p class="card-subtitle">Content card example</p>
        </div>
        <div class="content-card">
          <h3 class="card-title">Card 3</h3>
          <p class="card-subtitle">Content card example</p>
        </div>
      </div>
    </div>

    <!-- Interactive States -->
    <div class="section-divider">
      <h2 class="section-divider__title">Interactive States</h2>
    </div>

    <div class="page-card">
      <h3 class="subsection-title mb-4">Hover States</h3>
      <p class="text-theme-secondary mb-4">Hover over the elements below to see hover effects:</p>
      
      <div class="space-y-4">
        <div class="flex flex-wrap gap-4">
          <button class="btn btn-primary">Hover Me (Button)</button>
          <div class="content-card w-48">
            <p class="text-sm">Hover Me (Card)</p>
          </div>
          <div class="filter-item">Hover Me (Filter)</div>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="section-divider">
      <h2 class="section-divider__title">Summary</h2>
    </div>

    <div class="page-card">
      <h3 class="subsection-title mb-4">Class Status</h3>
      <div class="space-y-3 text-sm">
        <div class="flex items-start gap-2">
          <Icon name="heroicons:check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
          <div>
            <strong class="text-theme-primary">Unified Classes (New Standard):</strong>
            <p class="text-theme-secondary">These classes are defined in theme files and should be used for new pages: 
              <code>.page-title</code>, <code>.section-title</code>, <code>.page-card</code>, <code>.content-card</code>, 
              <code>.feature-card</code>, <code>.stat-card</code>, <code>.btn</code>, <code>.filter-item</code>
            </p>
          </div>
        </div>
        <div class="flex items-start gap-2">
          <Icon name="heroicons:information-circle" class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
          <div>
            <strong class="text-theme-primary">Legacy Classes (Still in Use):</strong>
            <p class="text-theme-secondary">These classes are still used in existing pages and will be migrated over time: 
              <code>.admin-dashboard__card</code>, <code>.play-page__card</code>, <code>.dashboard-page__action-card</code>, 
              <code>.category-filter-list__chip</code>
            </p>
          </div>
        </div>
        <div class="flex items-start gap-2">
          <Icon name="heroicons:light-bulb" class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" />
          <div>
            <strong class="text-theme-primary">Theme Variables:</strong>
            <p class="text-theme-secondary">All colors use CSS variables from <code>themes/default.css</code> and <code>themes/light.css</code>. 
              Switch themes using the buttons at the top to verify consistency.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Ensure proper spacing */
.space-y-6 > * + * {
  margin-top: 1.5rem;
}

.space-y-4 > * + * {
  margin-top: 1rem;
}

.space-y-2 > * + * {
  margin-top: 0.5rem;
}
</style>

