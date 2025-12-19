<script setup lang="ts">
import type { ObsPreferences } from '~/composables/useObsPreferences'

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
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 py-8 px-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <NuxtLink to="/dashboard" class="text-gray-400 hover:text-white mb-4 inline-flex items-center gap-2">
          ← Back to Dashboard
        </NuxtLink>
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">OBS Browser Sources</h1>
        <p class="text-gray-300">Configure overlay URLs for your streaming software</p>
      </div>

      <!-- Important Info Box -->
      <div class="bg-cyan/10 border-2 border-cyan/30 rounded-lg p-6 mb-8">
        <div class="flex items-start gap-3">
          <div class="text-3xl">ℹ️</div>
          <div>
            <h3 class="text-xl font-bold text-white mb-2">How It Works</h3>
            <p class="text-white/90 mb-2">
              You get <strong>permanent URLs</strong> that are <strong>unique to your account</strong>.
            </p>
            <p class="text-white/80">
              Set them up in OBS once, and they'll automatically display whichever game you're currently playing! No authentication needed, so viewers can access them too.
            </p>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error && !preferences" class="bg-red-500/20 border border-red-500/50 rounded-lg p-6 mb-6">
        <h3 class="text-red-300 font-bold mb-2">Failed to Load Preferences</h3>
        <p class="text-red-200">{{ error }}</p>
        <button 
          @click="fetchPreferences()" 
          class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
        >
          Retry
        </button>
      </div>

      <!-- Always show the URLs and settings -->
      <div class="space-y-6">
        <!-- Timer Overlay -->
        <div class="bg-gray-800/80 backdrop-blur-md rounded-xl p-6 border-2 border-gray-700">
          <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 bg-cyan/20 rounded-lg p-3 mt-1">
              <Icon name="heroicons:clock" class="w-8 h-8 text-cyan" />
            </div>
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-white mb-2">Timer Overlay</h2>
              <p class="text-gray-300 mb-1">Displays the session elapsed time since start</p>
              <p class="text-sm text-gray-400">Shows: HH:MM:SS or MM:SS format</p>
            </div>
          </div>

          <!-- URL Actions -->
          <div class="flex gap-3 mb-6">
            <CopyButton :url="obsUrls.timer" label="Copy URL" />
            <button
              @click="openUrl(obsUrls.timer)"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>

          <!-- Timer Design Style -->
          <div class="border-t border-gray-700 pt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Design Style</h3>
            <div v-if="loading && !preferences" class="text-center py-4">
              <div class="text-gray-400">Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-3">
              <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                <label class="flex items-center gap-3 text-white cursor-pointer flex-1">
                  <input
                    type="radio"
                    name="timerDesign"
                    value="numbers"
                    :checked="preferences.timerDesign === 'numbers'"
                    @change="updatePref('timerDesign', 'numbers')"
                    class="w-5 h-5"
                  />
                  <span>Numbers (HH:MM:SS or MM:SS)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.timer" 
                  design="numbers" 
                  label="Test Link"
                  color="cyan"
                />
              </div>
              <p class="text-white/50 text-sm ml-8">More styles coming soon...</p>
              <p class="text-white/40 text-xs mt-2 italic">💡 Use "Test Link" to copy the URL with a specific design without changing your saved preference</p>
            </div>
          </div>
        </div>

        <!-- Rules Overlay -->
        <div class="bg-gray-800/80 backdrop-blur-md rounded-xl p-6 border-2 border-gray-700">
          <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 bg-magenta/20 rounded-lg p-3 mt-1">
              <Icon name="heroicons:list-bullet" class="w-8 h-8 text-magenta" />
            </div>
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-white mb-2">Rules Overlay</h2>
              <p class="text-gray-300 mb-1">Shows currently active rules during gameplay</p>
              <p class="text-sm text-gray-400">Shows: List of rules with countdown timers</p>
            </div>
          </div>

          <!-- URL Actions -->
          <div class="flex gap-3 mb-6">
            <CopyButton :url="obsUrls.rules" label="Copy URL" />
            <button
              @click="openUrl(obsUrls.rules)"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>


          <!-- Rules Design Style -->
          <div class="border-t border-gray-700 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Design Style</h3>
            <div v-if="loading && !preferences" class="text-center py-4">
              <div class="text-gray-400">Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-3">
              <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                <label class="flex items-center gap-3 text-white cursor-pointer flex-1">
                  <input
                    type="radio"
                    name="rulesDesign"
                    value="list"
                    :checked="preferences.rulesDesign === 'list'"
                    @change="updatePref('rulesDesign', 'list')"
                    class="w-5 h-5"
                  />
                  <span>List (full-screen text list with timer on right)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.rules" 
                  design="list" 
                  label="Test Link"
                  color="magenta"
                />
              </div>
              <p class="text-white/50 text-sm ml-8">More layouts coming soon...</p>
              <p class="text-white/40 text-xs mt-2 italic">💡 Use "Test Link" to copy the URL with a specific design without changing your saved preference</p>
            </div>
          </div>

          <!-- Timer Position on Rules Card -->
          <div class="border-t border-gray-700 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Timer Display on Rules Card</h3>
            <div v-if="loading && !preferences" class="text-center py-4">
              <div class="text-gray-400">Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-2">
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="radio"
                  name="timerPosition"
                  value="none"
                  :checked="preferences.timerPosition === 'none'"
                  @change="updatePref('timerPosition', 'none')"
                  class="w-5 h-5"
                />
                <span>No timer on rules card</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="radio"
                  name="timerPosition"
                  value="on_card"
                  :checked="preferences.timerPosition === 'on_card'"
                  @change="updatePref('timerPosition', 'on_card')"
                  class="w-5 h-5"
                />
                <span>Show timer on rules card</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="radio"
                  name="timerPosition"
                  value="below_card"
                  :checked="preferences.timerPosition === 'below_card'"
                  @change="updatePref('timerPosition', 'below_card')"
                  class="w-5 h-5"
                />
                <span>Show timer below rules card</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Status Overlay -->
        <div class="bg-gray-800/80 backdrop-blur-md rounded-xl p-6 border-2 border-gray-700">
          <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 bg-cyan/20 rounded-lg p-3 mt-1">
              <Icon name="heroicons:signal" class="w-8 h-8 text-cyan" />
            </div>
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-white mb-2">Status Overlay</h2>
              <p class="text-gray-300 mb-1">Shows current game session status</p>
              <p class="text-sm text-gray-400">Shows: SETUP, LIVE, PAUSED, or ENDED (as word/symbol/button)</p>
            </div>
          </div>

          <!-- URL Actions -->
          <div class="flex gap-3 mb-6">
            <CopyButton :url="obsUrls.status" label="Copy URL" />
            <button
              @click="openUrl(obsUrls.status)"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2"
            >
              <Icon name="heroicons:eye" class="w-5 h-5" />
              Preview
            </button>
          </div>

          <!-- Status Design Style -->
          <div class="border-t border-gray-700 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Design Style</h3>
            <div v-if="loading && !preferences" class="text-center py-4">
              <div class="text-gray-400">Loading preferences...</div>
            </div>
            <div v-else-if="preferences" class="space-y-3">
              <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                <label class="flex items-center gap-3 text-white cursor-pointer flex-1">
                  <input
                    type="radio"
                    name="statusDesign"
                    value="word"
                    :checked="preferences.statusDesign === 'word'"
                    @change="updatePref('statusDesign', 'word')"
                    class="w-5 h-5"
                  />
                  <span>Word (LIVE, PAUSED, SETUP, ENDED)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.status" 
                  design="word" 
                  label="Test Link"
                  color="cyan"
                />
              </div>
              <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                <label class="flex items-center gap-3 text-white cursor-pointer flex-1">
                  <input
                    type="radio"
                    name="statusDesign"
                    value="symbols"
                    :checked="preferences.statusDesign === 'symbols'"
                    @change="updatePref('statusDesign', 'symbols')"
                    class="w-5 h-5"
                  />
                  <span>Symbols (▶️ ⏸️ ⏹️ icons)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.status" 
                  design="symbols" 
                  label="Test Link"
                  color="cyan"
                />
              </div>
              <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                <label class="flex items-center gap-3 text-white cursor-pointer flex-1">
                  <input
                    type="radio"
                    name="statusDesign"
                    value="buttons"
                    :checked="preferences.statusDesign === 'buttons'"
                    @change="updatePref('statusDesign', 'buttons')"
                    class="w-5 h-5"
                  />
                  <span>Buttons (colored buttons with symbols)</span>
                </label>
                <TestLinkButton 
                  :base-url="obsUrls.status" 
                  design="buttons" 
                  label="Test Link"
                  color="cyan"
                />
              </div>
              <p class="text-white/40 text-xs mt-2 italic">💡 Use "Test Link" to copy the URL with a specific design without changing your saved preference</p>
            </div>
          </div>
        </div>

        <!-- Chroma Key Color -->
        <div class="bg-gray-800/80 backdrop-blur-md rounded-xl p-6 border-2 border-gray-700">
          <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 bg-magenta/20 rounded-lg p-3">
              <Icon name="heroicons:paint-brush" class="w-8 h-8 text-magenta" />
            </div>
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-white mb-0">Chroma Key Background</h2>
            </div>
          </div>
          <p class="text-gray-300 mb-4">
            Set the background color for your overlays. Use OBS's "Chroma Key" filter to make this color transparent.
          </p>

          <div v-if="loading && !preferences" class="text-center py-4">
            <div class="text-gray-400">Loading preferences...</div>
          </div>

          <div v-else-if="preferences" class="space-y-4">
            <div>
              <label class="block text-white font-semibold mb-2">Background Color (Hex)</label>
              <div class="flex items-center gap-4">
                <!-- Color input -->
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/50 text-lg font-mono pointer-events-none">#</span>
                  <input
                    type="text"
                    :value="chromaKeyInput"
                    @input="updateChromaKey(($event.target as HTMLInputElement).value)"
                    placeholder="00FF00"
                    maxlength="6"
                    class="pl-7 pr-4 py-3 bg-black/30 border-2 rounded-lg text-white font-mono text-lg focus:outline-none transition w-44"
                    :class="chromaKeyValid ? 'border-green-500 focus:border-green-400' : 'border-red-500 focus:border-red-400'"
                  />
                </div>
                <!-- Color preview -->
                <div 
                  class="w-16 h-16 rounded-lg border-4 shadow-lg transition-all"
                  :class="chromaKeyValid ? 'border-green-500' : 'border-red-500/50'"
                  :style="{ backgroundColor: fullChromaColor }"
                ></div>
                <!-- Info -->
                <div class="flex-1">
                  <p class="text-white/80 text-sm">Format: RRGGBB (6 hex characters)</p>
                  <p v-if="chromaKeyValid" class="text-green-400 text-xs mt-1">✓ Valid color</p>
                  <p v-else class="text-red-400 text-xs mt-1">⚠ Enter 6 characters (0-9, A-F)</p>
                </div>
              </div>
            </div>

            <div class="bg-cyan/10 border border-cyan/30 rounded-lg p-4">
              <p class="text-white/90 text-sm mb-2"><strong>💡 OBS Setup:</strong></p>
              <ol class="text-white/80 text-sm space-y-1 ml-4">
                <li>1. Right-click your Browser Source → Filters</li>
                <li>2. Add "Chroma Key" filter</li>
                <li>3. Select "Green" (or pick custom color matching above)</li>
                <li>4. Adjust similarity/smoothness as needed</li>
              </ol>
            </div>
          </div>
        </div>

        <!-- OBS Setup Guide -->
        <div class="bg-gradient-to-r from-cyan/10 to-magenta/10 border-2 border-cyan/30 rounded-xl p-6">
          <h2 class="text-2xl font-bold text-white mb-4">📺 How to Add to OBS</h2>
          <ol class="space-y-3 text-white/90">
            <li class="flex gap-3">
              <span class="font-bold">1.</span>
              <span>In OBS, click the <strong>+</strong> button in the Sources panel</span>
            </li>
            <li class="flex gap-3">
              <span class="font-bold">2.</span>
              <span>Select <strong>"Browser"</strong> as the source type</span>
            </li>
            <li class="flex gap-3">
              <span class="font-bold">3.</span>
              <span>Copy one of the URLs above and paste it into the URL field</span>
            </li>
            <li class="flex gap-3">
              <span class="font-bold">4.</span>
              <span>Set width to <strong>1920</strong> and height to <strong>1080</strong></span>
            </li>
            <li class="flex gap-3">
              <span class="font-bold">5.</span>
              <span>Check <strong>"Shutdown source when not visible"</strong> for better performance</span>
            </li>
            <li class="flex gap-3">
              <span class="font-bold">6.</span>
              <span>Position and resize the overlay in your scene!</span>
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>

