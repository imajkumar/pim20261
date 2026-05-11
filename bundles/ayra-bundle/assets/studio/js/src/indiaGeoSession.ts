/** Session keys shared by the fetch patch and RTK prefetch so city select-options stay aligned. */

export function ayraGeoStateKey(objectId: number): string {
  return `ayra.indiaGeo.state.${objectId}`
}

export function setAyraIndiaGeoState(objectId: number, stateSlug: string): void {
  sessionStorage.setItem(ayraGeoStateKey(objectId), stateSlug)
}

export const AYRA_LAST_DATA_OBJECT_ID_KEY = 'ayra.indiaGeo.lastDataObjectId'

export function rememberAyraLastDataObjectId(id: number): void {
  if (!Number.isNaN(id)) {
    sessionStorage.setItem(AYRA_LAST_DATA_OBJECT_ID_KEY, String(id))
  }
}
