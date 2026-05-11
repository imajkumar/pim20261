/**
 * Display labels → option values for Indian states (must stay aligned with `IndiaGeoData::STATES` in PHP).
 */
export const INDIA_STATE_LABEL_TO_VALUE: Array<{ key: string; value: string }> = [
  { key: 'Andhra Pradesh', value: 'andhra_pradesh' },
  { key: 'Arunachal Pradesh', value: 'arunachal_pradesh' },
  { key: 'Assam', value: 'assam' },
  { key: 'Bihar', value: 'bihar' },
  { key: 'Chhattisgarh', value: 'chhattisgarh' },
  { key: 'Goa', value: 'goa' },
  { key: 'Gujarat', value: 'gujarat' },
  { key: 'Haryana', value: 'haryana' },
  { key: 'Himachal Pradesh', value: 'himachal_pradesh' },
  { key: 'Jharkhand', value: 'jharkhand' },
  { key: 'Karnataka', value: 'karnataka' },
  { key: 'Kerala', value: 'kerala' },
  { key: 'Madhya Pradesh', value: 'madhya_pradesh' },
  { key: 'Maharashtra', value: 'maharashtra' },
  { key: 'Manipur', value: 'manipur' },
  { key: 'Meghalaya', value: 'meghalaya' },
  { key: 'Mizoram', value: 'mizoram' },
  { key: 'Nagaland', value: 'nagaland' },
  { key: 'Odisha', value: 'odisha' },
  { key: 'Punjab', value: 'punjab' },
  { key: 'Rajasthan', value: 'rajasthan' },
  { key: 'Sikkim', value: 'sikkim' },
  { key: 'Tamil Nadu', value: 'tamil_nadu' },
  { key: 'Telangana', value: 'telangana' },
  { key: 'Tripura', value: 'tripura' },
  { key: 'Uttar Pradesh', value: 'uttar_pradesh' },
  { key: 'Uttarakhand', value: 'uttarakhand' },
  { key: 'West Bengal', value: 'west_bengal' },
  { key: 'Andaman and Nicobar Islands', value: 'andaman_nicobar' },
  { key: 'Chandigarh', value: 'chandigarh' },
  { key: 'Dadra and Nagar Haveli and Daman and Diu', value: 'dadra_nagar_haveli_daman_diu' },
  { key: 'Delhi', value: 'delhi' },
  { key: 'Jammu and Kashmir', value: 'jammu_kashmir' },
  { key: 'Ladakh', value: 'ladakh' },
  { key: 'Lakshadweep', value: 'lakshadweep' },
  { key: 'Puducherry', value: 'puducherry' }
]

export function resolveIndiaStateSlugFromProductsForm(): string | null {
  const labels = document.querySelectorAll('.ant-form-item-label label')
  for (const label of labels) {
    const text = (label.textContent ?? '').replace(/\s+/g, ' ').trim().replace(/\s*\*\s*$/, '')
    if (!/^state$/i.test(text)) {
      continue
    }
    const item = label.closest('.ant-form-item')
    if (item === null) {
      continue
    }
    const selectionItem = item.querySelector('.ant-select-selection-item')
    const title = selectionItem?.getAttribute('title')?.trim()
    const shown = title ?? selectionItem?.textContent?.trim()
    if (shown === undefined || shown === '') {
      continue
    }
    const row = INDIA_STATE_LABEL_TO_VALUE.find(
      (s) => s.key === shown || s.key.toLowerCase() === shown.toLowerCase()
    )
    if (row !== undefined) {
      return row.value
    }
  }

  // Fallback: Products layout is state (left) + city (right) — read the select in the row before City.
  const cityLabels = document.querySelectorAll('.ant-form-item-label label')
  for (const label of cityLabels) {
    const t = (label.textContent ?? '').replace(/\s+/g, ' ').trim().replace(/\s*\*\s*$/, '')
    if (!/^city$/i.test(t)) {
      continue
    }
    const cityItem = label.closest('.ant-form-item')
    if (cityItem === null) {
      continue
    }
    let prev = cityItem.previousElementSibling
    while (prev !== null && !prev.classList.contains('ant-form-item')) {
      prev = prev.previousElementSibling
    }
    if (prev === null) {
      continue
    }
    const selectionItem = prev.querySelector('.ant-select-selection-item')
    const title = selectionItem?.getAttribute('title')?.trim()
    const shown = title ?? selectionItem?.textContent?.trim()
    if (shown === undefined || shown === '') {
      continue
    }
    const row = INDIA_STATE_LABEL_TO_VALUE.find(
      (s) => s.key === shown || s.key.toLowerCase() === shown.toLowerCase()
    )
    if (row !== undefined) {
      return row.value
    }
  }

  return null
}
