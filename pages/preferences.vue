<script setup lang="ts">
import type { ObsPreferences } from '~/composables/useObsPreferences'
import { Icon } from '#components'
import { useDesigns, type DesignSet } from '~/composables/useDesigns'

definePageMeta({
  middleware: 'auth'
})

const { user } = useAuth()
const { preferences, loading, error, fetchPreferences, updatePreferences } = useObsPreferences()
const { fetchAvailableDesignSets, setActiveDesignSet, loading: designsLoading } = useDesigns()
const { getAuthHeader } = useAuth()
const config = useRuntimeConfig()

// Card Design state
const availableDesigns = ref<DesignSet[]>([])
const activeDesignId = ref<number | null>(null)
const designSuccess = ref('')
const designError = ref('')

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
  console.log('Loading preferences...')
  try {
    // Fetch OBS preferences
    await fetchPreferences()
    console.log('OBS Preferences loaded:', preferences.value)
    
    // Load available card designs
    await loadAvailableDesigns()
  } catch (err) {
    console.error('Failed to load data:', err)
  }
})

// Load available card designs
const loadAvailableDesigns = async () => {
  try {
    availableDesigns.value = await fetchAvailableDesignSets()
    // Get user's active design
    const response = await $fetch<{ success: boolean; data: { id: number } }>('/api/users/me/active-design-set', {
      headers: getAuthHeader()
    })
    if (response.success) {
      activeDesignId.value = response.data.id
    }
  } catch (error) {
    console.error('Failed to load designs:', error)
  }
}

// Handle design selection change
const handleDesignChange = async (designSetId: number) => {
  designError.value = ''
  designSuccess.value = ''
  
  try {
    await setActiveDesignSet(designSetId)
    activeDesignId.value = designSetId
    designSuccess.value = 'Card design updated successfully!'
    setTimeout(() => designSuccess.value = '', 3000)
  } catch (error: any) {
    designError.value = error.data?.error?.message || 'Failed to update card design'
  }
}

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
        <h1 class="obs-sources-page__title">Preferences</h1>
        <p class="obs-sources-page__description">Configure your general settings and streaming overlays</p>
      </div>

      <!-- ========== GENERAL SETTINGS SECTION ========== -->
      <div class="obs-sources-page__section-divider">
        <h2 class="obs-sources-page__section-divider-title">General Settings</h2>
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

        <div v-if="designsLoading" class="text-center py-8 text-white/40">
          Loading designs...
        </div>

        <div v-else-if="availableDesigns.length === 0" class="text-center py-8 text-white/40">
          No card designs available yet
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
          <div
            v-for="design in availableDesigns"
            :key="design.id"
            @click="handleDesignChange(design.id)"
            :class="[
              'cursor-pointer border-2 rounded-lg p-4 transition-all hover:shadow-lg',
              activeDesignId === design.id
                ? 'border-cyan-500 bg-cyan-500/10'
                : 'border-white/10 bg-white/5 hover:border-white/20'
            ]"
          >
            <div class="flex items-start justify-between mb-2">
              <h3 class="font-semibold text-white">{{ design.name }}</h3>
              <span v-if="design.isFree" class="text-xs px-2 py-1 rounded bg-green-500/20 text-green-400">FREE</span>
              <span v-else-if="design.isPremium" class="text-xs px-2 py-1 rounded bg-amber-500/20 text-amber-400">PREMIUM</span>
            </div>
            
            <p v-if="design.description" class="text-sm text-white/60 mb-3">{{ design.description }}</p>
            
            <div class="flex items-center justify-between text-xs text-white/40">
              <span>{{ design.type === 'template' ? 'Template' : 'Full Set' }}</span>
              <span v-if="design.theme" class="capitalize">{{ design.theme }}</span>
            </div>
            
            <div v-if="activeDesignId === design.id" class="mt-3 flex items-center gap-2 text-cyan-400 text-sm">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="font-medium">Active</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ========== OBS BROWSER SOURCES SECTION ========== -->
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

<style scoped>
/* Section Divider Styles */
.obs-sources-page__section-divider {
  margin: 3rem 0 2rem;
  border-top: 2px solid rgba(255, 255, 255, 0.1);
  padding-top: 2rem;
}

.obs-sources-page__section-divider-title {
  font-size: 1.75rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.obs-sources-page__section-divider-description {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.95rem;
}

/* Success/Error Messages */
.obs-sources-page__success-message {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: rgb(134, 239, 172);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.obs-sources-page__error-message {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: rgb(252, 165, 165);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
}
</style>

