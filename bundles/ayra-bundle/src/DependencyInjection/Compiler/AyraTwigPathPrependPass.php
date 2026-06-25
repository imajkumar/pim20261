<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers Ayra Twig templates after core paths so {@code addPath} prepends them first in the search stack
 * (Twig prepends each path — last registration wins for resolution order).
 */
final class AyraTwigPathPrependPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('twig.loader.native_filesystem')) {
            return;
        }

        $bundleTemplates = dirname(__DIR__, 3) . '/templates';
        if (!is_dir($bundleTemplates)) {
            return;
        }

        $container->getDefinition('twig.loader.native_filesystem')
            ->addMethodCall('addPath', [$bundleTemplates]);
    }
}
