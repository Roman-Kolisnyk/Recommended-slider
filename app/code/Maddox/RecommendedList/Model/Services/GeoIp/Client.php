<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Model\Services\GeoIp;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Maddox\RecommendedList\Helper\Config as RecommendedListConfig;

class Client
{
    /**
     * @var string
     */
    private readonly string $ip;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param LoggerInterface $logger
     * @param RecommendedListConfig $recommendedListConfig
     */
    public function __construct(
        private readonly Curl $curl,
        private readonly Json $json,
        private readonly LoggerInterface $logger,
        private readonly RecommendedListConfig $recommendedListConfig
    ) {}

    /**
     * @param string $ip
     * @return string|null
     */
    public function getCityByIpAddress(string $ip): ?string
    {
        $city = null;

        if ($this->validateIpAddress($ip)) {
            $this->setIp($ip);
            $geolocation = $this->makeRequestToGeoIp();
            $city = $this->getCityFromGeoIpResponse($geolocation);
        }

        return $city;
    }

    /**
     * @param string $ip
     * @return void
     */
    private function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @param string $ip
     * @return bool|string
     */
    private function validateIpAddress(string $ip): bool|string
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * @return array|null
     */
    private function makeRequestToGeoIp(): ?array
    {
        $url = $this->getGeoIpUrl();
        $this->curl->get($url);

        $response = $this->getGeoIpResponse();

        return $this->handleGeoIpResponse($response);
    }

    /**
     * @param array|null $response
     * @return array|null
     */
    private function handleGeoIpResponse(?array $response): ?array
    {
        if (
            !$response
            || (isset($response['status']) && $response['status'] === 'fail')
        ) {
            $this->logger->error(
                $response['message'] ?? __('Couldn\'t get response from the GeoIp'),
                $response
            );
            $response = null;
        }

        return $response;
    }

    /**
     * @return string
     */
    private function getGeoIpUrl(): string
    {
        $geoIpBaseUrl = $this->recommendedListConfig->getGeoIpBaseUrl();

        return sprintf(
            $geoIpBaseUrl . '%s',
            $this->ip
        );
    }

    /**
     * @return array|null
     */
    private function getGeoIpResponse(): ?array
    {
        try {
            $curlResponse = $this->json->unserialize($this->curl->getBody());
        } catch (\InvalidArgumentException) {
            $curlResponse = null;
        }

        return $curlResponse;
    }

    /**
     * @param array|null $response
     * @return string|null
     */
    private function getCityFromGeoIpResponse(?array $response): ?string
    {
        return $response['city'] ?? null;
    }
}
