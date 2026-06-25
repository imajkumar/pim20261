import React from 'react'
import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { IconButton } from '@pimcore/studio-ui-bundle/components'
import { componentConfig, type ComponentRegistry } from '@pimcore/studio-ui-bundle/modules/app'
import { installIndiaDependentSelectFetchPatch } from './indiaDependentSelectFetchPatch'
import { installIndiaStateSessionWatch } from './installIndiaStateSessionWatch'
import { mountIndiaGeoCityAttributeSync } from './IndiaGeoCityAttributeSync'
import { mountAyraCityFieldDrawerButton } from './mountAyraCityFieldDrawerButton'
import { AyraGoogleLoginButton } from './AyraGoogleLoginButton'
import { registerAyraObjectLayoutButton } from './registerAyraObjectLayoutButton'
import { registerAyraObjectDataButton } from './registerAyraObjectDataButton'
installIndiaDependentSelectFetchPatch()
installIndiaStateSessionWatch()
mountIndiaGeoCityAttributeSync()
mountAyraCityFieldDrawerButton()

const AyraSidebarIcon = (): React.JSX.Element => (
  <IconButton
    icon={ { value: 'new' } }
    onClick={ () => {
      window.alert('Hello from Ayra Bundle!')
    } }
    type='text'
  />
)

export const AyraStudioPlugin: IAbstractPlugin = {
  name: 'ayra-studio-plugin',

  onInit: ({ container }) => {
    registerAyraObjectLayoutButton(container)
    registerAyraObjectDataButton(container)

    const componentRegistry = container.get<ComponentRegistry>(
      serviceIds['App/ComponentRegistry/ComponentRegistry']
    )

    componentRegistry.registerToSlot(componentConfig.leftSidebar.slot.name, {
      name: 'ayra-left-sidebar-action',
      priority: 250,
      component: AyraSidebarIcon
    })

    componentRegistry.registerToSlot(componentConfig.form.login.name, {
      name: 'ayra-google-login',
      priority: 100,
      component: AyraGoogleLoginButton
    })
  }
}

if (module.hot !== undefined) {
  module.hot.accept()
}
