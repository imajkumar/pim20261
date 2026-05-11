/** Resolves the **City** field’s `.ant-form-item` in the current Studio form (label text “City”). */
export function findProductsCityFormItem(): HTMLElement | null {
  const cityLabels = document.querySelectorAll('.ant-form-item-label label')
  for (const label of cityLabels) {
    const t = (label.textContent ?? '').replace(/\s+/g, ' ').trim().replace(/\s*\*\s*$/, '')
    if (!/^city$/i.test(t)) {
      continue
    }
    const cityItem = label.closest('.ant-form-item')
    if (cityItem instanceof HTMLElement) {
      return cityItem
    }
  }
  return null
}
