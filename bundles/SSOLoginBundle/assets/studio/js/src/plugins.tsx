import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { componentConfig, type ComponentRegistry } from '@pimcore/studio-ui-bundle/modules/app'
import { type Container } from 'inversify'
import { PingSsoLoginButton } from './PingSsoLoginButton'

export const SSOLoginStudioPlugin: IAbstractPlugin = {
  name: 'SSOLoginBundle',

  onInit: ({ container }: { container: Container }) => {
    const componentRegistry = container.get<ComponentRegistry>(
      serviceIds['App/ComponentRegistry/ComponentRegistry']
    )

    componentRegistry.registerToSlot(componentConfig.form.login.name, {
      name: 'sso-login-ping-saml',
      priority: 100,
      component: PingSsoLoginButton
    })
  }
}

if (module.hot !== undefined) {
  module.hot.accept()
}
