<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle\Webpack;

use Pimcore\Bundle\StudioUiBundle\Webpack\WebpackEntryPointProviderInterface;

/**
 * @internal
 */
if (interface_exists(WebpackEntryPointProviderInterface::class)) {
    final class WebpackEntryPointProvider implements WebpackEntryPointProviderInterface
    {
        public function getEntryPointsJsonLocations(): array
        {
            return glob(__DIR__ . '/../../public/studio/build/*/entrypoints.json') ?: [];
        }

        public function getEntryPoints(): array
        {
            return ['exposeRemote'];
        }

        public function getOptionalEntryPoints(): array
        {
            return [];
        }
    }
}
