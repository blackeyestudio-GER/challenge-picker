/**
 * Returns a same-origin relative path safe to use after login (blocks open redirects).
 */
export function getSafeRedirectPath(queryValue: unknown): string | null {
  const raw = Array.isArray(queryValue) ? queryValue[0] : queryValue
  if (typeof raw !== 'string' || raw.length === 0) {
    return null
  }
  // Internal app paths only; disallow protocol-relative URLs and backslashes
  if (!raw.startsWith('/') || raw.startsWith('//') || raw.includes('\\')) {
    return null
  }
  return raw
}
