<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model;

use Maddox\WeatherType\Api\WeatherTypeInterface;

class WeatherType extends \Magento\Framework\Model\AbstractModel implements WeatherTypeInterface
{
    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Maddox\WeatherType\Model\ResourceModel\WeatherType::class);
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->_getData(self::ENTITY_ID);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->_getData(self::LABEL);
    }

    /**
     * @param string $label
     * @return WeatherTypeInterface
     */
    public function setLabel(string $label): WeatherTypeInterface
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @return int|string
     */
    public function getMinimumTemperatureValue(): int|string
    {
        return $this->_getData(self::MINIMUM_TEMPERATURE_VALUE);
    }

    /**
     * @param int $minimumTemperatureValue
     * @return WeatherTypeInterface
     */
    public function setMinimumTemperatureValue(int $minimumTemperatureValue): WeatherTypeInterface
    {
        return $this->setData(self::MINIMUM_TEMPERATURE_VALUE, $minimumTemperatureValue);
    }

    /**
     * @return int|string
     */
    public function getMaximumTemperatureValue(): int|string
    {
        return $this->_getData(self::MAXIMUM_TEMPERATURE_VALUE);
    }

    /**
     * @param int $maximumTemperatureValue
     * @return WeatherTypeInterface
     */
    public function setMaximumTemperatureValue(int $maximumTemperatureValue): WeatherTypeInterface
    {
        return $this->setData(self::MAXIMUM_TEMPERATURE_VALUE, $maximumTemperatureValue);
    }
}
