<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model\ResourceModel\WeatherType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \Maddox\WeatherType\Model\WeatherType::class,
            \Maddox\WeatherType\Model\ResourceModel\WeatherType::class
        );
    }
}
