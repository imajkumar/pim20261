<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use SSOLogin\Bundle\SSOLoginBundle\DependencyInjection\SSOLoginExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class SSOLoginBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getContainerExtension(): ExtensionInterface
    {
        return new SSOLoginExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
