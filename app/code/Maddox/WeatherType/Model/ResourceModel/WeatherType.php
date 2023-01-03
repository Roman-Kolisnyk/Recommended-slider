<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model\ResourceModel;

class WeatherType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('maddox_weather_type', 'entity_id');
    }
}
