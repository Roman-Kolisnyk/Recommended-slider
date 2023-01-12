<?php

declare(strict_types=1);

namespace Maddox\WeatherTypeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Maddox\WeatherType\Model\ForecastService\Client as ForecastService;

class GetForecast implements ResolverInterface
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
        if (!empty($args['cityName']) && $this->validateRequestParams($args['cityName'])) {
            $forecast = $this->forecastService->getForecastInfo($args['cityName']);
        }

        return $forecast ?? ['message' => 'Invalid requested arguments'];
    }

    /**
     * @param string $cityName
     * @return bool
     */
    private function validateRequestParams(string $cityName): bool
    {
        $isValid = true;

        if (
            is_numeric($cityName)
            || strlen($cityName) < 2
            || strlen ($cityName) > 168
        ) {
            $isValid = false;
        }

        return $isValid;
    }
}
