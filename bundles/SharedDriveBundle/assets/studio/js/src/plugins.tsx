import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { type Container } from 'inversify'
import { registerDigitalAssetQrCodeDownloadButton } from './registerDigitalAssetQrCodeDownloadButton'

export const SharedDriveStudioPlugin: IAbstractPlugin = {
  name: 'SharedDriveBundle',

  onInit: ({ container }: { container: Container }) => {
    registerDigitalAssetQrCodeDownloadButton(container)
  }
}

if (module.hot !== undefined) {
  module.hot.accept()
}
