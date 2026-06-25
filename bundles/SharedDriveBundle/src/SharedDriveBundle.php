<?php
declare(strict_types=1);

namespace SharedDrive\Bundle\SharedDriveBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use SharedDrive\Bundle\SharedDriveBundle\DependencyInjection\SharedDriveExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class SharedDriveBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getContainerExtension(): ExtensionInterface
    {
        return new SharedDriveExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
