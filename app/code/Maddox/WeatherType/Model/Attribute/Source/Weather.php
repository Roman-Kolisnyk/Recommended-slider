<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model\Attribute\Source;

use Maddox\WeatherType\Model\ResourceModel\WeatherType\CollectionFactory as WeatherTypeCollectionFactory;
use Maddox\WeatherType\Model\ResourceModel\WeatherType\Collection as WeatherTypeCollection;

class Weather extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @param WeatherTypeCollectionFactory $weatherTypeCollection
     */
    public function __construct(
        private readonly WeatherTypeCollectionFactory $weatherTypeCollection
    ) {}

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        $this->_options[] = ['label' => __('None'), 'value' => 'default'];

        $weatherOptions = $this->getWeatherTypeOptions();

        if ($weatherOptions->getSize()) {
            /** @var \Maddox\WeatherType\Model\WeatherType $weatherOption */
            foreach ($weatherOptions as $weatherOption) {
                $weatherTypeLabel = $weatherOption->getLabel();
                $this->_options[] = [
                    'label' => __(ucfirst($weatherTypeLabel)),
                    'value' => $weatherTypeLabel
                ];
            }
        }

        return $this->_options;
    }

    /**
     * @return WeatherTypeCollection
     */
    private function getWeatherTypeOptions(): WeatherTypeCollection
    {
        return $this->weatherTypeCollection->create()
            ->addFieldToSelect('label');
    }
}
