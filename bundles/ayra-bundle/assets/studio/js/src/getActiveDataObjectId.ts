import { AYRA_LAST_DATA_OBJECT_ID_KEY, rememberAyraLastDataObjectId } from './indiaGeoSession'

export function getActiveDataObjectId(): number | null {
  const href = window.location.href
  const patterns = [/\/pimcore-studio\/api\/data-objects\/(\d+)/, /\/data-objects\/(\d+)/, /[?&]objectId=(\d+)/i]
  for (const p of patterns) {
    const m = href.match(p)
    if (m !== null) {
      const n = Number(m[1])
      if (!Number.isNaN(n)) {
        rememberAyraLastDataObjectId(n)
        return n
      }
    }
  }
  const stored = sessionStorage.getItem(AYRA_LAST_DATA_OBJECT_ID_KEY)
  if (stored !== null) {
    const n = Number(stored)
    if (!Number.isNaN(n)) {
      return n
    }
  }
  return null
}
