<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-900 via-gray-900 to-indigo-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">
          🎮 Creator Dashboard
        </h1>
        <p class="text-xl text-gray-300">
          Professional tools for streamers and content creators
        </p>
        <p class="text-sm text-gray-400 mt-2">
          All URLs use your auth token - works across all playthroughs!
        </p>
      </div>

      <!-- Tabs Navigation -->
      <div class="flex justify-center mb-8">
        <div class="bg-gray-800/50 backdrop-blur rounded-lg p-1 inline-flex">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-6 py-3 rounded-md font-semibold transition-all',
              activeTab === tab.id
                ? 'bg-purple-600 text-white shadow-lg'
                : 'text-gray-400 hover:text-white hover:bg-gray-700/50'
            ]"
          >
            {{ tab.icon }} {{ tab.name }}
          </button>
        </div>
      </div>

      <!-- Authentication Warning -->
      <div v-if="!authToken" class="bg-red-900/30 border border-red-500 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-red-300 mb-2">⚠️ Not Logged In</h3>
        <p class="text-red-200">
          You must be logged in to use these URLs. Please log in with Discord first.
        </p>
      </div>

      <!-- Quick Copy Section -->
      <div v-if="authToken" class="bg-gray-800/50 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
        <h2 class="text-2xl font-bold text-white mb-4">🔑 Your Auth Token</h2>
        <p class="text-gray-300 mb-4">
          This token is used in all URLs below. Keep it private!
        </p>
        <div class="flex gap-2">
          <input
            type="password"
            :value="authToken"
            readonly
            class="flex-1 bg-gray-900 border border-gray-600 rounded px-4 py-2 text-white font-mono text-sm"
          />
          <button
            @click="copyToken"
            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold transition"
          >
            {{ tokenCopied ? '✓ Copied!' : 'Copy Token' }}
          </button>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 1: STREAM DECK -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'stream-deck'">
        <!-- How Stream Deck Polling Works -->
        <div class="bg-blue-900/30 border border-blue-500 rounded-lg p-6 mb-8">
          <h2 class="text-2xl font-bold text-blue-300 mb-4">⚡ How Stream Deck Auto-Updates Work</h2>
          <div class="space-y-4 text-blue-200">
            <p class="text-lg">
              Stream Deck has built-in support for <strong>"Title from API"</strong> - it automatically polls URLs and updates button text!
            </p>
            
            <div class="bg-blue-950/50 rounded-lg p-4">
              <h4 class="font-semibold mb-2">🔄 Automatic Polling Process:</h4>
              <ol class="list-decimal list-inside space-y-2 ml-4">
                <li>You configure a URL once (e.g., timer endpoint)</li>
                <li>Stream Deck sends GET request every X seconds (you choose: 1-10s)</li>
                <li>Our API returns plain text (e.g., "⏱️ No Healing\n15:42")</li>
                <li>Stream Deck updates the button display automatically</li>
                <li>Repeat forever - no manual refresh needed!</li>
              </ol>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-4">
              <div class="bg-blue-950/50 rounded-lg p-4">
                <h4 class="font-semibold mb-2">✅ Benefits:</h4>
                <ul class="list-disc list-inside space-y-1 ml-2 text-sm">
                  <li>Live countdown timers on physical buttons</li>
                  <li>Real-time counter updates</li>
                  <li>No alt-tabbing during gameplay</li>
                  <li>Works with all Stream Deck models</li>
                  <li>Minimal network usage (~1-2 MB per 4-hour stream)</li>
                </ul>
              </div>
              <div class="bg-blue-950/50 rounded-lg p-4">
                <h4 class="font-semibold mb-2">⏱️ Recommended Intervals:</h4>
                <ul class="list-disc list-inside space-y-1 ml-2 text-sm">
                  <li><strong>Timers:</strong> 1-2 seconds (precise countdown)</li>
                  <li><strong>Counters:</strong> 3-5 seconds (less frequent changes)</li>
                  <li><strong>Status:</strong> 5-10 seconds (rarely changes)</li>
                  <li><strong>Summary:</strong> 5-10 seconds (mostly static)</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

      <!-- Stream Deck Text Widgets -->
      <div class="bg-gray-800/50 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
        <h2 class="text-2xl font-bold text-white mb-2">
          📊 Stream Deck Text Widgets
        </h2>
        <p class="text-gray-300 mb-6">
          Use these URLs in Stream Deck's "Text" action to display live data.
          Set update interval to 2-5 seconds.
        </p>

        <div class="space-y-4">
          <!-- Playthrough Status -->
          <URLCard
            title="Playthrough Status"
            description="Shows current game and ruleset"
            :url="getUrl('/api/playthrough/stream-deck/status')"
            example-output="🎮 Resident Evil 2\n📋 Knife Only\n✅ Active"
          />

          <!-- Rules Summary -->
          <URLCard
            title="Active Rules Summary"
            description="Shows count of active rules by type"
            :url="getUrl('/api/playthrough/stream-deck/rules-summary')"
            example-output="📋 Active Rules: 5\n🔮 Permanent: 2\n⏱️ Timers: 2\n🔢 Counters: 1"
          />

          <!-- Timer Display -->
          <URLCard
            title="Timer Display (1st)"
            description="Shows time remaining for first timer rule"
            :url="getUrl('/api/playthrough/stream-deck/timer?index=1')"
            example-output="⏱️ No Healing\n15:42"
          />

          <URLCard
            title="Timer Display (2nd)"
            description="Shows time remaining for second timer rule"
            :url="getUrl('/api/playthrough/stream-deck/timer?index=2')"
            example-output="⏱️ Speed Run\n45:12"
          />

          <!-- Counter Display -->
          <URLCard
            title="Counter Display (1st)"
            description="Shows count for first counter rule"
            :url="getUrl('/api/playthrough/stream-deck/counter?index=1')"
            example-output="🔢 Take Damage 5 Times\n3 remaining"
          />

          <URLCard
            title="Counter Display (2nd)"
            description="Shows count for second counter rule"
            :url="getUrl('/api/playthrough/stream-deck/counter?index=2')"
            example-output="🔢 Defeat 3 Bosses\n1 remaining"
          />
        </div>
      </div>

      <!-- Stream Deck Action Buttons -->
      <div class="bg-gray-800/50 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
        <h2 class="text-2xl font-bold text-white mb-2">
          🎯 Stream Deck Action Buttons
        </h2>
        <p class="text-gray-300 mb-6">
          Use these URLs in Stream Deck's "Website" action to perform actions.
          Use POST method with your auth token in headers.
        </p>

        <div class="space-y-4">
          <!-- Decrement Counter -->
          <URLCard
            title="Decrement Counter (1st)"
            description="Decrements first counter rule by 1"
            :url="getUrl('/api/playthrough/counters/decrement?index=1')"
            method="POST"
            requires-auth
          />

          <URLCard
            title="Decrement Counter (2nd)"
            description="Decrements second counter rule by 1"
            :url="getUrl('/api/playthrough/counters/decrement?index=2')"
            method="POST"
            requires-auth
          />

          <URLCard
            title="Increment Counter (1st)"
            description="Increments first counter rule by 1 (undo)"
            :url="getUrl('/api/playthrough/counters/increment?index=1')"
            method="POST"
            requires-auth
          />
        </div>
      </div>

      </div>
      <!-- END STREAM DECK TAB -->

      <!-- ========================================== -->
      <!-- TAB 2: OBS SOURCES -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'obs'">
        <div class="bg-gray-800/50 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
          <h2 class="text-2xl font-bold text-white mb-2">
            🎥 OBS Browser Sources
          </h2>
          <p class="text-gray-300 mb-6">
            Add these URLs as Browser Sources in OBS to display overlays on your stream.
          </p>

          <div class="space-y-4">
            <URLCard
              title="Active Rules Overlay"
              description="Full overlay showing all active rules with timers and counters. Updates automatically every 2 seconds."
              :url="`${baseUrl}/playthrough/overlay`"
              example-output="Transparent overlay with all active rules, timers, and counters"
            />

            <URLCard
              title="Timer Only Overlay"
              description="Minimalist overlay showing only active timers (coming soon)"
              :url="`${baseUrl}/playthrough/overlay/timers`"
              example-output="Compact timer display"
            />

            <URLCard
              title="Counter Only Overlay"
              description="Minimalist overlay showing only active counters (coming soon)"
              :url="`${baseUrl}/playthrough/overlay/counters`"
              example-output="Compact counter display"
            />
          </div>
        </div>

        <!-- OBS Configuration Guide -->
        <div class="bg-green-900/30 border border-green-500 rounded-lg p-6">
          <h3 class="text-xl font-bold text-green-300 mb-4">🎬 OBS Setup Guide</h3>
          
          <div class="space-y-4 text-green-200">
            <div>
              <h4 class="font-semibold mb-2">Adding Browser Source:</h4>
              <ol class="list-decimal list-inside space-y-2 ml-4">
                <li>In OBS, click <strong>+</strong> under Sources</li>
                <li>Select <strong>Browser</strong></li>
                <li>Name it "Challenge Rules Overlay"</li>
                <li>Paste the overlay URL (from above)</li>
                <li>Set Width: <code class="bg-green-950 px-2 py-1 rounded">1920</code> Height: <code class="bg-green-950 px-2 py-1 rounded">1080</code></li>
                <li>Check <strong>"Refresh browser when scene becomes active"</strong></li>
                <li>Click OK</li>
              </ol>
            </div>

            <div>
              <h4 class="font-semibold mb-2">Positioning & Styling:</h4>
              <ul class="list-disc list-inside space-y-1 ml-4 text-sm">
                <li>Drag the overlay to your preferred position (usually top-right or bottom-right)</li>
                <li>The overlay has a transparent background - perfect for streaming</li>
                <li>Resize the source if needed (maintain aspect ratio)</li>
                <li>You can add CSS filters in OBS for custom styling</li>
              </ul>
            </div>

            <div>
              <h4 class="font-semibold mb-2">⚡ Auto-Refresh:</h4>
              <p class="ml-4">
                The overlay automatically polls the API every 2 seconds. No need to refresh manually!
                Timers count down in real-time, counters update instantly when you use Stream Deck buttons.
              </p>
            </div>
          </div>
        </div>
      </div>
      <!-- END OBS TAB -->

      <!-- ========================================== -->
      <!-- TAB 3: SETUP GUIDE -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'setup'">
      <!-- Stream Deck Setup Guide -->
        <div class="bg-gray-800/50 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
          <h2 class="text-2xl font-bold text-white mb-4">🎛️ Stream Deck Complete Setup</h2>
          
          <div class="space-y-6">
            <!-- Text Widgets Setup -->
            <div class="bg-gray-900/50 rounded-lg p-6 border border-gray-700">
              <h3 class="text-xl font-semibold text-purple-300 mb-4">📊 Text Widgets (Timers/Counters/Status)</h3>
              <div class="space-y-4 text-gray-300">
                <p class="text-sm text-gray-400 mb-3">
                  These display live data that auto-updates via polling.
                </p>
                <ol class="list-decimal list-inside space-y-3 ml-4">
                  <li>
                    <strong>Add "Text" action</strong> to your Stream Deck button
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Drag "Text" from action list to a button</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Enable "Title from API"</strong>
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Look for API or "Title from API" tab in settings</li>
                      <li>Toggle it ON</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Paste the URL</strong> from the Stream Deck tab above
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Example: <code class="bg-gray-950 px-1 rounded">http://localhost:3000/api/playthrough/stream-deck/timer?index=1</code></li>
                    </ul>
                  </li>
                  <li>
                    <strong>Add Authorization header</strong>
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Header Name: <code class="bg-gray-950 px-2 py-1 rounded">Authorization</code></li>
                      <li>Header Value: <code class="bg-gray-950 px-2 py-1 rounded">Bearer YOUR_TOKEN</code></li>
                      <li>(Copy token from section above)</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Set update interval</strong>
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Timers: 1-2 seconds</li>
                      <li>Counters: 3-5 seconds</li>
                      <li>Status/Summary: 5-10 seconds</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Customize appearance</strong> (optional)
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Font size, color, alignment</li>
                      <li>Background color</li>
                      <li>Add icon overlay</li>
                    </ul>
                  </li>
                </ol>
              </div>
            </div>

            <!-- Action Buttons Setup -->
            <div class="bg-gray-900/50 rounded-lg p-6 border border-gray-700">
              <h3 class="text-xl font-semibold text-green-300 mb-4">⚡ Action Buttons (Decrement/Increment)</h3>
              <div class="space-y-4 text-gray-300">
                <p class="text-sm text-gray-400 mb-3">
                  These trigger actions when pressed (update counters).
                </p>
                <ol class="list-decimal list-inside space-y-3 ml-4">
                  <li>
                    <strong>Add "Website" action</strong> to your Stream Deck button
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Drag "System" → "Website" to a button</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Paste the URL</strong> from the Stream Deck tab above
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Example: <code class="bg-gray-950 px-1 rounded">http://localhost:3000/api/playthrough/counters/decrement?index=1</code></li>
                    </ul>
                  </li>
                  <li>
                    <strong>Set Method to POST</strong>
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Change from GET to POST in dropdown</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Add TWO headers</strong>
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Header 1: <code class="bg-gray-950 px-2 py-1 rounded">Authorization: Bearer YOUR_TOKEN</code></li>
                      <li>Header 2: <code class="bg-gray-950 px-2 py-1 rounded">Content-Type: application/json</code></li>
                    </ul>
                  </li>
                  <li>
                    <strong>Customize button</strong> (optional)
                    <ul class="list-disc list-inside ml-6 mt-1 text-sm text-gray-400">
                      <li>Add label like "-1" or "↓"</li>
                      <li>Add icon</li>
                      <li>Set background color</li>
                    </ul>
                  </li>
                </ol>
              </div>
            </div>

            <!-- Pro Tips -->
            <div class="bg-purple-900/30 border border-purple-500 rounded-lg p-6">
              <h3 class="text-lg font-semibold text-purple-300 mb-3">💡 Pro Tips</h3>
              <ul class="list-disc list-inside space-y-2 text-purple-200 ml-4">
                <li><strong>Layout:</strong> Group related buttons together (all timers in one row, counters in another)</li>
                <li><strong>Visual Feedback:</strong> Use different colors for different rule types (red for timers, blue for counters)</li>
                <li><strong>Pair Buttons:</strong> Put a text widget above its corresponding action button</li>
                <li><strong>Testing:</strong> Start a playthrough and watch buttons update in real-time</li>
                <li><strong>Multiple Monitors:</strong> Keep Stream Deck visible during gameplay for quick glances</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- OBS Setup -->
        <div class="bg-gray-800/50 backdrop-blur rounded-lg p-6 border border-gray-700">
          <h2 class="text-2xl font-bold text-white mb-4">🎥 OBS Browser Source Setup</h2>
          
          <div class="space-y-4 text-gray-300">
            <p class="text-gray-400 mb-4">
              See the "OBS Sources" tab for overlay URLs and detailed setup instructions.
            </p>
            <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700">
              <p class="text-sm">
                <strong>Quick Summary:</strong> Add Browser Source in OBS → Paste overlay URL → Set size to 1920x1080 → Position where you want it on stream → Done! The overlay auto-refreshes every 2 seconds.
              </p>
            </div>
          </div>
        </div>

        <!-- Troubleshooting -->
        <div class="bg-red-900/30 border border-red-500 rounded-lg p-6">
          <h3 class="text-xl font-bold text-red-300 mb-4">🔧 Troubleshooting</h3>
          
          <div class="space-y-4 text-red-200">
            <div>
              <h4 class="font-semibold mb-2">"Title from API" not showing in Stream Deck:</h4>
              <ul class="list-disc list-inside ml-4 text-sm">
                <li>Update to Stream Deck Software 6.0 or later</li>
                <li>Some older plugins don't support this feature</li>
              </ul>
            </div>

            <div>
              <h4 class="font-semibold mb-2">Text not updating:</h4>
              <ul class="list-disc list-inside ml-4 text-sm">
                <li>Check update interval is set (3 seconds recommended)</li>
                <li>Verify auth token is correct (copy fresh token from above)</li>
                <li>Make sure you have an active playthrough</li>
                <li>Check network connectivity to localhost:3000</li>
              </ul>
            </div>

            <div>
              <h4 class="font-semibold mb-2">"Not logged in" error:</h4>
              <ul class="list-disc list-inside ml-4 text-sm">
                <li>Copy a fresh auth token from the section above</li>
                <li>Make sure header format is: <code class="bg-red-950 px-2 py-1 rounded">Authorization: Bearer TOKEN</code></li>
                <li>No extra spaces or quotes around the token</li>
              </ul>
            </div>

            <div>
              <h4 class="font-semibold mb-2">Action buttons not working:</h4>
              <ul class="list-disc list-inside ml-4 text-sm">
                <li>Verify Method is set to POST (not GET)</li>
                <li>Check both headers are added (Authorization + Content-Type)</li>
                <li>Make sure index parameter matches your active rules (index=1 for 1st counter, index=2 for 2nd, etc.)</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- END SETUP GUIDE TAB -->

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const authToken = ref<string | null>(null)
const tokenCopied = ref(false)
const activeTab = ref('stream-deck')

const tabs = [
  { id: 'stream-deck', name: 'Stream Deck', icon: '🎛️' },
  { id: 'obs', name: 'OBS Sources', icon: '🎥' },
  { id: 'setup', name: 'Setup Guide', icon: '📖' },
]

// Get auth token from localStorage
onMounted(() => {
  authToken.value = localStorage.getItem('auth_token')
})

const baseUrl = computed(() => {
  if (process.client) {
    return window.location.origin
  }
  return 'http://localhost:3000'
})

const getUrl = (path: string): string => {
  return `${baseUrl.value}${path}`
}

const copyToken = async () => {
  if (authToken.value) {
    await navigator.clipboard.writeText(authToken.value)
    tokenCopied.value = true
    setTimeout(() => {
      tokenCopied.value = false
    }, 2000)
  }
}
</script>

