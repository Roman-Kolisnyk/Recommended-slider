<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Api;

interface PostRepositoryInterface
{
    /**
     * @param string $cityName
     * @param int $pageSize
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProductList(string $cityName, int $pageSize): array;
}
