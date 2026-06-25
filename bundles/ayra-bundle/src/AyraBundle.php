<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle;

use Ayra\Bundle\AyraBundle\DependencyInjection\AyraExtension;
use Ayra\Bundle\AyraBundle\DependencyInjection\Compiler\AyraTwigPathPrependPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class AyraBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new AyraTwigPathPrependPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 200);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new AyraExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
