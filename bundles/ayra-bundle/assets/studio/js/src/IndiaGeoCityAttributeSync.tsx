import React, { useEffect } from 'react'
import { createRoot } from 'react-dom/client'
import { Provider } from 'react-redux'
import { indiaGeoStore } from './store/indiaGeoStore'
import { useLazyCityOptionsForStateQuery } from './store/indiaGeoApi'
import { resolveIndiaStateSlugFromProductsForm } from './indiaStateLabelToValue'
import { getActiveDataObjectId } from './getActiveDataObjectId'
import { setAyraIndiaGeoState } from './indiaGeoSession'

declare global {
  interface Window {
    __ayraIndiaGeoCityAttributeSyncMounted?: boolean
  }
}

/**
 * Redux + RTK Query prefetch for city **select-options**; syncs sessionStorage for the fetch patch.
 * After a successful fetch, opens the **City** attribute select so options appear on the real field
 * (no separate Alert panel).
 */
function IndiaGeoCityAttributeSyncInner(): null {
  const [fetchCities] = useLazyCityOptionsForStateQuery()

  useEffect(() => {
    let last = resolveIndiaStateSlugFromProductsForm()
    const baselineTimer = window.setTimeout(() => {
      last = resolveIndiaStateSlugFromProductsForm()
    }, 600)

    const onClick = (e: MouseEvent): void => {
      const target = e.target as HTMLElement | null
      if (target === null || target.closest('.ant-select-item-option') === null) {
        return
      }
      window.setTimeout(() => {
        const slug = resolveIndiaStateSlugFromProductsForm()
        if (slug === null || slug === '' || slug === last) {
          return
        }
        last = slug
        const objectId = getActiveDataObjectId()
        if (objectId === null) {
          return
        }
        setAyraIndiaGeoState(objectId, slug)

        void fetchCities({ objectId, stateSlug: slug }).catch(() => {})
      }, 0)
    }

    document.addEventListener('click', onClick, true)
    return () => {
      window.clearTimeout(baselineTimer)
      document.removeEventListener('click', onClick, true)
    }
  }, [fetchCities])

  return null
}

export function mountIndiaGeoCityAttributeSync(): void {
  if (typeof window === 'undefined' || window.__ayraIndiaGeoCityAttributeSyncMounted === true) {
    return
  }
  window.__ayraIndiaGeoCityAttributeSyncMounted = true

  const el = document.createElement('div')
  el.id = 'ayra-india-geo-city-attribute-sync-root'
  document.body.appendChild(el)
  createRoot(el).render(
    <Provider store={ indiaGeoStore }>
      <IndiaGeoCityAttributeSyncInner />
    </Provider>
  )
}
