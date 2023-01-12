<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Api;

interface WeatherTypeInterface
{
    public const ENTITY_ID = 'entity_id';

    public const LABEL = 'label';

    public const MINIMUM_TEMPERATURE_VALUE = 'minimum_temperature_value';

    public const MAXIMUM_TEMPERATURE_VALUE = 'maximum_temperature_value';

    /**
     * @return string
     */
    public function getEntityId(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return WeatherTypeInterface
     */
    public function setLabel(string $label): WeatherTypeInterface;

    /**
     * @return int|string
     */
    public function getMinimumTemperatureValue(): int|string;

    /**
     * @param int $minimumTemperatureValue
     * @return WeatherTypeInterface
     */
    public function setMinimumTemperatureValue(int $minimumTemperatureValue): WeatherTypeInterface;

    /**
     * @return int|string
     */
    public function getMaximumTemperatureValue(): int|string;

    /**
     * @param int $maximumTemperatureValue
     * @return WeatherTypeInterface
     */
    public function setMaximumTemperatureValue(int $maximumTemperatureValue): WeatherTypeInterface;
}
