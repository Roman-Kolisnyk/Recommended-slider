<?php

declare(strict_types=1);

namespace Maddox\WeatherTypeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Maddox\WeatherType\Model\ResourceModel\WeatherType\CollectionFactory as WeatherTypeCollectionFactory;
use Maddox\WeatherType\Model\WeatherType;

class GetWeatherTypes implements ResolverInterface
{
    /**
     * @param WeatherTypeCollectionFactory $weatherTypeCollectionFactory
     */
    public function __construct(
        private readonly WeatherTypeCollectionFactory $weatherTypeCollectionFactory
    ) {}

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $weatherTypeCollection = $this->weatherTypeCollectionFactory->create();
        $weatherTypes = [];

        if (!$weatherTypeCollection->getSize()) {
            return $weatherTypes;
        }

        /** @var WeatherType $weatherType */
        foreach ($weatherTypeCollection->getItems() as $weatherType) {
            $weatherTypes[] = [
                'id' => $weatherType->getEntityId(),
                'label' => $weatherType->getLabel(),
                'minimum_temperature_value' => (int) $weatherType->getMinimumTemperatureValue(),
                'maximum_temperature_value' => (int) $weatherType->getMaximumTemperatureValue()
            ];
        }

        return $weatherTypes;
    }
}
