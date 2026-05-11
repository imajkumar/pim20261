import React from 'react'
import { createRoot, type Root } from 'react-dom/client'
import { AyraDrawerGridButton } from './AyraDrawerGridButton'
import { findProductsCityFormItem } from './productsFormCity'

declare global {
  interface Window {
    __ayraCityFieldDrawerMounted?: boolean
  }
}

let reactRoot: Root | null = null
let hostEl: HTMLDivElement | null = null

function clearMount(): void {
  if (reactRoot !== null) {
    reactRoot.unmount()
    reactRoot = null
  }
  if (hostEl !== null) {
    hostEl.remove()
    hostEl = null
  }
}

function tryAttach(): void {
  const cityItem = findProductsCityFormItem()
  if (cityItem === null) {
    clearMount()
    return
  }

  if (hostEl !== null && hostEl.isConnected && cityItem.contains(hostEl)) {
    return
  }

  clearMount()

  const content = cityItem.querySelector('.ant-form-item-control-input-content')
  if (content === null || content.parentElement === null) {
    return
  }

  hostEl = document.createElement('div')
  hostEl.className = 'ayra-city-drawer-host'
  hostEl.style.cssText = 'margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;'
  content.insertAdjacentElement('afterend', hostEl)

  reactRoot = createRoot(hostEl)
  reactRoot.render(<AyraDrawerGridButton />)
}

/**
 * Renders the Ayra drawer trigger **next to the City attribute** (not the left sidebar).
 */
export function mountAyraCityFieldDrawerButton(): void {
  if (typeof window === 'undefined' || window.__ayraCityFieldDrawerMounted === true) {
    return
  }
  window.__ayraCityFieldDrawerMounted = true

  const tick = (): void => {
    tryAttach()
  }

  window.setInterval(tick, 700)
  const obs = new MutationObserver(() => {
    window.requestAnimationFrame(tick)
  })
  obs.observe(document.body, { subtree: true, childList: true })

  window.setTimeout(tick, 400)
}
