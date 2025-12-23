<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  middleware: 'auth'
})

const { user, loadAuth, getAuthHeader } = useAuth()

onMounted(() => {
  loadAuth()
  if (user.value) {
    email.value = user.value.email
    username.value = user.value.username
    avatarPreview.value = user.value.avatar || null
  }

  // Listen for OAuth callback messages
  window.addEventListener('message', handleOAuthMessage)
})

onUnmounted(() => {
  window.removeEventListener('message', handleOAuthMessage)
})

// Handle OAuth popup messages
const handleOAuthMessage = async (event: MessageEvent) => {
  const data = event.data
  
  if (data.type === 'discord_connected') {
    connectionSuccess.value = `Discord connected as ${data.username}!`
    // Fetch fresh user data from API
    await fetchUserData()
    setTimeout(() => connectionSuccess.value = '', 3000)
  } else if (data.type === 'discord_error') {
    connectionError.value = data.message
  } else if (data.type === 'twitch_connected') {
    connectionSuccess.value = `Twitch connected as ${data.username}!`
    // Fetch fresh user data from API
    await fetchUserData()
    setTimeout(() => connectionSuccess.value = '', 3000)
  } else if (data.type === 'twitch_error') {
    connectionError.value = data.message
  }
}

// Fetch current user data from API
const fetchUserData = async () => {
  try {
    const response = await $fetch(`/api/users/me`, {
      headers: getAuthHeader()
    })
    
    if (response.success && response.data) {
      // Update user in composable
      if (user.value) {
        Object.assign(user.value, response.data)
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      }
    }
  } catch (error) {
    console.error('Failed to fetch user data:', error)
  }
}

// Profile form state
const email = ref('')
const username = ref('')
const avatarPreview = ref<string | null>(null)
const avatarBase64 = ref<string | null>(null)
const loadingProfile = ref(false)
const profileError = ref('')
const profileSuccess = ref(false)

// Password form state
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const loadingPassword = ref(false)
const passwordError = ref('')
const passwordSuccess = ref(false)

// Handle image upload and resize
const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (!file) return
  
  // Check file type
  if (!file.type.startsWith('image/')) {
    profileError.value = 'Please select an image file'
    return
  }
  
  // Check file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    profileError.value = 'Image must be smaller than 5MB'
    return
  }
  
  const reader = new FileReader()
  
  reader.onload = (e) => {
    const img = new Image()
    img.src = e.target?.result as string
    
    img.onload = () => {
      // Resize image to max 200x200
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')
      
      const maxSize = 200
      let width = img.width
      let height = img.height
      
      if (width > height) {
        if (width > maxSize) {
          height = (height * maxSize) / width
          width = maxSize
        }
      } else {
        if (height > maxSize) {
          width = (width * maxSize) / height
          height = maxSize
        }
      }
      
      canvas.width = width
      canvas.height = height
      
      ctx?.drawImage(img, 0, 0, width, height)
      
      // Convert to base64 (JPEG, 80% quality)
      const resizedBase64 = canvas.toDataURL('image/jpeg', 0.8)
      avatarBase64.value = resizedBase64
      avatarPreview.value = resizedBase64
    }
  }
  
  reader.readAsDataURL(file)
}

// Update profile
const handleUpdateProfile = async () => {
  profileError.value = ''
  profileSuccess.value = false
  loadingProfile.value = true
  
  try {
    const response = await $fetch(`/api/users/${user.value?.uuid}`, {
      method: 'PUT',
      headers: getAuthHeader(),
      body: {
        email: email.value,
        username: username.value,
        avatar: avatarBase64.value || user.value?.avatar
      }
    })
    
    if (response.success) {
      profileSuccess.value = true
      // Update local user data
      if (user.value) {
        user.value.email = email.value
        user.value.username = username.value
        user.value.avatar = avatarBase64.value || user.value.avatar
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      }
      setTimeout(() => profileSuccess.value = false, 3000)
    }
  } catch (error: any) {
    profileError.value = error.data?.error?.message || 'Failed to update profile'
  } finally {
    loadingProfile.value = false
  }
}

// Update password
const handleUpdatePassword = async () => {
  passwordError.value = ''
  passwordSuccess.value = false
  
  // Validate passwords match
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'New passwords do not match'
    return
  }
  
  loadingPassword.value = true
  
  try {
    const response = await $fetch(`/api/users/${user.value?.uuid}/password`, {
      method: 'PUT',
      headers: getAuthHeader(),
      body: {
        currentPassword: currentPassword.value,
        newPassword: newPassword.value
      }
    })
    
    if (response.success) {
      passwordSuccess.value = true
      currentPassword.value = ''
      newPassword.value = ''
      confirmPassword.value = ''
      setTimeout(() => passwordSuccess.value = false, 3000)
    }
  } catch (error: any) {
    passwordError.value = error.data?.error?.message || 'Failed to update password'
  } finally {
    loadingPassword.value = false
  }
}

// Discord/Twitch connection state
const connectingDiscord = ref(false)
const connectingTwitch = ref(false)
const disconnectingDiscord = ref(false)
const disconnectingTwitch = ref(false)
const connectionError = ref('')
const connectionSuccess = ref('')

// Connect Discord
const handleConnectDiscord = async () => {
  connectingDiscord.value = true
  connectionError.value = ''
  
  try {
    const response = await $fetch('/api/user/connect/discord', {
      headers: getAuthHeader()
    })
    
    if (response.success && response.data.authUrl) {
      // Open Discord OAuth in new window
      window.open(response.data.authUrl, '_blank', 'width=500,height=700')
    }
  } catch (error: any) {
    connectionError.value = error.data?.error?.message || 'Failed to connect Discord'
  } finally {
    connectingDiscord.value = false
  }
}

// Disconnect Discord
const handleDisconnectDiscord = async () => {
  if (!confirm('Are you sure you want to disconnect your Discord account?')) {
    return
  }
  
  disconnectingDiscord.value = true
  connectionError.value = ''
  
  try {
    const response = await $fetch('/api/user/disconnect/discord', {
      method: 'POST',
      headers: getAuthHeader()
    })
    
    if (response.success) {
      connectionSuccess.value = 'Discord disconnected successfully!'
      if (user.value) {
        user.value.discordId = null
        user.value.discordUsername = null
        user.value.discordAvatar = null
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      }
      setTimeout(() => connectionSuccess.value = '', 3000)
    }
  } catch (error: any) {
    connectionError.value = error.data?.error?.message || 'Failed to disconnect Discord'
  } finally {
    disconnectingDiscord.value = false
  }
}

// Connect Twitch
const handleConnectTwitch = async () => {
  connectingTwitch.value = true
  connectionError.value = ''
  
  try {
    const response = await $fetch('/api/user/connect/twitch', {
      headers: getAuthHeader()
    })
    
    if (response.success && response.data.authUrl) {
      // Open Twitch OAuth in new window
      window.open(response.data.authUrl, '_blank', 'width=500,height=700')
    }
  } catch (error: any) {
    connectionError.value = error.data?.error?.message || 'Failed to connect Twitch'
  } finally {
    connectingTwitch.value = false
  }
}

// Disconnect Twitch
const handleDisconnectTwitch = async () => {
  if (!confirm('Are you sure you want to disconnect your Twitch account?')) {
    return
  }
  
  disconnectingTwitch.value = true
  connectionError.value = ''
  
  try {
    const response = await $fetch('/api/user/disconnect/twitch', {
      method: 'POST',
      headers: getAuthHeader()
    })
    
    if (response.success) {
      connectionSuccess.value = 'Twitch disconnected successfully!'
      if (user.value) {
        user.value.twitchId = null
        user.value.twitchUsername = null
        user.value.twitchAvatar = null
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      }
      setTimeout(() => connectionSuccess.value = '', 3000)
    }
  } catch (error: any) {
    connectionError.value = error.data?.error?.message || 'Failed to disconnect Twitch'
  } finally {
    disconnectingTwitch.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan to-magenta mb-2">Edit Profile</h1>
        <p class="text-gray-300">Update your profile information and avatar</p>
      </div>

      <!-- Profile Section -->
      <div class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-2xl p-6 mb-6 border border-gray-800">
        <h2 class="text-2xl font-bold text-white mb-6">Profile Information</h2>
        
        <!-- Success Message -->
        <div v-if="profileSuccess" class="bg-cyan/10 border border-cyan/50 text-cyan-100 rounded-lg p-4 mb-6">
          Profile updated successfully!
        </div>
        
        <!-- Error Message -->
        <div v-if="profileError" class="bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg p-4 mb-6">
          {{ profileError }}
        </div>

        <form @submit.prevent="handleUpdateProfile" class="space-y-6">
          <!-- Avatar Upload -->
          <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
            <div class="flex-shrink-0">
              <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-800 flex items-center justify-center border-2 border-cyan/30">
                <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="w-full h-full object-cover">
                <svg v-else class="w-16 h-16 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
              </div>
            </div>
            <div class="flex-1 text-center sm:text-left">
              <label for="avatar-upload" class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-700 rounded-lg text-sm font-medium text-gray-300 bg-gray-800 hover:bg-gray-700 hover:border-cyan/50 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Upload Photo
              </label>
              <input
                id="avatar-upload"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleImageUpload"
              >
              <p class="mt-2 text-sm text-gray-500">
                JPG, PNG or GIF. Max 5MB. Will be resized to 200x200.
              </p>
            </div>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan focus:border-transparent transition"
            >
          </div>

          <!-- Username -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
              Username
            </label>
            <input
              id="username"
              v-model="username"
              type="text"
              required
              minlength="3"
              maxlength="50"
              pattern="[a-zA-Z0-9_-]+"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-magenta focus:border-transparent transition"
            >
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="loadingProfile"
              class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta hover:from-cyan-muted hover:to-magenta-muted text-white font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
            >
              <span v-if="loadingProfile">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Password Section -->
      <div class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-2xl p-6 mb-6 border border-gray-800">
        <h2 class="text-2xl font-bold text-white mb-6">Change Password</h2>
        
        <!-- Success Message -->
        <div v-if="passwordSuccess" class="bg-cyan/10 border border-cyan/50 text-cyan-100 rounded-lg p-4 mb-6">
          Password updated successfully!
        </div>
        
        <!-- Error Message -->
        <div v-if="passwordError" class="bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg p-4 mb-6">
          {{ passwordError }}
        </div>

        <form @submit.prevent="handleUpdatePassword" class="space-y-6">
          <!-- Current Password -->
          <div>
            <label for="current-password" class="block text-sm font-medium text-gray-300 mb-2">
              Current Password
            </label>
            <input
              id="current-password"
              v-model="currentPassword"
              type="password"
              required
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan focus:border-transparent transition"
            >
          </div>

          <!-- New Password -->
          <div>
            <label for="new-password" class="block text-sm font-medium text-gray-300 mb-2">
              New Password
            </label>
            <input
              id="new-password"
              v-model="newPassword"
              type="password"
              required
              minlength="8"
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-magenta focus:border-transparent transition"
            >
            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirm-new-password" class="block text-sm font-medium text-gray-300 mb-2">
              Confirm New Password
            </label>
            <input
              id="confirm-new-password"
              v-model="confirmPassword"
              type="password"
              required
              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan focus:border-transparent transition"
            >
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="loadingPassword"
              class="px-6 py-3 bg-gradient-to-r from-cyan to-magenta hover:from-cyan-muted hover:to-magenta-muted text-white font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
            >
              <span v-if="loadingPassword">Updating...</span>
              <span v-else">Update Password</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Connected Accounts Section -->
      <div class="bg-gray-900/80 backdrop-blur-sm rounded-xl shadow-2xl p-6 border border-gray-800">
        <h2 class="text-2xl font-bold text-white mb-6">Connected Accounts</h2>
        
        <!-- Success Message -->
        <div v-if="connectionSuccess" class="bg-cyan/10 border border-cyan/50 text-cyan-100 rounded-lg p-4 mb-6">
          {{ connectionSuccess }}
        </div>
        
        <!-- Error Message -->
        <div v-if="connectionError" class="bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg p-4 mb-6">
          {{ connectionError }}
        </div>

        <div class="space-y-6">
          <!-- Discord Connection -->
          <div class="flex items-center justify-between p-4 bg-gray-800/50 border border-gray-700 rounded-lg">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 rounded-lg bg-[#5865F2] flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">Discord</h3>
                <p v-if="user?.discordUsername" class="text-sm text-gray-400">
                  Connected as <span class="text-cyan">{{ user.discordUsername }}</span>
                </p>
                <p v-else class="text-sm text-gray-400">
                  Not connected
                </p>
              </div>
            </div>
            <button
              v-if="!user?.discordId"
              @click="handleConnectDiscord"
              :disabled="connectingDiscord"
              class="px-4 py-2 bg-[#5865F2] hover:bg-[#4752C4] text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="connectingDiscord">Connecting...</span>
              <span v-else>Connect</span>
            </button>
            <button
              v-else
              @click="handleDisconnectDiscord"
              :disabled="disconnectingDiscord"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="disconnectingDiscord">Disconnecting...</span>
              <span v-else>Disconnect</span>
            </button>
          </div>

          <!-- Twitch Connection -->
          <div class="flex items-center justify-between p-4 bg-gray-800/50 border border-gray-700 rounded-lg">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 rounded-lg bg-[#9146FF] flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">Twitch</h3>
                <p v-if="user?.twitchUsername" class="text-sm text-gray-400">
                  Connected as <span class="text-magenta">{{ user.twitchUsername }}</span>
                </p>
                <p v-else class="text-sm text-gray-400">
                  Not connected
                </p>
              </div>
            </div>
            <button
              v-if="!user?.twitchId"
              @click="handleConnectTwitch"
              :disabled="connectingTwitch"
              class="px-4 py-2 bg-[#9146FF] hover:bg-[#772CE8] text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="connectingTwitch">Connecting...</span>
              <span v-else>Connect</span>
            </button>
            <button
              v-else
              @click="handleDisconnectTwitch"
              :disabled="disconnectingTwitch"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="disconnectingTwitch">Disconnecting...</span>
              <span v-else>Disconnect</span>
            </button>
          </div>
        </div>
      </div>
  </div>
</template>
