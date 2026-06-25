import React, { useEffect, useRef } from 'react'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { useDownload } from '@pimcore/studio-ui-bundle/modules/asset'
import { type Container } from 'inversify'
import {
  type AbstractDocumentEditableDefinition,
  type AbstractObjectDataDefinition,
  DynamicTypeDocumentEditableAbstract,
  DynamicTypeObjectDataAbstract,
  type DynamicTypeDocumentEditableRegistry,
  type DynamicTypeObjectDataRegistry
} from '@pimcore/studio-ui-bundle/modules/element'

const FLAG = '__sharedDriveQrImage'
const IMAGE_TYPE = 'image'
const QR_FIELD = 'qrcode'
const TOOLTIP = 'Download QR code'
const DOWNLOAD_ICON = (
  '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">'
  + '<path d="M14 14H2M12 7.33333L8 11.3333M8 11.3333L4 7.33333M8 11.3333V2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
  + '</svg>'
)

const isQrField = (name: unknown): boolean => {
  const field = Array.isArray(name) ? name[name.length - 1] : name
  return String(field ?? '').toLowerCase() === QR_FIELD
}

const getAssetId = (value: unknown): number | undefined => {
  if (value == null || typeof value !== 'object' || !('id' in value)) {
    return undefined
  }
  const id = (value as { id?: unknown }).id
  return typeof id === 'number' ? id : undefined
}

/** Replace ⋯ with download; hide open/delete on object image footers. */
const patchDownloadButton = (
  root: HTMLElement,
  onDownload: () => void,
  enabled: boolean
): void => {
  const footer = Array.from(root.querySelectorAll('.ant-btn-group button'))
  footer.slice(1, 3).forEach((btn) => {
    const wrap = btn.closest('span') ?? btn.parentElement
    if (wrap instanceof HTMLElement) {
      wrap.style.display = 'none'
    }
  })

  const more = footer[footer.length - 1]
    ?? root.querySelector('.dropdown-menu__icon')?.closest('button')
    ?? root.querySelector('.studio-image-editable button.ant-btn-icon-only')

  if (!(more instanceof HTMLButtonElement)) {
    return
  }

  if (more.dataset.sharedDriveQr !== '1') {
    more.dataset.sharedDriveQr = '1'
    more.title = TOOLTIP
    more.setAttribute('aria-label', TOOLTIP)
    const icon = more.querySelector('svg')
    if (icon != null) {
      icon.outerHTML = DOWNLOAD_ICON
    } else {
      more.innerHTML = DOWNLOAD_ICON
    }
    more.addEventListener('click', (e) => {
      e.preventDefault()
      e.stopImmediatePropagation()
      onDownload()
    }, true)
  }

  more.toggleAttribute('disabled', !enabled)
  more.classList.toggle('ant-btn-disabled', !enabled)
  more.style.pointerEvents = enabled ? 'auto' : 'none'
}

const QrCodeImageWrapper = ({
  children,
  value,
  ...rest
}: {
  children: React.ReactElement
  value?: unknown
  [key: string]: unknown
}): React.JSX.Element => {
  const rootRef = useRef<HTMLDivElement>(null)
  const { download } = useDownload()
  const downloadRef = useRef(download)
  const onDownloadRef = useRef<() => void>(() => {})

  downloadRef.current = download
  const assetId = getAssetId(value)
  const hasAsset = assetId != null

  onDownloadRef.current = () => {
    if (hasAsset) {
      downloadRef.current(String(assetId))
    }
  }

  useEffect(() => {
    const root = rootRef.current
    if (root == null) {
      return
    }

    const apply = (): void => {
      patchDownloadButton(root, () => { onDownloadRef.current() }, hasAsset)
    }

    apply()
    const timer = window.setTimeout(apply, 200)
    return () => { window.clearTimeout(timer) }
  }, [hasAsset, assetId])

  return (
    <div ref={ rootRef } style={ { width: '100%' } }>
      {React.cloneElement(children, { value, ...rest })}
    </div>
  )
}

const wrapQrImage = (element: React.ReactElement, name: unknown): React.ReactElement =>
  isQrField(name) ? <QrCodeImageWrapper>{element}</QrCodeImageWrapper> : element

function delegateMethods (target: object, source: object, methods: readonly string[]): void {
  for (const method of methods) {
    const fn = (source as any)[method]
    if (typeof fn === 'function') {
      ;(target as any)[method] = fn.bind(source)
    }
  }
}

function overrideObjectImage (original: DynamicTypeObjectDataAbstract): DynamicTypeObjectDataAbstract {
  const override = new class extends DynamicTypeObjectDataAbstract {
    readonly id = IMAGE_TYPE

    getObjectDataComponent (props: AbstractObjectDataDefinition): React.ReactElement {
      return wrapQrImage(original.getObjectDataComponent(props), props.name)
    }
  }()

  override.inheritedMaskOverlay = (original as any).inheritedMaskOverlay
  override.gridCellEditMode = (original as any).gridCellEditMode
  override.gridCellEditModalSettings = (original as any).gridCellEditModalSettings
  delegateMethods(override, original, ['getGridCellPreviewComponent', 'getDefaultGridColumnWidth'])

  return override
}

function overrideDocumentImage (original: DynamicTypeDocumentEditableAbstract): DynamicTypeDocumentEditableAbstract {
  const override = new class extends DynamicTypeDocumentEditableAbstract {
    readonly id = IMAGE_TYPE

    getEditableDataComponent (props: AbstractDocumentEditableDefinition): React.ReactElement {
      return wrapQrImage(original.getEditableDataComponent(props), props.name)
    }
  }()

  delegateMethods(override, original, [
    'transformValue',
    'transformValueForApi',
    'isEmpty',
    'reloadOnChange'
  ])

  return override
}

function installImageOverride (
  registry: DynamicTypeObjectDataRegistry | DynamicTypeDocumentEditableRegistry,
  build: (original: any) => any
): void {
  const wrap = (original: any): any => {
    const wrapped = build(original)
    wrapped[FLAG] = true
    wrapped.__original = original
    return wrapped
  }

  const map = (registry as any).dynamicTypes as Map<string, any> | undefined
  if (map != null) {
    const set = map.set.bind(map)
    map.set = (key: string, value: any) =>
      key === IMAGE_TYPE && value[FLAG] !== true ? set(key, wrap(value)) : set(key, value)
  }

  const install = (): boolean => {
    if (!registry.hasDynamicType(IMAGE_TYPE)) {
      return false
    }
    const current = registry.getDynamicType(IMAGE_TYPE) as any
    if (current[FLAG] === true) {
      return true
    }
    registry.overrideDynamicType(wrap(current.__original ?? current))
    return true
  }

  if (!install()) {
    window.setTimeout(install, 0)
  }
}

export function registerDigitalAssetQrCodeDownloadButton (container: Container): void {
  const pairs: Array<[
    keyof typeof serviceIds,
    (original: any) => any
  ]> = [
    ['DynamicTypes/ObjectDataRegistry', overrideObjectImage],
    ['DynamicTypes/DocumentEditableRegistry', overrideDocumentImage]
  ]

  for (const [serviceId, build] of pairs) {
    installImageOverride(container.get(serviceIds[serviceId]), build)
  }
}
