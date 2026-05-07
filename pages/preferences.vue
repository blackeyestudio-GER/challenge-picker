<script setup lang="ts">
import { Icon } from '#components'
import { usePreferencesPage as usePreferencesPageComposable } from '~/composables/pages/usePreferencesPage'

definePageMeta({
  middleware: 'auth'
})

const {
  preferences,
  loading,
  error,
  designsLoading,
  availableDesigns,
  activeDesignId,
  designSuccess,
  designError,
  obsUrls,
  bootstrap,
  openUrl,
  fetchPreferences,
  preferencePreviewTiles,
  handleDesignChange,
  updatePref
} = usePreferencesPageComposable()

onMounted(async () => {
  await bootstrap()
})

</script>

<template>
  <div class="obs-sources-page">
      <section class="preferences-page__hero">
        <article class="preferences-page__hero-card preferences-page__hero-card--primary">
          <p class="preferences-page__eyebrow">Workspace Setup</p>
          <h1 class="page-title">Preferences</h1>
          <p class="page-description">Configure your general settings and streaming overlays.</p>
        </article>

        <article class="preferences-page__hero-card preferences-page__hero-card--status">
          <span class="preferences-page__hero-label">Setup status</span>
          <strong class="preferences-page__hero-value">{{ availableDesigns.length }} designs</strong>
          <div class="preferences-page__hero-pills">
            <span class="preferences-page__hero-pill">3 overlay URLs</span>
            <span class="preferences-page__hero-pill">{{ activeDesignId ? 'Design active' : 'Choose design' }}</span>
          </div>
        </article>
      </section>

      <!-- ========== GENERAL SETTINGS SECTION ========== -->
      <div class="section-divider">
        <h2 class="section-divider__title">General Settings</h2>
      </div>

      <!-- Card Design Section -->
      <div class="obs-sources-page__overlay-card">
        <div class="obs-sources-page__overlay-header">
          <div class="obs-sources-page__overlay-icon-wrapper">
            <Icon name="heroicons:sparkles" class="obs-sources-page__overlay-icon" />
          </div>
          <div class="obs-sources-page__overlay-content">
            <h2 class="obs-sources-page__overlay-title">Card Design</h2>
            <p class="obs-sources-page__overlay-description">Choose how you want your rule cards to be displayed</p>
            <p class="obs-sources-page__overlay-hint">Free designs and designs you've purchased are available</p>
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="designSuccess" class="obs-sources-page__success-message">
          {{ designSuccess }}
        </div>
        
        <!-- Error Message -->
        <div v-if="designError" class="obs-sources-page__error-message">
          {{ designError }}
        </div>

        <LoadingState v-if="designsLoading" message="Loading card designs..." />

        <ErrorState v-else-if="designError && availableDesigns.length === 0" :message="designError" />

        <EmptyState
          v-else-if="availableDesigns.length === 0"
          icon="heroicons:sparkles"
          title="No card designs available"
          message="There are currently no card designs available for your account."
        />

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
          <div
            v-for="design in availableDesigns"
            :key="design.id"
            :class="[
              'preferences-design-card cursor-pointer border-2 rounded-lg p-4 transition-all hover:shadow-lg',
              activeDesignId === design.id
                ? 'preferences-design-card--active'
                : 'preferences-design-card--inactive'
            ]"
            @click="handleDesignChange(design.id)"
          >
            <DesignSetPreviewMosaic
              class="mb-3"
              :images="preferencePreviewTiles(design)"
              :alt-prefix="design.name || design.designName || 'Card design'"
              variant="compact"
            />
            <div class="flex items-start justify-between mb-2">
              <h3 class="preferences-design-card__title font-semibold">{{ design.name ?? design.designName }}</h3>
              <span v-if="design.isFree" class="text-xs px-2 py-1 rounded preferences-design-card__badge preferences-design-card__badge--free">FREE</span>
              <span v-else-if="design.isPremium" class="text-xs px-2 py-1 rounded preferences-design-card__badge preferences-design-card__badge--premium">PREMIUM</span>
            </div>
            
            <p v-if="design.description" class="preferences-design-card__description text-sm mb-3">{{ design.description }}</p>
            
            <div class="flex items-center justify-between text-xs preferences-design-card__meta">
              <span>{{ design.type === 'template' ? 'Template' : 'Full Set' }}</span>
              <span v-if="design.theme" class="capitalize">{{ design.theme }}</span>
            </div>
            
            <div v-if="activeDesignId === design.id" class="mt-3 flex items-center gap-2 preferences-design-card__active-indicator text-sm">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="font-medium">Active</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ========== OBS BROWSER SOURCES SECTION ========== -->
      <ErrorState v-if="error" :message="error" />

      <div class="obs-sources-page__section-divider">
        <h2 class="obs-sources-page__section-divider-title">OBS Browser Sources</h2>
        <p class="obs-sources-page__section-divider-description">Configure overlay URLs for your streaming software</p>
      </div>

      <!-- Important Info Box -->
      <div class="obs-sources-page__info-box">
        <div class="obs-sources-page__info-content">
          <div class="obs-sources-page__info-emoji">ℹ️</div>
          <div class="obs-sources-page__info-text">
            <h3 class="obs-sources-page__info-title">How It Works</h3>
            <p class="obs-sources-page__info-paragraph">
              You get <strong>permanent URLs</strong> that are <strong>unique to your account</strong>.
            </p>
            <p class="obs-sources-page__info-paragraph obs-sources-page__info-paragraph--muted">
              Set them up in OBS once, and they'll automatically display whichever game you're currently playing! No authentication needed, so viewers can access them too.
            </p>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error && !preferences" class="obs-sources-page__error">
        <h3 class="obs-sources-page__error-title">Failed to Load OBS Preferences</h3>
        <p class="obs-sources-page__error-message">{{ error }}</p>
        <button 
          class="obs-sources-page__error-button" 
          @click="fetchPreferences()"
        >
          Retry
        </button>
      </div>

      <!-- Always show the URLs and settings -->
      <div class="space-y-6">
        <!-- Timer Overlay -->
        <div class="obs-sources-page__overlay-card">
          <div class="obs-sources-page__overlay-header">
            <div class="obs-sources-page__overlay-icon-wrapper">
              <Icon name="heroicons:clock" class="obs-sources-page__overlay-icon" />
            </div>
            <div class="obs-sources-page__overlay-content">
              <h2 class="obs-sources-page__overlay-title">Timer Overlay</h2>
              <p class="obs-sources-page__overlay-description">Displays the session elapsed time since start</p>
              <p class="obs-sources-page__overlay-hint">Shows: HH:MM:SS or MM:SS format</p>
            </div>
          </div>

          <!-- URL Actions -->
          <div class="obs-sources-page__actions">
            <CopyButton :url="obsUrls.timer" label="Copy URL" />
            <button
              class="obs-sources-page__preview-button"
              @click="openUrl(obsUrls.timer)"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>

          <!-- Timer Design Style -->
          <div class="obs-sources-page__section">
            <h3 class="subsection-title">Design Style</h3>
            <div v-if="loading && !preferences" class="obs-sources-page__loading">
              <div>Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-3">
              <div class="obs-sources-page__design-option">
                <label class="obs-sources-page__design-label">
                  <input
                    type="radio"
                    name="timerDesign"
                    value="numbers"
                    :checked="preferences.timerDesign === 'numbers'"
                    class="obs-sources-page__design-radio"
                    @change="updatePref('timerDesign', 'numbers')"
                  >
                  <span>Numbers (HH:MM:SS or MM:SS)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.timer" 
                  design="numbers" 
                  label="Test Link"
                />
              </div>
              <p class="obs-sources-page__design-hint">More styles coming soon...</p>
              <p class="obs-sources-page__design-tip">💡 Use "Test Link" to copy the URL with a specific design without changing your saved preference</p>
            </div>
          </div>
        </div>

        <!-- Rules Overlay -->
        <div class="obs-sources-page__overlay-card">
          <div class="obs-sources-page__overlay-header">
            <div class="obs-sources-page__overlay-icon-wrapper obs-sources-page__overlay-icon-wrapper--magenta">
              <Icon name="heroicons:list-bullet" class="obs-sources-page__overlay-icon obs-sources-page__overlay-icon--magenta" />
            </div>
            <div class="obs-sources-page__overlay-content">
              <h2 class="obs-sources-page__overlay-title">Rules Overlay</h2>
              <p class="obs-sources-page__overlay-description">Shows currently active rules during gameplay</p>
              <p class="obs-sources-page__overlay-hint">Shows: List of rules with countdown timers</p>
            </div>
          </div>

          <!-- URL Actions -->
          <div class="obs-sources-page__actions">
            <CopyButton :url="obsUrls.rules" label="Copy URL" />
            <button
              class="obs-sources-page__preview-button"
              @click="openUrl(obsUrls.rules)"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>


          <!-- Rules Design Style -->
          <div class="obs-sources-page__section">
            <h3 class="subsection-title">Design Style</h3>
            <div v-if="loading && !preferences" class="obs-sources-page__loading">
              <div>Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-3">
              <div class="obs-sources-page__design-option">
                <label class="obs-sources-page__design-label">
                  <input
                    type="radio"
                    name="rulesDesign"
                    value="list"
                    :checked="preferences.rulesDesign === 'list'"
                    class="obs-sources-page__design-radio"
                    @change="updatePref('rulesDesign', 'list')"
                  >
                  <span>List (full-screen text list with timer on right)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.rules" 
                  design="list" 
                  label="Test Link"
                />
              </div>
              <p class="obs-sources-page__design-hint">More layouts coming soon...</p>
              <p class="obs-sources-page__design-tip">💡 Use "Test Link" to copy the URL with a specific design without changing your saved preference</p>
            </div>
          </div>

          <!-- Timer Position on Rules Card -->
          <div class="obs-sources-page__section">
            <h3 class="subsection-title">Timer Display on Rules Card</h3>
            <div v-if="loading && !preferences" class="obs-sources-page__loading">
              <div>Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="obs-sources-page__radio-options">
              <label class="obs-sources-page__radio-label">
                <input
                  type="radio"
                  name="timerPosition"
                  value="none"
                  :checked="preferences.timerPosition === 'none'"
                  class="obs-sources-page__radio-input"
                  @change="updatePref('timerPosition', 'none')"
                >
                <span>No timer on rules card</span>
              </label>
              <label class="obs-sources-page__radio-label">
                <input
                  type="radio"
                  name="timerPosition"
                  value="on_card"
                  :checked="preferences.timerPosition === 'on_card'"
                  class="obs-sources-page__radio-input"
                  @change="updatePref('timerPosition', 'on_card')"
                >
                <span>Show timer on rules card</span>
              </label>
              <label class="obs-sources-page__radio-label">
                <input
                  type="radio"
                  name="timerPosition"
                  value="below_card"
                  :checked="preferences.timerPosition === 'below_card'"
                  class="obs-sources-page__radio-input"
                  @change="updatePref('timerPosition', 'below_card')"
                >
                <span>Show timer below rules card</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Status Overlay -->
        <div class="obs-sources-page__overlay-card">
          <div class="obs-sources-page__overlay-header">
            <div class="obs-sources-page__overlay-icon-wrapper">
              <Icon name="heroicons:signal" class="obs-sources-page__overlay-icon" />
            </div>
            <div class="obs-sources-page__overlay-content">
              <h2 class="obs-sources-page__overlay-title">Status Overlay</h2>
              <p class="obs-sources-page__overlay-description">Shows current game session status</p>
              <p class="obs-sources-page__overlay-hint">Shows: SETUP, LIVE, PAUSED, or ENDED (as word/symbol/button)</p>
            </div>
          </div>

          <!-- URL Actions -->
          <div class="obs-sources-page__actions">
            <CopyButton :url="obsUrls.status" label="Copy URL" />
            <button
              class="obs-sources-page__preview-button"
              @click="openUrl(obsUrls.status)"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>

          <!-- Status Design Style -->
          <div class="obs-sources-page__section">
            <h3 class="subsection-title">Design Style</h3>
            <div v-if="loading && !preferences" class="obs-sources-page__loading">
              <div>Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-3">
              <div class="obs-sources-page__design-option">
                <label class="obs-sources-page__design-label">
                  <input
                    type="radio"
                    name="statusDesign"
                    value="word"
                    :checked="preferences.statusDesign === 'word'"
                    class="obs-sources-page__design-radio"
                    @change="updatePref('statusDesign', 'word')"
                  >
                  <span>Word (LIVE, PAUSED, SETUP, ENDED)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.status" 
                  design="word" 
                  label="Test Link"
                />
              </div>
              <div class="obs-sources-page__design-option">
                <label class="obs-sources-page__design-label">
                  <input
                    type="radio"
                    name="statusDesign"
                    value="symbols"
                    :checked="preferences.statusDesign === 'symbols'"
                    class="obs-sources-page__design-radio"
                    @change="updatePref('statusDesign', 'symbols')"
                  >
                  <span>Symbols (▶️ ⏸️ ⏹️ icons)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.status" 
                  design="symbols" 
                  label="Test Link"
                />
              </div>
              <div class="obs-sources-page__design-option">
                <label class="obs-sources-page__design-label">
                  <input
                    type="radio"
                    name="statusDesign"
                    value="buttons"
                    :checked="preferences.statusDesign === 'buttons'"
                    class="obs-sources-page__design-radio"
                    @change="updatePref('statusDesign', 'buttons')"
                  >
                  <span>Buttons (colored buttons with symbols)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.status" 
                  design="buttons" 
                  label="Test Link"
                />
              </div>
              <p class="obs-sources-page__design-tip">💡 Use "Test Link" to copy the URL with a specific design without changing your saved preference</p>
            </div>
          </div>
        </div>

        <!-- OBS Setup Guide -->
        <div class="obs-sources-page__setup-guide">
          <h2 class="obs-sources-page__setup-title">📺 How to Add to OBS</h2>
          <ol class="obs-sources-page__setup-list">
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">1.</span>
              <span class="obs-sources-page__setup-text">In OBS, click the <strong>+</strong> button in the Sources panel</span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">2.</span>
              <span class="obs-sources-page__setup-text">Select <strong>"Browser"</strong> as the source type</span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">3.</span>
              <span class="obs-sources-page__setup-text">Copy one of the URLs above and paste it into the URL field</span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">4.</span>
              <span class="obs-sources-page__setup-text">Set width to <strong>1920</strong> and height to <strong>1080</strong></span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">5.</span>
              <span class="obs-sources-page__setup-text">Enable <strong>transparent background</strong> for the Browser Source (OBS 28+: checkbox in source properties), or set Custom CSS to <code class="obs-sources-page__setup-code">body { background: transparent !important; }</code></span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">6.</span>
              <span class="obs-sources-page__setup-text">Check <strong>"Shutdown source when not visible"</strong> for better performance</span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">7.</span>
              <span class="obs-sources-page__setup-text">Position and resize the overlay in your scene!</span>
            </li>
          </ol>
        </div>
      </div>
  </div>
</template>

<style scoped>
.preferences-page__hero {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.preferences-page__hero-card {
  border-radius: 1.5rem;
  padding: 1.6rem 1.75rem;
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-bg-card) 97%, rgba(255, 255, 255, 0.02)), color-mix(in srgb, var(--color-bg-card) 92%, rgba(0, 0, 0, 0.03)));
  border: 1px solid var(--color-border-secondary);
  box-shadow: var(--shadow-card);
}

.preferences-page__hero-card--primary {
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-bg-card) 96%, rgba(255, 255, 255, 0.03)), color-mix(in srgb, var(--color-bg-card) 91%, rgba(0, 0, 0, 0.03))),
    var(--theme-background-art);
}

.preferences-page__hero-card--status {
  display: grid;
  align-content: center;
  gap: 0.65rem;
}

.preferences-page__eyebrow,
.preferences-page__hero-label {
  margin: 0;
  font-size: 0.76rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-accent-primary);
}

.preferences-page__hero-value {
  color: var(--color-text-primary);
  font-size: 1.35rem;
  line-height: 1.1;
}

.preferences-page__hero-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.preferences-page__hero-pill {
  display: inline-flex;
  width: fit-content;
  align-items: center;
  padding: 0.55rem 0.9rem;
  border-radius: 9999px;
  border: 1px solid var(--color-border-secondary);
  background-color: var(--color-bg-overlay);
  color: var(--color-accent-primary);
  font-size: 0.875rem;
}

/* Section Divider Styles */
.obs-sources-page__section-divider {
  margin: 2.5rem 0 1.5rem;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-border-secondary);
}

.obs-sources-page__section-divider-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin: 0;
}

.obs-sources-page__section-divider-description {
  color: var(--color-text-secondary);
  font-size: 0.95rem;
  margin: 0;
}

/* Success/Error Messages */
.obs-sources-page__success-message {
  background: var(--status-success-bg);
  border: 1px solid var(--status-success-border);
  color: var(--status-success-text);
  padding: 0.75rem 1rem;
  border-radius: 0.85rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.obs-sources-page__error-message {
  background: var(--status-danger-bg);
  border: 1px solid var(--status-danger-border);
  color: var(--status-danger-text);
  padding: 0.75rem 1rem;
  border-radius: 0.85rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
}

/* Design Card Styles - Theme Aware */
.preferences-design-card {
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-bg-card) 97%, rgba(255, 255, 255, 0.02)), color-mix(in srgb, var(--color-bg-card) 92%, rgba(0, 0, 0, 0.03)));
  border-color: var(--color-border-secondary);
  color: var(--color-text-primary);
  box-shadow: var(--shadow-card);
}

.preferences-design-card--active {
  border-color: var(--color-accent-primary);
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--color-accent-primary) 11%, var(--color-bg-card)), color-mix(in srgb, var(--color-accent-primary) 6%, var(--color-bg-card)));
}

.preferences-design-card--inactive:hover {
  border-color: var(--color-accent-primary);
  background-color: var(--color-bg-card-hover);
  box-shadow: var(--shadow-card-hover);
}

.preferences-design-card__title {
  color: var(--color-text-primary);
}

.preferences-design-card__description {
  color: var(--color-text-secondary);
}

.preferences-design-card__meta {
  color: var(--color-text-tertiary);
}

.preferences-design-card__badge {
  font-weight: 600;
}

.preferences-design-card__badge--free {
  background-color: var(--color-btn-success-bg);
  color: var(--color-btn-success-text);
  border: 1px solid var(--color-btn-success-border);
}

.preferences-design-card__badge--premium {
  background-color: var(--color-btn-warning-bg);
  color: var(--color-btn-warning-text);
  border: 1px solid var(--color-btn-warning-border);
}

.preferences-design-card__active-indicator {
  color: var(--color-accent-primary);
}

.obs-sources-page__setup-code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.88em;
  padding: 0.15rem 0.4rem;
  border-radius: 0.25rem;
  background: rgba(0, 0, 0, 0.25);
  word-break: break-all;
}

html.theme-light .obs-sources-page__setup-code {
  background: var(--color-bg-tertiary);
}

/* Ensure icons are visible */
.preferences-design-card svg,
.obs-sources-page svg,
.obs-sources-page .iconify,
.obs-sources-page [class*="iconify"] {
  display: inline-block;
  width: 1em;
  height: 1em;
  color: inherit;
  fill: currentColor;
  vertical-align: middle;
}

/* Make icons more prominent in light theme */
html.theme-light .obs-sources-page svg,
html.theme-light .obs-sources-page .iconify,
html.theme-light .obs-sources-page [class*="iconify"] {
  opacity: 1;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

@media (max-width: 960px) {
  .preferences-page__hero {
    grid-template-columns: 1fr;
  }
}
</style>
