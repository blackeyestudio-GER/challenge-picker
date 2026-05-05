import { ref } from 'vue'
import { extractErrorMessage } from '~/utils/errorHandler'

export const useDiscordCallbackPage = () => {
  const callbackError = ref('')

  const bootstrap = async () => {
    const route = useRoute()
    const code = route.query.code as string
    const state = route.query.state as string

    if (!code) {
      if (window.opener) {
        window.opener.postMessage({ type: 'discord_login_error', message: 'Authorization cancelled' }, '*')
        window.close()
      } else {
        await navigateTo('/login')
      }
      return
    }

    try {
      await $fetch(`/api/user/connect/discord/callback?code=${code}&state=${state}`)
    } catch (error: unknown) {
      if (window.opener) {
        window.opener.postMessage({
          type: 'discord_login_error',
          message: extractErrorMessage(error, 'Login failed')
        }, '*')
        setTimeout(() => window.close(), 1000)
      } else {
        callbackError.value = extractErrorMessage(error, 'Discord login failed')
      }
    }
  }

  return {
    callbackError,
    bootstrap
  }
}
