export interface OAuthProvidersData {
  twitchAccountLinking: boolean
}

export interface OAuthProvidersResponse {
  success: boolean
  data: OAuthProvidersData
}

export function useOAuthProviders() {
  const config = useRuntimeConfig()
  const providers = ref<OAuthProvidersData | null>(null)
  const loaded = ref(false)

  const load = async () => {
    try {
      const res = await $fetch<OAuthProvidersResponse>(
        `${config.public.apiBase}/oauth/providers`
      )
      if (res.success && res.data) {
        providers.value = res.data
      }
    } catch {
      providers.value = { twitchAccountLinking: false }
    } finally {
      loaded.value = true
    }
  }

  return { providers, loaded, load }
}
