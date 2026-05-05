import { computed, ref } from 'vue'
import type { AuthUser } from '~/generated/api-contracts'
import { useAuth } from '~/composables/useAuth'
import { extractErrorMessage } from '~/utils/errorHandler'

export const useProfilePage = () => {
  const { user, loadAuth, getAuthHeader, deleteAccount } = useAuth()

  const email = ref('')
  const username = ref('')
  const avatarPreview = ref<string | null>(null)
  const avatarBase64 = ref<string | null>(null)
  const loadingProfile = ref(false)
  const profileError = ref('')
  const profileSuccess = ref(false)

  const currentPassword = ref('')
  const newPassword = ref('')
  const confirmPassword = ref('')
  const loadingPassword = ref(false)
  const passwordError = ref('')
  const passwordSuccess = ref(false)

  const deleteConfirmation = ref('')
  const deletePassword = ref('')
  const deletingAccount = ref(false)
  const deleteAccountError = ref('')
  const canDeleteAccount = computed(() => deleteConfirmation.value === 'DELETE')

  const connectingDiscord = ref(false)
  const disconnectingDiscord = ref(false)
  const connectionError = ref('')
  const connectionSuccess = ref('')

  const fetchUserData = async () => {
    try {
      const response = await $fetch<{ success: boolean; data: AuthUser }>(`/api/users/me`, {
        headers: getAuthHeader()
      })

      if (response.success && response.data && user.value) {
        Object.assign(user.value, response.data)
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      }
    } catch (error: unknown) {
      connectionError.value = extractErrorMessage(error, 'Failed to refresh connected account details')
    }
  }

  const handleOAuthMessage = async (event: MessageEvent) => {
    const data = event.data

    if (data.type === 'discord_connected') {
      connectionSuccess.value = `Discord connected as ${data.username}!`
      await fetchUserData()
      setTimeout(() => {
        connectionSuccess.value = ''
      }, 3000)
    } else if (data.type === 'discord_error') {
      connectionError.value = data.message
    }
  }

  const bootstrap = () => {
    loadAuth()
    if (user.value) {
      email.value = user.value.email
      username.value = user.value.username
      avatarPreview.value = user.value.avatar || null
    }

    window.addEventListener('message', handleOAuthMessage)
  }

  const teardown = () => {
    window.removeEventListener('message', handleOAuthMessage)
  }

  const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    if (!file.type.startsWith('image/')) {
      profileError.value = 'Please select an image file'
      return
    }

    if (file.size > 5 * 1024 * 1024) {
      profileError.value = 'Image must be smaller than 5MB'
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      const img = new Image()
      img.src = e.target?.result as string

      img.onload = () => {
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
        } else if (height > maxSize) {
          width = (width * maxSize) / height
          height = maxSize
        }

        canvas.width = width
        canvas.height = height
        ctx?.drawImage(img, 0, 0, width, height)

        const resizedBase64 = canvas.toDataURL('image/jpeg', 0.8)
        avatarBase64.value = resizedBase64
        avatarPreview.value = resizedBase64
      }
    }

    reader.readAsDataURL(file)
  }

  const handleUpdateProfile = async () => {
    profileError.value = ''
    profileSuccess.value = false
    loadingProfile.value = true

    try {
      const response = await $fetch<{ success: boolean }>(`/api/users/${user.value?.uuid}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: {
          email: email.value,
          username: username.value,
          avatar: avatarBase64.value || user.value?.avatar
        }
      })

      if (response.success && user.value) {
        profileSuccess.value = true
        user.value.email = email.value
        user.value.username = username.value
        user.value.avatar = avatarBase64.value || user.value.avatar
        localStorage.setItem('auth_user', JSON.stringify(user.value))
        setTimeout(() => {
          profileSuccess.value = false
        }, 3000)
      }
    } catch (error: unknown) {
      profileError.value = extractErrorMessage(error, 'Failed to update profile')
    } finally {
      loadingProfile.value = false
    }
  }

  const handleUpdatePassword = async () => {
    passwordError.value = ''
    passwordSuccess.value = false

    if (newPassword.value !== confirmPassword.value) {
      passwordError.value = 'New passwords do not match'
      return
    }

    loadingPassword.value = true

    try {
      const response = await $fetch<{ success: boolean }>(`/api/users/${user.value?.uuid}/password`, {
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
        setTimeout(() => {
          passwordSuccess.value = false
        }, 3000)
      }
    } catch (error: unknown) {
      passwordError.value = extractErrorMessage(error, 'Failed to update password')
    } finally {
      loadingPassword.value = false
    }
  }

  const handleConnectDiscord = async () => {
    connectingDiscord.value = true
    connectionError.value = ''

    try {
      const response = await $fetch<{ success: boolean; data?: { authUrl?: string } }>('/api/user/connect/discord', {
        headers: getAuthHeader()
      })

      if (response.success && response.data?.authUrl) {
        window.open(response.data.authUrl, '_blank', 'width=500,height=700')
      }
    } catch (error: unknown) {
      connectionError.value = extractErrorMessage(error, 'Failed to connect Discord')
    } finally {
      connectingDiscord.value = false
    }
  }

  const handleDisconnectDiscord = async () => {
    if (!confirm('Are you sure you want to disconnect your Discord account?')) {
      return
    }

    disconnectingDiscord.value = true
    connectionError.value = ''

    try {
      const response = await $fetch<{ success: boolean }>('/api/user/disconnect/discord', {
        method: 'POST',
        headers: getAuthHeader()
      })

      if (response.success && user.value) {
        connectionSuccess.value = 'Discord disconnected successfully!'
        user.value.discordId = null
        user.value.discordUsername = null
        user.value.discordAvatar = null
        localStorage.setItem('auth_user', JSON.stringify(user.value))
        setTimeout(() => {
          connectionSuccess.value = ''
        }, 3000)
      }
    } catch (error: unknown) {
      connectionError.value = extractErrorMessage(error, 'Failed to disconnect Discord')
    } finally {
      disconnectingDiscord.value = false
    }
  }

  const handleDeleteAccount = async () => {
    deleteAccountError.value = ''

    if (deleteConfirmation.value !== 'DELETE') {
      deleteAccountError.value = 'Type DELETE exactly to confirm account deletion'
      return
    }

    deletingAccount.value = true
    try {
      const result = await deleteAccount(deleteConfirmation.value, deletePassword.value)
      if (!result.success) {
        deleteAccountError.value = result.error ?? 'Failed to delete account'
        return
      }

      await navigateTo('/login')
    } finally {
      deletingAccount.value = false
    }
  }

  return {
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
  }
}
