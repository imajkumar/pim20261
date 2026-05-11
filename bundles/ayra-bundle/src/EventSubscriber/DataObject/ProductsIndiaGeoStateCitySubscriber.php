<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\EventSubscriber\DataObject;

use Ayra\Bundle\AyraBundle\DataObject\IndiaGeoData;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Keeps **city** consistent with **state**:
 * - No state → clear city.
 * - State set but city missing or not in that state’s list → set city to the **first** city for that state
 *   (so auto-save after changing state is not stuck with `city: null` when you expect a Goa/Bihar option).
 * - State set and city already valid → unchanged.
 */
final class ProductsIndiaGeoStateCitySubscriber implements EventSubscriberInterface
{
    private const PRODUCTS_CLASS_NAME = 'Products';

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_UPDATE => 'normalizeCityForState',
            DataObjectEvents::PRE_ADD => 'normalizeCityForState',
        ];
    }

    public function normalizeCityForState(DataObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof Concrete || $object->getClassName() !== self::PRODUCTS_CLASS_NAME) {
            return;
        }

        if (!method_exists($object, 'getState') || !method_exists($object, 'getCity') || !method_exists($object, 'setCity')) {
            return;
        }

        $state = $object->getState();
        if ($state === null || $state === '') {
            $object->setCity(null);

            return;
        }

        $stateStr = (string) $state;
        $city = $object->getCity();
        $cityStr = $city !== null && $city !== '' ? (string) $city : '';

        $hasValidCity = $cityStr !== ''
            && IndiaGeoData::isCityValueAllowedForState($cityStr, $stateStr);

        if ($hasValidCity) {
            return;
        }

        $object->setCity(IndiaGeoData::firstCityValueForState($stateStr));
    }
}
