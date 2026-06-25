import React from 'react'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { Button, Flex } from '@pimcore/studio-ui-bundle/components'
import { type Container } from 'inversify'
import {
  type AbstractObjectDataDefinition,
  DynamicTypeObjectDataAbstract,
  type DynamicTypeObjectDataRegistry
} from '@pimcore/studio-ui-bundle/modules/element'

export class AyraObjectDataButton extends DynamicTypeObjectDataAbstract {
  id: string = 'button'
  isAllowedInBatchEdit: boolean = false

  getObjectDataComponent (props: AbstractObjectDataDefinition): React.ReactElement<AbstractObjectDataDefinition> {
    const label = props.title ?? 'Button'

    return (
      <Flex
        className={ props.className }
        gap='extra-small'
      >
        <Button
          block
          disabled={ props.noteditable === true }
          type='default'
          onClick={ () => {
            window.alert('hello')
          } }
        >
          {label}
        </Button>
      </Flex>
    )
  }
}

export function registerAyraObjectDataButton (container: Container): void {
  const registry = container.get<DynamicTypeObjectDataRegistry>(
    serviceIds['DynamicTypes/ObjectDataRegistry']
  )
  if (!registry.hasDynamicType('button')) {
    registry.registerDynamicType(new AyraObjectDataButton())
  }
}
