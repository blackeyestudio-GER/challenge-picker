<script setup lang="ts">
import { useProfilePage as useProfilePageComposable } from '~/composables/pages/useProfilePage'

definePageMeta({
  middleware: 'auth'
})

const {
  user,
  email,
  username,
  avatarPreview,
  loadingProfile,
  profileError,
  profileSuccess,
  currentPassword,
  newPassword,
  confirmPassword,
  loadingPassword,
  passwordError,
  passwordSuccess,
  deleteConfirmation,
  deletePassword,
  deletingAccount,
  deleteAccountError,
  canDeleteAccount,
  connectingDiscord,
  disconnectingDiscord,
  connectionError,
  connectionSuccess,
  bootstrap,
  teardown,
  handleImageUpload,
  handleUpdateProfile,
  handleUpdatePassword,
  handleConnectDiscord,
  handleDisconnectDiscord,
  handleDeleteAccount
} = useProfilePageComposable()

onMounted(() => {
  bootstrap()
})

onUnmounted(() => {
  teardown()
})
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
        <h2 class="section-title">Profile Information</h2>
        
        <!-- Success Message -->
        <div v-if="profileSuccess" class="profile-page__message profile-page__message--success">
          Profile updated successfully!
        </div>
        
        <!-- Error Message -->
        <div v-if="profileError" class="profile-page__message profile-page__message--error">
          {{ profileError }}
        </div>

        <form class="profile-page__form" @submit.prevent="handleUpdateProfile">
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
        <h2 class="section-title">Change Password</h2>
        
        <!-- Success Message -->
        <div v-if="passwordSuccess" class="profile-page__message profile-page__message--success">
          Password updated successfully!
        </div>
        
        <!-- Error Message -->
        <div v-if="passwordError" class="profile-page__message profile-page__message--error">
          {{ passwordError }}
        </div>

        <form class="profile-page__form" @submit.prevent="handleUpdatePassword">
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
        <h2 class="section-title">Connected Accounts</h2>
        
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
              :disabled="connectingDiscord"
              class="profile-page__account-button profile-page__account-button--connect-discord"
              @click="handleConnectDiscord"
            >
              <span v-if="connectingDiscord">Connecting...</span>
              <span v-else>Connect</span>
            </button>
            <button
              v-else
              :disabled="disconnectingDiscord"
              class="profile-page__account-button profile-page__account-button--disconnect"
              @click="handleDisconnectDiscord"
            >
              <span v-if="disconnectingDiscord">Disconnecting...</span>
              <span v-else>Disconnect</span>
            </button>
          </div>
        </div>
      </div>

      <div class="profile-page__section">
        <h2 class="section-title">Delete Account</h2>

        <div class="profile-page__message profile-page__message--error">
          Deleting your account is permanent. Your personal profile data and active login methods will be removed immediately.
        </div>

        <div v-if="deleteAccountError" class="profile-page__message profile-page__message--error">
          {{ deleteAccountError }}
        </div>

        <form class="profile-page__form" @submit.prevent="handleDeleteAccount">
          <div class="profile-page__field">
            <label for="delete-confirmation" class="profile-page__label">
              Type DELETE to confirm
            </label>
            <input
              id="delete-confirmation"
              v-model="deleteConfirmation"
              type="text"
              autocomplete="off"
              class="profile-page__input"
            >
          </div>

          <div v-if="user?.oauthProvider === null" class="profile-page__field">
            <label for="delete-password" class="profile-page__label">
              Current Password
            </label>
            <input
              id="delete-password"
              v-model="deletePassword"
              type="password"
              autocomplete="current-password"
              class="profile-page__input"
            >
          </div>

          <div class="profile-page__actions">
            <button
              type="submit"
              :disabled="deletingAccount || !canDeleteAccount"
              class="profile-page__button profile-page__account-button--disconnect"
            >
              <span v-if="deletingAccount">Deleting...</span>
              <span v-else>Delete Account</span>
            </button>
          </div>
        </form>
      </div>
  </div>
</template>
