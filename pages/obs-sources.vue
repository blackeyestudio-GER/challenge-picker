<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { user } = useAuth()
const { preferences, loading, fetchPreferences, updatePreferences } = useObsPreferences()
const config = useRuntimeConfig()

// Generate permanent OBS URLs
// These URLs automatically show the user's currently active playthrough
const obsUrls = computed(() => {
  const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return {
    timer: `${baseUrl}/play/me/timer`,
    rules: `${baseUrl}/play/me/rules`,
    status: `${baseUrl}/play/me/status`
  }
})

// Copy URL to clipboard
const copyUrl = async (url: string, label: string) => {
  try {
    await navigator.clipboard.writeText(url)
    alert(`${label} URL copied to clipboard!`)
  } catch (err) {
    console.error('Failed to copy:', err)
    alert('Failed to copy URL')
  }
}

// Open URL in new tab
const openUrl = (url: string) => {
  window.open(url, '_blank')
}

// Load preferences on mount
onMounted(async () => {
  await fetchPreferences()
})

// Update a single preference
const updatePref = async (key: keyof typeof preferences.value, value: any) => {
  if (!preferences.value) return
  
  try {
    await updatePreferences({ [key]: value })
  } catch (err) {
    console.error('Failed to update preference:', err)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-900 via-purple-800 to-pink-800 py-8 px-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <NuxtLink to="/dashboard" class="text-white/80 hover:text-white mb-4 inline-flex items-center gap-2">
          ← Back to Dashboard
        </NuxtLink>
        <h1 class="text-4xl font-bold text-white mb-2">OBS Browser Sources</h1>
        <p class="text-white/80">Configure overlay URLs for your streaming software</p>
      </div>

      <!-- Important Info Box -->
      <div class="bg-blue-500/20 border-2 border-blue-400/50 rounded-lg p-6 mb-8">
        <div class="flex items-start gap-3">
          <div class="text-3xl">ℹ️</div>
          <div>
            <h3 class="text-xl font-bold text-white mb-2">How It Works</h3>
            <p class="text-white/90 mb-2">
              The URLs below are <strong>permanent</strong> and will automatically show your <strong>currently active game session</strong>.
            </p>
            <p class="text-white/80 mb-3">
              You can add these URLs to OBS once and leave them there. They will automatically update when you start a new game session!
            </p>
            <div class="bg-white/10 rounded-lg p-4 mt-3">
              <p class="text-white/90 font-semibold mb-2">✨ Smart Design System:</p>
              <ul class="text-white/80 text-sm space-y-1 ml-4">
                <li>• Your design preferences below are automatically applied to all overlays</li>
                <li>• No need to update OBS when you change designs - they sync instantly!</li>
                <li>• Advanced: Add <code class="text-pink-300">?design=word</code> to URLs to override for specific scenes</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div v-if="loading && !preferences" class="text-center py-12">
        <div class="text-white text-xl">Loading preferences...</div>
      </div>

      <div v-else-if="preferences" class="space-y-6">
        <!-- Timer Overlay -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 border-2 border-white/20">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 class="text-2xl font-bold text-white mb-2">⏱️ Timer Overlay</h2>
              <p class="text-white/70">Displays the session timer in large, readable format</p>
            </div>
          </div>

          <!-- URL Display -->
          <div class="bg-black/30 rounded-lg p-4 mb-4 font-mono text-sm text-white break-all">
            {{ obsUrls.timer }}
          </div>

          <div class="flex gap-3 mb-6">
            <button
              @click="copyUrl(obsUrls.timer, 'Timer')"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
            >
              📋 Copy URL
            </button>
            <button
              @click="openUrl(obsUrls.timer)"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
            >
              👁️ Preview
            </button>
          </div>

          <!-- Timer Visibility Settings -->
          <div class="border-t border-white/20 pt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Visibility Settings</h3>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showTimerInSetup"
                  @change="updatePref('showTimerInSetup', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Setup</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showTimerInActive"
                  @change="updatePref('showTimerInActive', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Active</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showTimerInPaused"
                  @change="updatePref('showTimerInPaused', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Paused</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showTimerInCompleted"
                  @change="updatePref('showTimerInCompleted', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Completed</span>
              </label>
            </div>
          </div>

          <!-- Timer Design Style -->
          <div class="border-t border-white/20 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Design Style</h3>
            <div class="space-y-2">
              <label class="flex items-center gap-3 text-white cursor-pointer">
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
              <p class="text-white/50 text-sm ml-8">More styles coming soon...</p>
            </div>
          </div>
        </div>

        <!-- Rules Overlay -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 border-2 border-white/20">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 class="text-2xl font-bold text-white mb-2">📜 Rules Overlay</h2>
              <p class="text-white/70">Shows active rules and game statistics</p>
            </div>
          </div>

          <div class="bg-black/30 rounded-lg p-4 mb-4 font-mono text-sm text-white break-all">
            {{ obsUrls.rules }}
          </div>

          <div class="flex gap-3 mb-6">
            <button
              @click="copyUrl(obsUrls.rules, 'Rules')"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
            >
              📋 Copy URL
            </button>
            <button
              @click="openUrl(obsUrls.rules)"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
            >
              👁️ Preview
            </button>
          </div>

          <div class="border-t border-white/20 pt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Visibility Settings</h3>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showRulesInSetup"
                  @change="updatePref('showRulesInSetup', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Setup</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showRulesInActive"
                  @change="updatePref('showRulesInActive', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Active</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showRulesInPaused"
                  @change="updatePref('showRulesInPaused', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Paused</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showRulesInCompleted"
                  @change="updatePref('showRulesInCompleted', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Completed</span>
              </label>
            </div>
          </div>

          <!-- Rules Design Style -->
          <div class="border-t border-white/20 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Design Style</h3>
            <div class="space-y-2">
              <label class="flex items-center gap-3 text-white cursor-pointer">
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
              <p class="text-white/50 text-sm ml-8">More layouts coming soon...</p>
            </div>
          </div>

          <!-- Timer Position on Rules Card -->
          <div class="border-t border-white/20 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Timer Display on Rules Card</h3>
            <div class="space-y-2">
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
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 border-2 border-white/20">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 class="text-2xl font-bold text-white mb-2">🎮 Status Overlay</h2>
              <p class="text-white/70">Large status badge (LIVE, PAUSED, etc.)</p>
            </div>
          </div>

          <div class="bg-black/30 rounded-lg p-4 mb-4 font-mono text-sm text-white break-all">
            {{ obsUrls.status }}
          </div>

          <div class="flex gap-3 mb-6">
            <button
              @click="copyUrl(obsUrls.status, 'Status')"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
            >
              📋 Copy URL
            </button>
            <button
              @click="openUrl(obsUrls.status)"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
            >
              👁️ Preview
            </button>
          </div>

          <div class="border-t border-white/20 pt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Visibility Settings</h3>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showStatusInSetup"
                  @change="updatePref('showStatusInSetup', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Setup</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showStatusInActive"
                  @change="updatePref('showStatusInActive', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Active</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showStatusInPaused"
                  @change="updatePref('showStatusInPaused', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Paused</span>
              </label>
              <label class="flex items-center gap-3 text-white cursor-pointer">
                <input
                  type="checkbox"
                  :checked="preferences.showStatusInCompleted"
                  @change="updatePref('showStatusInCompleted', ($event.target as HTMLInputElement).checked)"
                  class="w-5 h-5 rounded"
                />
                <span>Show in Completed</span>
              </label>
            </div>
          </div>

          <!-- Status Design Style -->
          <div class="border-t border-white/20 pt-4 mt-4">
            <h3 class="text-lg font-semibold text-white mb-3">Design Style</h3>
            <div class="space-y-2">
              <label class="flex items-center gap-3 text-white cursor-pointer">
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
              <label class="flex items-center gap-3 text-white cursor-pointer">
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
              <label class="flex items-center gap-3 text-white cursor-pointer">
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
            </div>
          </div>
        </div>

        <!-- OBS Setup Guide -->
        <div class="bg-gradient-to-r from-green-500/20 to-blue-500/20 border-2 border-green-400/50 rounded-xl p-6">
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

