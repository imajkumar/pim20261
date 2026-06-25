<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\DependencyInjection;

use Ayra\Bundle\AyraBundle\Model\DataObject\ClassDefinition\Data\Button as AyraButtonDataType;
use Ayra\Bundle\AyraBundle\Studio\DataObject\Data\Adapter\ButtonDataAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class AyraExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('pimcore', [
            'objects' => [
                'class_definitions' => [
                    'data' => [
                        'map' => [
                            'button' => AyraButtonDataType::class,
                        ],
                    ],
                ],
            ],
        ]);

        $container->prependExtensionConfig('pimcore_studio_backend', [
            'data_object_data_adapter_mapping' => [
                ButtonDataAdapter::class => [
                    'button',
                ],
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
    }
}
