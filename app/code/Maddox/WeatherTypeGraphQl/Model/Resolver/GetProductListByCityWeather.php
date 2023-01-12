<?php

declare(strict_types=1);

namespace Maddox\WeatherTypeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Maddox\WeatherType\Model\PostRepository as ProductsByWeather;

class GetProductListByCityWeather implements ResolverInterface
{
    /**
     * @param ProductsByWeather $productsByWeather
     */
    public function __construct(
        private readonly ProductsByWeather $productsByWeather
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
        if (!empty($args['input']['cityName']) && !empty($args['input']['pageSize'])) {
            $cityName = $args['input']['cityName'];
            $pageSize = $args['input']['pageSize'];
            $productList = $this->productsByWeather->getProductList($cityName, $pageSize);
        }

        return $productList ?? ['Invalid requested arguments'];
    }
}
