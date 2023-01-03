<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Maddox\WeatherType\Model\WeatherTypeFactory;
use Maddox\WeatherType\Api\WeatherTypeInterface;
use Maddox\WeatherType\Model\ResourceModel\WeatherType as WeatherTypeResource;

class InstallDefaultWeatherTypes implements DataPatchInterface
{
    /**
     * @param WeatherTypeFactory $weatherTypeFactory
     * @param WeatherTypeResource $weatherTypeResource
     */
    public function __construct(
        private readonly WeatherTypeFactory $weatherTypeFactory,
        private readonly WeatherTypeResource $weatherTypeResource
    ) {}

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function apply(): void
    {
        $defaultWeatherTypes = [
            [
                WeatherTypeInterface::LABEL => 'cold',
                WeatherTypeInterface::MINIMUM_TEMPERATURE_VALUE => -50,
                WeatherTypeInterface::MAXIMUM_TEMPERATURE_VALUE => 4
            ],
            [
                WeatherTypeInterface::LABEL => 'cool',
                WeatherTypeInterface::MINIMUM_TEMPERATURE_VALUE => 5,
                WeatherTypeInterface::MAXIMUM_TEMPERATURE_VALUE => 14
            ],
            [
                WeatherTypeInterface::LABEL => 'warm',
                WeatherTypeInterface::MINIMUM_TEMPERATURE_VALUE => 15,
                WeatherTypeInterface::MAXIMUM_TEMPERATURE_VALUE => 24
            ],
            [
                WeatherTypeInterface::LABEL => 'hot',
                WeatherTypeInterface::MINIMUM_TEMPERATURE_VALUE => 25,
                WeatherTypeInterface::MAXIMUM_TEMPERATURE_VALUE => 50
            ]
        ];

        foreach ($defaultWeatherTypes as $defaultWeatherType) {
            $insertWeatherType = $this->weatherTypeFactory->create()->setData($defaultWeatherType);
            $this->weatherTypeResource->save($insertWeatherType);
        }
    }
}
