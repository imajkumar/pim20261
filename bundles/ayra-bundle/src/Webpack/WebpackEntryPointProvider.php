<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\Webpack;

use Pimcore\Bundle\StudioUiBundle\Webpack\WebpackEntryPointProviderInterface;

/**
 * Points Pimcore Studio at generated entrypoints.json files under public/studio/build.
 *
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
