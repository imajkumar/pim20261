<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\Model\DataObject\ClassDefinition\Data;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\EqualComparisonInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\ResourcePersistenceAwareInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\TypeDeclarationSupportInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\VarExporterInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Traits\SimpleNormalizerTrait;
use Pimcore\Normalizer\NormalizerInterface;

/**
 * UI-only field: no meaningful persisted value (column exists as varchar(1), always null).
 * Use for actions in forms; map key and {@see getFieldType()} must be <code>button</code>.
 */
final class Button extends Data implements
    ResourcePersistenceAwareInterface,
    QueryResourcePersistenceAwareInterface,
    TypeDeclarationSupportInterface,
    EqualComparisonInterface,
    VarExporterInterface,
    NormalizerInterface
{
    use SimpleNormalizerTrait;

    public function getFieldType(): string
    {
        return 'button';
    }

    public function getDataForResource(mixed $data, ?Concrete $object = null, array $params = []): ?string
    {
        return null;
    }

    public function getDataFromResource(mixed $data, ?Concrete $object = null, array $params = []): ?string
    {
        return null;
    }

    public function getDataForQueryResource(mixed $data, ?Concrete $object = null, array $params = []): ?string
    {
        return null;
    }

    public function getDataForEditmode(mixed $data, ?Concrete $object = null, array $params = []): mixed
    {
        return null;
    }

    public function getDataFromEditmode(mixed $data, ?Concrete $object = null, array $params = []): mixed
    {
        return null;
    }

    public function getColumnType(): string
    {
        return 'varchar(1)';
    }

    public function getQueryColumnType(): string
    {
        return 'varchar(1)';
    }

    public function isEqual(mixed $oldValue, mixed $newValue): bool
    {
        return true;
    }

    public function getVersionPreview(mixed $data, ?Concrete $object = null, array $params = []): string
    {
        return '';
    }

    public function checkValidity(mixed $data, bool $omitMandatoryCheck = false, array $params = []): void
    {
    }

    public function isEmpty(mixed $data): bool
    {
        return true;
    }

    public function getParameterTypeDeclaration(): ?string
    {
        return '?string';
    }

    public function getReturnTypeDeclaration(): ?string
    {
        return '?string';
    }

    public function getPhpdocInputType(): ?string
    {
        return 'string|null';
    }

    public function getPhpdocReturnType(): ?string
    {
        return 'string|null';
    }

    public function isFilterable(): bool
    {
        return false;
    }
}
