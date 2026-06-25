import React from 'react'
import { Button } from '@pimcore/studio-ui-bundle/components'
import { Divider } from 'antd'

/** Props passed by Pimcore Studio `LoginForm` into the `form.login` slot. */
export interface AyraLoginFormSlotProps {
  hideCredentialsForm: boolean
  onHideCredentialsForm: (hide: boolean) => void
}

const googleConnectPath = '/ayra/oauth/google/connect'

export const AyraGoogleLoginButton = (_props: AyraLoginFormSlotProps): React.JSX.Element => (
  <div style={ { marginTop: 8, width: '100%' } }>
   
    <Button
      block
      onClick={ () => {
        window.location.assign(googleConnectPath)
      } }
      type='default'
    >
      Continue with Google
    </Button>
  </div>
)
