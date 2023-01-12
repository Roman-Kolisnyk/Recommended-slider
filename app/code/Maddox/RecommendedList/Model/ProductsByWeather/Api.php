<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Model\ProductsByWeather;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;
use Maddox\RecommendedList\Helper\Config as RecommendedListConfig;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Maddox\RecommendedList\Model\Services\GeoIp\Client as GeoIpService;
use Maddox\RecommendedList\Model\ProductsByWeather\PreparePostData;
use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Api
{
    private const PRODUCTS_BY_WEATHER_TYPE_ENDPOINT = 'rest/default/V1/products-by-weather/city/%s/size/%s';

    /**
     * @param Curl $curl
     * @param CustomerSession $customerSession
     * @param LoggerInterface $logger
     * @param RecommendedListConfig $recommendedListConfig
     * @param RemoteAddress $remoteAddress
     * @param GeoIpService $geoIpService
     * @param PreparePostData $preparePostData
     * @param UrlInterface $url
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly Curl $curl,
        private readonly CustomerSession $customerSession,
        private readonly LoggerInterface $logger,
        private readonly RecommendedListConfig $recommendedListConfig,
        private readonly RemoteAddress $remoteAddress,
        private readonly GeoIpService $geoIpService,
        private readonly PreparePostData $preparePostData,
        private readonly UrlInterface $url,
        private readonly SerializerInterface $serializer
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
            $products = $this->getProducts($customerCity, $sliderSize);
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
    private function getProducts(string $customerCity, int $sliderSize): array
    {
        if ($this->recommendedListConfig->isRequestProductsWithGraphql()) {
            $products = $this->makeGraphQlQueryToGetProducts($customerCity, $sliderSize);
        } else {
            $products = $this->makeRequestToGetProducts($customerCity, $sliderSize);
        }

        return $products;
    }

    /**
     * @param string $customerCity
     * @param int $sliderSize
     * @return array
     */
    private function makeGraphQlQueryToGetProducts(string $customerCity, int $sliderSize): array
    {
        $query = $this->getGraphQlQueryToGetProducts();

        $variables = [
            'inputData' => [
                'cityName' => $customerCity,
                'pageSize' => $sliderSize
            ]
        ];

        $queryParams = [
            'query' => $query,
            'variables' => $variables,
            'operationName' => 'GetProductListByCityWeather'
        ];

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->post($this->url->getUrl('graphql'), $this->serializer->serialize($queryParams));

        return $this->getProductsFromGraphQlResponse();
    }

    /**
     * @return string
     */
    private function getGraphQlQueryToGetProducts(): string
    {
        return <<<QUERY
            query GetProductListByCityWeather(\$inputData: productListByCityWeatherInput!) {
                productListByCityWeather(
                    input: \$inputData
                ) {
                    success
                    products {
                        entity_id
                        attribute_set_id
                        type_id
                        sku
                        name
                        url_key
                        store_id
                        final_price
                        image_url
                        product_url
                        is_available
                        is_saleable
                    }
                    message
                }
            }
        QUERY;
    }

    /**
     * @return array
     */
    private function getProductsFromGraphQlResponse(): array
    {
        $curlResponse = $this->curl->getBody();
        $output = $this->serializer->unserialize($curlResponse);

        if (!empty($output['data']['productListByCityWeather']['success'])) {
            $products = $output['data']['productListByCityWeather']['products'];
        } else {
            $this->logger->error($output['data']['productListByCityWeather']['message']);
        }

        return $products ?? [];
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
        $endpoint = sprintf(
                self::PRODUCTS_BY_WEATHER_TYPE_ENDPOINT,
                $cityName,
                $sliderSize
            );

        return $this->url->getBaseUrl() . $endpoint;
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
            $response = $this->serializer->unserialize($this->curl->getBody());
        } catch (\InvalidArgumentException) {
            $response = [];
        }

        return $response;
    }
}
