<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle;

use Ayra\Bundle\AyraBundle\DependencyInjection\AyraExtension;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class AyraBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getContainerExtension(): ExtensionInterface
    {
        return new AyraExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
