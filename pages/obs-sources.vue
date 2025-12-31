<script setup lang="ts">
import type { ObsPreferences } from '~/composables/useObsPreferences'
import { Icon } from '#components'

definePageMeta({
  middleware: 'auth'
})

const { user } = useAuth()
const { preferences, loading, error, fetchPreferences, updatePreferences } = useObsPreferences()
const config = useRuntimeConfig()

// Generate public OBS URLs using the user's UUID (persistent across all games)
const obsUrls = computed(() => {
  const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  const userUuid = user.value?.uuid
  
  if (!userUuid) {
    return {
      timer: '',
      rules: '',
      status: ''
    }
  }
  
  return {
    timer: `${baseUrl}/play/${userUuid}/timer`,
    rules: `${baseUrl}/play/${userUuid}/rules`,
    status: `${baseUrl}/play/${userUuid}/status`
  }
})

// Open URL in new tab
const openUrl = (url: string) => {
  window.open(url, '_blank')
}

// Load preferences and active playthrough on mount
onMounted(async () => {
  console.log('Loading OBS preferences...')
  try {
    // Fetch preferences (authenticated endpoint since we're on the settings page)
    await fetchPreferences()
    console.log('Preferences loaded:', preferences.value)
  } catch (err) {
    console.error('Failed to load data:', err)
  }
})

// Update a single preference
const updatePref = async (key: keyof ObsPreferences, value: any) => {
  if (!preferences.value) return
  
  try {
    await updatePreferences({ [key]: value })
  } catch (err) {
    console.error('Failed to update preference:', err)
  }
}

// Handle color input changes
const chromaKeyInput = ref('')
const chromaKeyValid = ref(true)

// Initialize color input when preferences load (strip # from stored value)
watch(preferences, (newPrefs) => {
  if (newPrefs?.chromaKeyColor) {
    // Remove the # for display in the input field
    chromaKeyInput.value = newPrefs.chromaKeyColor.replace('#', '')
  }
}, { immediate: true })

// Update color with validation
const updateChromaKey = async (value: string) => {
  // Remove # if present and convert to uppercase
  const cleanValue = value.replace('#', '').toUpperCase()
  chromaKeyInput.value = cleanValue
  
  // Validate: must be exactly 6 hex characters
  const isValid = /^[0-9A-F]{6}$/.test(cleanValue)
  chromaKeyValid.value = isValid
  
  // Only send update if valid (6 characters)
  if (isValid && preferences.value) {
    const fullColor = `#${cleanValue}`
    try {
      await updatePreferences({ chromaKeyColor: fullColor })
    } catch (err) {
      console.error('Failed to update chroma key color:', err)
    }
  }
}

// Computed property for the full color with # for styling
const fullChromaColor = computed(() => {
  return chromaKeyValid.value && chromaKeyInput.value ? `#${chromaKeyInput.value}` : '#333'
})
</script>

<template>
  <div class="obs-sources-page">
      <!-- Page Header -->
      <div class="obs-sources-page__header">
        <h1 class="obs-sources-page__title">OBS Browser Sources</h1>
        <p class="obs-sources-page__description">Configure overlay URLs for your streaming software</p>
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
        <h3 class="obs-sources-page__error-title">Failed to Load Preferences</h3>
        <p class="obs-sources-page__error-message">{{ error }}</p>
        <button 
          @click="fetchPreferences()" 
          class="obs-sources-page__error-button"
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
              @click="openUrl(obsUrls.timer)"
              class="obs-sources-page__preview-button"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>

          <!-- Timer Design Style -->
          <div class="obs-sources-page__section">
            <h3 class="obs-sources-page__section-title">Design Style</h3>
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
                    @change="updatePref('timerDesign', 'numbers')"
                    class="obs-sources-page__design-radio"
                  />
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
              @click="openUrl(obsUrls.rules)"
              class="obs-sources-page__preview-button"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>


          <!-- Rules Design Style -->
          <div class="obs-sources-page__section">
            <h3 class="obs-sources-page__section-title">Design Style</h3>
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
                    @change="updatePref('rulesDesign', 'list')"
                    class="obs-sources-page__design-radio"
                  />
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
            <h3 class="obs-sources-page__section-title">Timer Display on Rules Card</h3>
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
                  @change="updatePref('timerPosition', 'none')"
                  class="obs-sources-page__radio-input"
                />
                <span>No timer on rules card</span>
              </label>
              <label class="obs-sources-page__radio-label">
                <input
                  type="radio"
                  name="timerPosition"
                  value="on_card"
                  :checked="preferences.timerPosition === 'on_card'"
                  @change="updatePref('timerPosition', 'on_card')"
                  class="obs-sources-page__radio-input"
                />
                <span>Show timer on rules card</span>
              </label>
              <label class="obs-sources-page__radio-label">
                <input
                  type="radio"
                  name="timerPosition"
                  value="below_card"
                  :checked="preferences.timerPosition === 'below_card'"
                  @change="updatePref('timerPosition', 'below_card')"
                  class="obs-sources-page__radio-input"
                />
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
              @click="openUrl(obsUrls.status)"
              class="obs-sources-page__preview-button"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>

          <!-- Status Design Style -->
          <div class="obs-sources-page__section">
            <h3 class="obs-sources-page__section-title">Design Style</h3>
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
                    @change="updatePref('statusDesign', 'word')"
                    class="obs-sources-page__design-radio"
                  />
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
                    @change="updatePref('statusDesign', 'symbols')"
                    class="obs-sources-page__design-radio"
                  />
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
                    @change="updatePref('statusDesign', 'buttons')"
                    class="obs-sources-page__design-radio"
                  />
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

        <!-- Chroma Key Color -->
        <div class="obs-sources-page__chroma-section">
          <div class="obs-sources-page__chroma-header">
            <div class="obs-sources-page__chroma-icon-wrapper">
              <Icon name="heroicons:paint-brush" class="obs-sources-page__chroma-icon" />
            </div>
            <div class="flex-1">
              <h2 class="obs-sources-page__chroma-title">Chroma Key Background</h2>
            </div>
          </div>
          <p class="obs-sources-page__chroma-description">
            Set the background color for your overlays. Use OBS's "Chroma Key" filter to make this color transparent.
          </p>

          <div v-if="loading && !preferences" class="obs-sources-page__loading">
            <div>Loading preferences...</div>
          </div>

          <div v-else-if="preferences" class="space-y-4">
            <div class="obs-sources-page__chroma-field">
              <label class="obs-sources-page__chroma-label">Background Color (Hex)</label>
              <div class="obs-sources-page__chroma-input-wrapper">
                <!-- Color input -->
                <div class="obs-sources-page__chroma-input-container">
                  <span class="obs-sources-page__chroma-input-prefix">#</span>
                  <input
                    type="text"
                    :value="chromaKeyInput"
                    @input="updateChromaKey(($event.target as HTMLInputElement).value)"
                    placeholder="00FF00"
                    maxlength="6"
                    class="obs-sources-page__chroma-input"
                    :class="chromaKeyValid ? 'obs-sources-page__chroma-input--valid' : 'obs-sources-page__chroma-input--invalid'"
                  />
                </div>
                <!-- Color preview -->
                <div 
                  class="obs-sources-page__chroma-preview"
                  :class="chromaKeyValid ? 'obs-sources-page__chroma-preview--valid' : 'obs-sources-page__chroma-preview--invalid'"
                  :style="{ backgroundColor: fullChromaColor }"
                ></div>
                <!-- Info -->
                <div class="obs-sources-page__chroma-info">
                  <p class="obs-sources-page__chroma-info-text">Format: RRGGBB (6 hex characters)</p>
                  <p v-if="chromaKeyValid" class="obs-sources-page__chroma-status obs-sources-page__chroma-status--valid">✓ Valid color</p>
                  <p v-else class="obs-sources-page__chroma-status obs-sources-page__chroma-status--invalid">⚠ Enter 6 characters (0-9, A-F)</p>
                </div>
              </div>
            </div>

            <div class="obs-sources-page__chroma-instructions">
              <p class="obs-sources-page__chroma-instructions-title">💡 OBS Setup:</p>
              <ol class="obs-sources-page__chroma-instructions-list">
                <li>1. Right-click your Browser Source → Filters</li>
                <li>2. Add "Chroma Key" filter</li>
                <li>3. Select "Green" (or pick custom color matching above)</li>
                <li>4. Adjust similarity/smoothness as needed</li>
              </ol>
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
              <span class="obs-sources-page__setup-text">Check <strong>"Shutdown source when not visible"</strong> for better performance</span>
            </li>
            <li class="obs-sources-page__setup-item">
              <span class="obs-sources-page__setup-number">6.</span>
              <span class="obs-sources-page__setup-text">Position and resize the overlay in your scene!</span>
            </li>
          </ol>
        </div>
      </div>
  </div>
</template>

