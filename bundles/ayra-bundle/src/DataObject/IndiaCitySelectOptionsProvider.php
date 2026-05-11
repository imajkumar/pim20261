<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\DataObject;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class IndiaCitySelectOptionsProvider implements SelectOptionsProviderInterface
{
    public function __construct(
        private readonly ?RequestStack $requestStack = null
    ) {
    }

    /**
     * Options provider data on the **city** field should contain the **state** field name
     * (for example `state`). Defaults to `state`.
     *
     * For Pimcore Studio, register this provider with a leading **@** and the Symfony service id
     * (see doc/SELECT_OPTIONS_INDIA.md) so RequestStack is injected and unsaved **state**
     * from the select-options API is applied immediately.
     */
    public function getOptions(array $context, Data $fieldDefinition): array
    {
        $stateField = trim((string) ($fieldDefinition->getOptionsProviderData() ?: 'state'));

        $object = $context['object'] ?? null;
        if (!$object instanceof Concrete) {
            return $this->placeholderOptions();
        }

        $stateValue = $this->resolveStateValue($object, $stateField);
        if ($stateValue === null || $stateValue === '') {
            return $this->placeholderOptions();
        }

        $cities = IndiaGeoData::cityOptionsForState($stateValue);

        return $cities !== [] ? $cities : [
            ['key' => 'No cities listed for this state (extend IndiaGeoData)', 'value' => ''],
        ];
    }

    public function hasStaticOptions(array $context, Data $fieldDefinition): bool
    {
        return false;
    }

    public function getDefaultValue(array $context, Data $fieldDefinition): string|array|null
    {
        return null;
    }

    /**
     * Prefers **unsaved** state from the Studio `select-options` POST body (`changedData`),
     * then falls back to the persisted object.
     */
    private function resolveStateValue(Concrete $object, string $stateField): ?string
    {
        $fromRequest = $this->readStateFromSelectOptionsRequest($stateField);
        if ($fromRequest !== null && $fromRequest !== '') {
            return $fromRequest;
        }

        return $this->readStringField($object, $stateField);
    }

    private function readStateFromSelectOptionsRequest(string $stateField): ?string
    {
        $request = $this->resolveCurrentRequest();
        if ($request === null || $request->getMethod() !== 'POST') {
            return null;
        }

        $path = $request->getPathInfo() . $request->getRequestUri();
        if (!str_contains($path, 'select-options')) {
            return null;
        }

        $raw = $request->getContent();
        if ($raw === '') {
            return null;
        }

        try {
            /** @var mixed $payload */
            $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        $changed = $payload['changedData'] ?? null;
        if (!is_array($changed)) {
            return null;
        }

        return $this->findStateValueInChangedData($changed, $stateField);
    }

    /**
     * Studio sends flat keys when fields sit in a fieldcontainer; some clients may nest values — walk the tree.
     *
     * @param array<string, mixed> $node
     */
    private function findStateValueInChangedData(array $node, string $stateField): ?string
    {
        if (array_key_exists($stateField, $node)) {
            $scalar = $this->extractSelectScalar($node[$stateField]);
            if ($scalar !== null && $scalar !== '') {
                return $scalar;
            }
        }

        foreach ($node as $value) {
            if (!is_array($value)) {
                continue;
            }
            $scalar = $this->findStateValueInChangedData($value, $stateField);
            if ($scalar !== null && $scalar !== '') {
                return $scalar;
            }
        }

        return null;
    }

    private function resolveCurrentRequest(): ?Request
    {
        if ($this->requestStack !== null) {
            $r = $this->requestStack->getCurrentRequest();
            if ($r instanceof Request) {
                return $r;
            }
        }

        // Pimcore often resolves option providers with `new ClassName()` (no leading `@`), so RequestStack is not
        // injected. The select-options request is still the active HTTP request — read it from the global stack.
        if (!class_exists(\Pimcore::class) || !\Pimcore::hasKernel()) {
            return null;
        }

        try {
            $container = \Pimcore::getContainer();
            if ($container === null || !$container->has('request_stack')) {
                return null;
            }
            $stack = $container->get('request_stack');
            if (!$stack instanceof RequestStack) {
                return null;
            }

            return $stack->getCurrentRequest();
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractSelectScalar(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_array($value) && array_key_exists('value', $value)) {
            $inner = $value['value'];

            return $inner === null || $inner === '' ? null : (string) $inner;
        }

        return null;
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    private function placeholderOptions(): array
    {
        return [
            ['key' => '— Select state first —', 'value' => ''],
        ];
    }

    private function readStringField(Concrete $object, string $field): ?string
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        if (!method_exists($object, $method)) {
            return null;
        }

        $value = $object->$method();
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
