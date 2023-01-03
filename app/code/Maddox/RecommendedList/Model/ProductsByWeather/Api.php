<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Model\ProductsByWeather;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Maddox\RecommendedList\Helper\Config as RecommendedListConfig;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Maddox\RecommendedList\Model\Services\GeoIp\Client as GeoIpService;
use Maddox\RecommendedList\Model\ProductsByWeather\PreparePostData;

class Api
{
    private const PRODUCTS_BY_WEATHER_TYPE_ENDPOINT = 'http://magento24.ca/rest/default/V1/products-by-weather/city/%s/size/%s';

    /**
     * @param Curl $curl
     * @param CustomerSession $customerSession
     * @param Json $json
     * @param LoggerInterface $logger
     * @param RecommendedListConfig $recommendedListConfig
     * @param RemoteAddress $remoteAddress
     * @param GeoIpService $geoIpService
     * @param PreparePostData $preparePostData
     */
    public function __construct(
        private readonly Curl $curl,
        private readonly CustomerSession $customerSession,
        private readonly Json $json,
        private readonly LoggerInterface $logger,
        private readonly RecommendedListConfig $recommendedListConfig,
        private readonly RemoteAddress $remoteAddress,
        private readonly GeoIpService $geoIpService,
        private readonly PreparePostData $preparePostData
    ) {}

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductsByWeatherType(): array
    {
        $products = [];
        $customerCity = $this->getCustomerCity();

        if (!$customerCity) {
            return $products;
        }

        $sliderSize = $this->recommendedListConfig->getSliderSize();

        if ($sliderSize) {
            $products = $this->makeRequestToGetProducts($customerCity, $sliderSize);
            $this->preparePostData->setPostParams($products);
        }

        return $products;
    }

    /**
     * @return string|null
     */
    private function getCustomerCity(): ?string
    {
        $customerAddresses = $this->customerSession->getCustomer()?->getAddresses();

        foreach ($customerAddresses as $customerAddress) {
            if ($customerAddress['city']) {
                $customerCity = $customerAddress['city'];
                break;
            }
        }

        if (!isset($customerCity)) {
            $customerCity = $this->getCustomerCityByIpAddress();
        }

        return $customerCity ?? null;
    }

    /**
     * @return string|null
     */
    private function getCustomerCityByIpAddress(): ?string
    {
        if ($this->recommendedListConfig->isGeoIpSearchEnabled()) {
            $customerIp = $this->getCustomerIpAddress();

            if ($customerIp) {
                return $this->geoIpService->getCityByIpAddress($customerIp);
            }
        }

        return null;
    }

    /**
     * @return bool|string
     */
    private function getCustomerIpAddress(): bool|string
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * @param string $customerCity
     * @param int $sliderSize
     * @return array
     */
    private function makeRequestToGetProducts(string $customerCity, int $sliderSize): array
    {
        $url = $this->getProductsByWeatherUrl($customerCity, $sliderSize);
        $this->curl->get($url);

        return $this->getProductsFromResponse();
    }

    /**
     * @param string $cityName
     * @param int $sliderSize
     * @return string
     */
    private function getProductsByWeatherUrl(string $cityName, int $sliderSize): string
    {
        return sprintf(
            self::PRODUCTS_BY_WEATHER_TYPE_ENDPOINT,
            $cityName,
            $sliderSize
        );
    }

    /**
     * @return array
     */
    private function getProductsFromResponse(): array
    {
        $curlResponse = $this->getCurlResponse();

        if (isset($curlResponse['success']) && !$curlResponse['success']) {
            $this->logger->error($curlResponse['message']);
        }

        return $curlResponse['products'] ?? [];
    }

    /**
     * @return array
     */
    private function getCurlResponse(): array
    {
        try {
            $response = $this->json->unserialize($this->curl->getBody());
        } catch (\InvalidArgumentException) {
            $response = [];
        }

        return $response;
    }
}
