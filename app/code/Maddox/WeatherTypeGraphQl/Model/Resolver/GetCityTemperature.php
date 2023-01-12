<?php

declare(strict_types=1);

namespace Maddox\WeatherTypeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Maddox\WeatherType\Model\ForecastService\Client as ForecastService;

class GetCityTemperature implements ResolverInterface
{
    /**
     * @param ForecastService $forecastService
     */
    public function __construct(
        private readonly ForecastService $forecastService
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
        if (!empty($args['cityName'])) {
            try {
                $cityTemperature = $this->forecastService->getCurrentCityTemperature($args['cityName']);
            } catch (\InvalidArgumentException $e) {
                $errorMessage = $e->getMessage();
            }
        }

        return [
            'temperature' => $cityTemperature ?? null,
            'errorMessage' => $errorMessage ?? null
        ];
    }
}
