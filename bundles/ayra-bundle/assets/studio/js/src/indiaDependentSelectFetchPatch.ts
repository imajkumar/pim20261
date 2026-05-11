import { ayraGeoStateKey, rememberAyraLastDataObjectId } from './indiaGeoSession'
import { resolveIndiaStateSlugFromProductsForm } from './indiaStateLabelToValue'

declare global {
  interface Window {
    __ayraIndiaSelectFetchPatched?: boolean
  }
}

/**
 * Pimcore Studio embeds city options from the layout using the **saved** object. The API may send a
 * **stale** `changedData.state` or sessionStorage may lag auto-save — **the visible State field wins**.
 */
export function installIndiaDependentSelectFetchPatch(): void {
  if (typeof window === 'undefined' || window.__ayraIndiaSelectFetchPatched === true) {
    return
  }
  window.__ayraIndiaSelectFetchPatched = true

  const originalFetch = window.fetch.bind(window)

  window.fetch = async (input: RequestInfo | URL, init?: RequestInit): Promise<Response> => {
    let nextInit = init
    const url =
      typeof input === 'string'
        ? input
        : input instanceof URL
          ? input.href
          : input instanceof Request
            ? input.url
            : String(input)

    if (
      nextInit?.method === 'POST' &&
      url.includes('/pimcore-studio/api/data-objects/select-options') &&
      typeof nextInit.body === 'string'
    ) {
      try {
        const body = JSON.parse(nextInit.body) as Record<string, unknown>
        if (typeof body.objectId === 'number') {
          rememberAyraLastDataObjectId(body.objectId)
        }
        if (body.fieldName === 'city' && typeof body.objectId === 'number') {
          const oid = body.objectId
          const stored = sessionStorage.getItem(ayraGeoStateKey(oid))
          const fromDom = resolveIndiaStateSlugFromProductsForm()
          const existing =
            body.changedData !== undefined && typeof body.changedData === 'object' && body.changedData !== null
              ? (body.changedData as Record<string, unknown>)
              : {}
          const fromBody =
            typeof existing.state === 'string' && existing.state !== '' ? (existing.state as string) : null

          const stateSlug = (fromDom ?? stored ?? fromBody) || null

          if (stateSlug !== null && stateSlug !== '') {
            if (fromDom !== null && fromDom !== '') {
              sessionStorage.setItem(ayraGeoStateKey(oid), fromDom)
            }
            nextInit = {
              ...nextInit,
              body: JSON.stringify({
                ...body,
                changedData: { ...existing, state: stateSlug }
              })
            }
          }
        }
      } catch {
        /* ignore */
      }
    }

    if (
      nextInit?.method === 'PUT' &&
      url.match(/\/pimcore-studio\/api\/data-objects\/\d+(?:\?|$)/) !== null &&
      typeof nextInit.body === 'string'
    ) {
      try {
        const body = JSON.parse(nextInit.body) as Record<string, unknown>
        const m = url.match(/\/data-objects\/(\d+)/)
        const id = m !== null ? Number(m[1]) : NaN
        if (!Number.isNaN(id)) {
          rememberAyraLastDataObjectId(id)
        }
        const editable = (body.data as Record<string, unknown> | undefined)?.editableData as
          | Record<string, unknown>
          | undefined
        if (!Number.isNaN(id) && editable !== undefined && typeof editable.state === 'string' && editable.state !== '') {
          sessionStorage.setItem(ayraGeoStateKey(id), editable.state)
        }
      } catch {
        /* ignore */
      }
    }

    const response = await originalFetch(input, nextInit)

    const reqMethod =
      nextInit?.method ??
      (input instanceof Request ? input.method : 'GET')
    const upper = reqMethod.toUpperCase()

    const objectUrlMatch = url.match(/\/pimcore-studio\/api\/data-objects\/(\d+)/)
    if (objectUrlMatch !== null) {
      const oid = Number(objectUrlMatch[1])
      if (!Number.isNaN(oid)) {
        rememberAyraLastDataObjectId(oid)
      }
    }

    if (
      url.match(/\/pimcore-studio\/api\/data-objects\/\d+(?:\?|$)/) !== null &&
      (upper === 'PUT' || upper === 'GET') &&
      response.ok
    ) {
      const ct = response.headers.get('content-type') ?? ''
      if (ct.includes('application/json')) {
        const clone = response.clone()
        void clone
          .json()
          .then((json: unknown) => {
            if (json === null || typeof json !== 'object') {
              return
            }
            const j = json as Record<string, unknown>
            const id = j.id
            const od = j.objectData as Record<string, unknown> | undefined
            if (typeof id === 'number') {
              rememberAyraLastDataObjectId(id)
            }
            if (typeof id === 'number' && od !== undefined && typeof od.state === 'string' && od.state !== '') {
              sessionStorage.setItem(ayraGeoStateKey(id), od.state)
            }
          })
          .catch(() => {})
      }
    }

    return response
  }
}
