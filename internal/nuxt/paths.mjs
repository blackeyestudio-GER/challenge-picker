import { joinRelativeURL } from 'ufo'

const getBaseURL = () => process.env.NUXT_APP_BASE_URL || '/'

const getBuildAssetsDir = () => process.env.NUXT_APP_BUILD_ASSETS_DIR || '/_nuxt/'

const getPublicBaseURL = () => process.env.NUXT_APP_CDN_URL || getBaseURL()

export function baseURL() {
  return getBaseURL()
}

export function buildAssetsDir() {
  return getBuildAssetsDir()
}

export function publicAssetsURL(...path) {
  const publicBase = getPublicBaseURL()

  return path.length > 0 ? joinRelativeURL(publicBase, ...path) : publicBase
}

export function buildAssetsURL(...path) {
  return joinRelativeURL(publicAssetsURL(), buildAssetsDir(), ...path)
}
