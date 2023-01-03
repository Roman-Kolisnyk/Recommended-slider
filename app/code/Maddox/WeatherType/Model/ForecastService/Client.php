<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model\ForecastService;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Client
{
    /**
     * @param Curl $curl
     * @param Json $json
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly Curl $curl,
        private readonly Json $json,
        private readonly LoggerInterface $logger,
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    /**
     * @param string $cityName
     * @return int
     */
    public function getCurrentCityTemperature(string $cityName): int
    {
        $currentForecast = $this->getForecastInfo($cityName);

        if ($currentForecast['success'] !== true || empty($currentForecast['response']['main']['temp'])) {
            throw new \InvalidArgumentException($currentForecast['message']);
        }

        if (!is_numeric($currentForecast['response']['main']['temp'])) {
            throw new \InvalidArgumentException(
                'The temperature value was found in the Forecast API response, but with an invalid value.'
            );
        }

        return (int) $currentForecast['response']['main']['temp'];
    }

    /**
     * @param string $cityName
     * @return array
     */
    public function getForecastInfo(string $cityName): array
    {
        return $this->makeRequestToForecast($cityName);
    }

    /**
     * @param string $cityName
     * @return array
     */
    private function makeRequestToForecast(string $cityName): array
    {
        $url = $this->getForecastEndpointUrl($cityName);
        $this->curl->get($url);

        $response = $this->getForecastResponse();

        return $this->handleForecastResponse($response);
    }

    /**
     * @param string $cityName
     * @return string
     */
    private function getForecastEndpointUrl(string $cityName): string
    {
        return sprintf(
            '%s?q=%s&appId=%s&units=metric',
            $this->getForecastApiBaseUrl(),
            $cityName,
            $this->getForecastApiKey()
        );
    }

    /**
     * @return array
     */
    private function getForecastResponse(): array
    {
        $responseJson = $this->curl->getBody();

        try {
            $response = $this->json->unserialize($responseJson);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $this->logger->error(
                $invalidArgumentException->getMessage(),
                [
                    'file' => $invalidArgumentException->getFile(),
                    'line' => $invalidArgumentException->getLine(),
                    'responseJson' => $responseJson
                ]
            );
            $response = $this->failedResponse();
        }

        return $response;
    }

    /**
     * @param array $forecastResponse
     * @return array
     */
    private function handleForecastResponse(array $forecastResponse): array
    {
        $responseStatus = $this->curl->getStatus();

        if (
            $responseStatus !== 200
            || (isset($forecastResponse['success']) && !$forecastResponse['success'])
        ) {
            $this->logger->error(
                'Bad response from the Forecast API: ' . $this->getForecastApiBaseUrl(),
                [
                    'statusCode' => $responseStatus,
                    'invalidResponse' => $forecastResponse
                ]
            );

            return $this->failedResponse();
        }

        return $this->validResponse($forecastResponse);
    }

    /**
     * @return array
     */
    private function failedResponse(): array
    {
        return [
            'success' => false,
            'message' => $this->getForecastApiErrorMessage()
        ];
    }

    /**
     * @param array $response
     * @return array
     */
    private function validResponse(array $response): array
    {
        return [
            'success' => true,
            'response' => $response
        ];
    }

    /**
     * @return string
     */
    private function getForecastApiBaseUrl(): string
    {
        return $this->scopeConfig->getValue('forecast_api/forecast_auth/base_url');
    }

    /**
     * @return string
     */
    private function getForecastApiKey(): string
    {
        return $this->scopeConfig->getValue('forecast_api/forecast_auth/api_key');
    }

    /**
     * @return string
     */
    private function getForecastApiErrorMessage(): string
    {
        return $this->scopeConfig->getValue('forecast_api/forecast_auth/error_message');
    }
}
