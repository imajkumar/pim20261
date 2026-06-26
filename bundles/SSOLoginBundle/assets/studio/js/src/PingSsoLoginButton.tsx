import React from 'react'
import { Button } from '@pimcore/studio-ui-bundle/components'

/** Props passed by Pimcore Studio `LoginForm` into the `form.login` slot. */
export interface PingSsoLoginFormSlotProps {
  hideCredentialsForm: boolean
  onHideCredentialsForm: (hide: boolean) => void
}

const samlLoginPath = '/sso-login/saml/login'

export const PingSsoLoginButton = (_props: PingSsoLoginFormSlotProps): React.JSX.Element => (
  <div style={ { marginTop: 8, width: '100%' } }>
    <Button
      block
      onClick={ () => {
        window.location.assign(samlLoginPath)
      } }
      type='default'
    >
      Sign in with Ping SSO
    </Button>
  </div>
)
