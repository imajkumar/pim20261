import { getActiveDataObjectId } from './getActiveDataObjectId'
import { setAyraIndiaGeoState } from './indiaGeoSession'
import { resolveIndiaStateSlugFromProductsForm } from './indiaStateLabelToValue'

declare global {
  interface Window {
    __ayraIndiaStateSessionWatchInstalled?: boolean
  }
}

/**
 * Keeps `sessionStorage` state slug aligned with the **visible** State control (poll + click).
 * Different from inferring only from select-options timing — avoids City requests using an old slug
 * while the UI already shows another state.
 */
export function installIndiaStateSessionWatch(): void {
  if (typeof window === 'undefined' || window.__ayraIndiaStateSessionWatchInstalled === true) {
    return
  }
  window.__ayraIndiaStateSessionWatchInstalled = true

  let lastKey = ''

  const sync = (): void => {
    const slug = resolveIndiaStateSlugFromProductsForm()
    const oid = getActiveDataObjectId()
    if (oid === null || slug === null || slug === '') {
      lastKey = ''
      return
    }
    const key = `${oid}:${slug}`
    if (key === lastKey) {
      return
    }
    lastKey = key
    setAyraIndiaGeoState(oid, slug)
  }

  window.setInterval(sync, 400)

  document.addEventListener('click', () => window.setTimeout(sync, 0), true)
  document.addEventListener('keydown', () => window.setTimeout(sync, 0), true)

  window.setTimeout(sync, 800)
}
