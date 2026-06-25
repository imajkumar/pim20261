<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\Studio\DataObject\Data\Adapter;

use Pimcore\Bundle\StudioBackendBundle\DataObject\Data\Model\FieldContextData;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Data\SetterDataInterface;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Service\DataAdapterLoaderInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Studio API adapter for {@see \Ayra\Bundle\AyraBundle\Model\DataObject\ClassDefinition\Data\Button}.
 * Ignores incoming patch values (UI-only field).
 */
#[AutoconfigureTag(DataAdapterLoaderInterface::ADAPTER_TAG)]
final readonly class ButtonDataAdapter implements SetterDataInterface
{
    public function getDataForSetter(
        Concrete $element,
        Data $fieldDefinition,
        string $key,
        array $data,
        UserInterface $user,
        ?FieldContextData $contextData = null,
        bool $isPatch = false,
    ): mixed {
        return null;
    }
}
