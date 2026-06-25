import React from 'react'
import { injectable } from 'inversify'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { Button } from '@pimcore/studio-ui-bundle/components'
import { type Container } from 'inversify'
import {
  DynamicTypeObjectLayoutAbstract,
  type DynamicTypeObjectLayoutRegistry
} from '@pimcore/studio-ui-bundle/modules/element'

type LayoutProps = Record<string, unknown> & {
  text?: string
  title?: string
  label?: string
}

function pickLabel (props: LayoutProps): string {
  const t = props.text ?? props.title ?? props.label
  return typeof t === 'string' && t.length > 0 ? t : 'Button'
}

function createButtonComponent (props: LayoutProps): React.ReactElement {
  const label = pickLabel(props)
  return (
    <Button
      block
      type='default'
      onClick={ () => {
        window.alert('hello')
      } }
    >
      {label}
    </Button>
  )
}

@injectable()
class AyraObjectLayoutButtonLower extends DynamicTypeObjectLayoutAbstract {
  readonly id = 'button'

  getObjectLayoutComponent (props: LayoutProps): React.ReactElement {
    return createButtonComponent(props)
  }
}

@injectable()
class AyraObjectLayoutButtonUpper extends DynamicTypeObjectLayoutAbstract {
  readonly id = 'Button'

  getObjectLayoutComponent (props: LayoutProps): React.ReactElement {
    return createButtonComponent(props)
  }
}

/**
 * Pimcore Studio only registers a fixed set of object layout types (panel, text, iframe, …). The classic
 * admin "Button" layout is not included, so panels that contain only a button look empty in Studio.
 * This registers minimal `button` / `Button` renderers so the control appears; click shows "hello".
 */
export function registerAyraObjectLayoutButton (container: Container): void {
  const registry = container.get<DynamicTypeObjectLayoutRegistry>(
    serviceIds['DynamicTypes/ObjectLayoutRegistry']
  )
  if (!registry.hasDynamicType('button')) {
    registry.registerDynamicType(new AyraObjectLayoutButtonLower())
  }
  if (!registry.hasDynamicType('Button')) {
    registry.registerDynamicType(new AyraObjectLayoutButtonUpper())
  }
}
