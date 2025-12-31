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
  <div class="profile-page">
      <!-- Page Header -->
      <div class="profile-page__header">
        <h1 class="profile-page__title">Edit Profile</h1>
        <p class="profile-page__description">Update your profile information and avatar</p>
      </div>

      <!-- Profile Section -->
      <div class="profile-page__section">
        <h2 class="profile-page__section-title">Profile Information</h2>
        
        <!-- Success Message -->
        <div v-if="profileSuccess" class="profile-page__message profile-page__message--success">
          Profile updated successfully!
        </div>
        
        <!-- Error Message -->
        <div v-if="profileError" class="profile-page__message profile-page__message--error">
          {{ profileError }}
        </div>

        <form @submit.prevent="handleUpdateProfile" class="profile-page__form">
          <!-- Avatar Upload -->
          <div class="profile-page__avatar-section">
            <div class="profile-page__avatar-wrapper">
              <div class="profile-page__avatar">
                <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="profile-page__avatar-image">
                <svg v-else class="profile-page__avatar-placeholder" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
              </div>
            </div>
            <div class="profile-page__avatar-upload-wrapper">
              <label for="avatar-upload" class="profile-page__avatar-upload-label">
                <svg class="profile-page__avatar-upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Upload Photo
              </label>
              <input
                id="avatar-upload"
                type="file"
                accept="image/*"
                class="profile-page__avatar-upload-input"
                @change="handleImageUpload"
              >
              <p class="profile-page__avatar-hint">
                JPG, PNG or GIF. Max 5MB. Will be resized to 200x200.
              </p>
            </div>
          </div>

          <!-- Email -->
          <div class="profile-page__field">
            <label for="email" class="profile-page__label">
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="profile-page__input"
            >
          </div>

          <!-- Username -->
          <div class="profile-page__field">
            <label for="username" class="profile-page__label">
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
              class="profile-page__input profile-page__input--magenta"
            >
          </div>

          <!-- Submit Button -->
          <div class="profile-page__actions">
            <button
              type="submit"
              :disabled="loadingProfile"
              class="profile-page__button"
            >
              <span v-if="loadingProfile">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Password Section -->
      <div class="profile-page__section">
        <h2 class="profile-page__section-title">Change Password</h2>
        
        <!-- Success Message -->
        <div v-if="passwordSuccess" class="profile-page__message profile-page__message--success">
          Password updated successfully!
        </div>
        
        <!-- Error Message -->
        <div v-if="passwordError" class="profile-page__message profile-page__message--error">
          {{ passwordError }}
        </div>

        <form @submit.prevent="handleUpdatePassword" class="profile-page__form">
          <!-- Current Password -->
          <div class="profile-page__field">
            <label for="current-password" class="profile-page__label">
              Current Password
            </label>
            <input
              id="current-password"
              v-model="currentPassword"
              type="password"
              required
              class="profile-page__input"
            >
          </div>

          <!-- New Password -->
          <div class="profile-page__field">
            <label for="new-password" class="profile-page__label">
              New Password
            </label>
            <input
              id="new-password"
              v-model="newPassword"
              type="password"
              required
              minlength="8"
              class="profile-page__input profile-page__input--magenta"
            >
            <p class="profile-page__hint">Minimum 8 characters</p>
          </div>

          <!-- Confirm Password -->
          <div class="profile-page__field">
            <label for="confirm-new-password" class="profile-page__label">
              Confirm New Password
            </label>
            <input
              id="confirm-new-password"
              v-model="confirmPassword"
              type="password"
              required
              class="profile-page__input"
            >
          </div>

          <!-- Submit Button -->
          <div class="profile-page__actions">
            <button
              type="submit"
              :disabled="loadingPassword"
              class="profile-page__button"
            >
              <span v-if="loadingPassword">Updating...</span>
              <span v-else>Update Password</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Connected Accounts Section -->
      <div class="profile-page__section">
        <h2 class="profile-page__section-title">Connected Accounts</h2>
        
        <!-- Success Message -->
        <div v-if="connectionSuccess" class="profile-page__message profile-page__message--success">
          {{ connectionSuccess }}
        </div>
        
        <!-- Error Message -->
        <div v-if="connectionError" class="profile-page__message profile-page__message--error">
          {{ connectionError }}
        </div>

        <div class="profile-page__accounts">
          <!-- Discord Connection -->
          <div class="profile-page__account-card">
            <div class="profile-page__account-left">
              <div class="profile-page__account-icon-wrapper profile-page__account-icon-wrapper--discord">
                <svg class="profile-page__account-icon" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
              </div>
              <div class="profile-page__account-info">
                <h3 class="profile-page__account-name">Discord</h3>
                <p v-if="user?.discordUsername" class="profile-page__account-status">
                  Connected as <span class="profile-page__account-username">{{ user.discordUsername }}</span>
                </p>
                <p v-else class="profile-page__account-status">
                  Not connected
                </p>
              </div>
            </div>
            <button
              v-if="!user?.discordId"
              @click="handleConnectDiscord"
              :disabled="connectingDiscord"
              class="profile-page__account-button profile-page__account-button--connect-discord"
            >
              <span v-if="connectingDiscord">Connecting...</span>
              <span v-else>Connect</span>
            </button>
            <button
              v-else
              @click="handleDisconnectDiscord"
              :disabled="disconnectingDiscord"
              class="profile-page__account-button profile-page__account-button--disconnect"
            >
              <span v-if="disconnectingDiscord">Disconnecting...</span>
              <span v-else>Disconnect</span>
            </button>
          </div>

          <!-- Twitch Connection -->
          <div class="profile-page__account-card">
            <div class="profile-page__account-left">
              <div class="profile-page__account-icon-wrapper profile-page__account-icon-wrapper--twitch">
                <svg class="profile-page__account-icon" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/>
                </svg>
              </div>
              <div class="profile-page__account-info">
                <h3 class="profile-page__account-name">Twitch</h3>
                <p v-if="user?.twitchUsername" class="profile-page__account-status">
                  Connected as <span class="profile-page__account-username profile-page__account-username--magenta">{{ user.twitchUsername }}</span>
                </p>
                <p v-else class="profile-page__account-status">
                  Not connected
                </p>
              </div>
            </div>
            <button
              v-if="!user?.twitchId"
              @click="handleConnectTwitch"
              :disabled="connectingTwitch"
              class="profile-page__account-button profile-page__account-button--connect-twitch"
            >
              <span v-if="connectingTwitch">Connecting...</span>
              <span v-else>Connect</span>
            </button>
            <button
              v-else
              @click="handleDisconnectTwitch"
              :disabled="disconnectingTwitch"
              class="profile-page__account-button profile-page__account-button--disconnect"
            >
              <span v-if="disconnectingTwitch">Disconnecting...</span>
              <span v-else>Disconnect</span>
            </button>
          </div>
        </div>
      </div>
  </div>
</template>
