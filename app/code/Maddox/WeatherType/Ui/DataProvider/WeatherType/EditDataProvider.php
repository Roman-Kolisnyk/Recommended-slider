<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Ui\DataProvider\WeatherType;

use Maddox\WeatherType\Model\ResourceModel\WeatherType\CollectionFactory as WeatherTypeCollection;

class EditDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param WeatherTypeCollection $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        WeatherTypeCollection $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * @return array
     */
    public function getDataSourceData(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return parent::getData();
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
