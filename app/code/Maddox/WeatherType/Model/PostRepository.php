<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Maddox\WeatherType\Api\PostRepositoryInterface;
use Maddox\WeatherType\Model\ForecastService\Client as ForecastApi;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Type\Pool as ProductTypePool;
use Maddox\WeatherType\Model\ResourceModel\WeatherType\CollectionFactory as WeatherTypeCollection;

class PostRepository implements PostRepositoryInterface
{
    private ProductCollection $productMatchCollection;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ForecastApi $forecastApi
     * @param ProductHelper $productHelper
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param ProductType $productType
     * @param ProductTypePool $productTypePool
     * @param WeatherTypeCollection $weatherTypeCollection
     */
    public function __construct(
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ForecastApi $forecastApi,
        private readonly ProductHelper $productHelper,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductType $productType,
        private readonly ProductTypePool $productTypePool,
        private readonly WeatherTypeCollection $weatherTypeCollection
    ) {}

    /**
     * @param string $cityName
     * @param int $pageSize
     * @return ProductInterface[]
     */
    public function getProductList(string $cityName, int $pageSize): array
    {
        if (!$this->validateRequestParams($cityName, $pageSize)) {
            return $this->requestedParamsNotValid();
        }

        $cityTemperature = $this->getCityTemperature(trim($cityName));

        if (!is_numeric($cityTemperature)) {
            return $this->impossibleGetCurrentCityTemperature($cityTemperature);
        }

        $weatherTypes = $this->getWeatherTypesByTemperature($cityTemperature);

        $this->productMatchCollection = $this->getProductMatchCollection($weatherTypes, $pageSize);

        if (!$this->productMatchCollection->getSize()) {
            return $this->invalidProductMatchCollection();
        }

        $this->setAdditionalDataToProducts();

        return $this->prepareSuccessfulResponse();
    }

    /**
     * @param string $cityName
     * @param int $pageSize
     * @return bool
     */
    private function validateRequestParams(string $cityName, int $pageSize): bool
    {
        $isValid = true;

        if (
            empty($pageSize)
            || !is_numeric($pageSize)
            || empty($cityName)
            || is_numeric($cityName)
            || strlen($cityName) < 2
            || strlen ($cityName) > 168
        ) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param string $cityName
     * @return int|string
     */
    private function getCityTemperature(string $cityName): int|string
    {
        try {
            $cityTemperature = $this->forecastApi->getCurrentCityTemperature($cityName);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $cityTemperature = $invalidArgumentException->getMessage();
        }

        return $cityTemperature;
    }

    /**
     * @return array
     */
    private function requestedParamsNotValid(): array
    {
        return [
            'success' => false,
            'message' => 'Mistake in your request. Please, verify your parameters and try again.'
        ];
    }

    /**
     * @return array
     */
    private function prepareSuccessfulResponse(): array
    {
        return [
            'success' => true,
            'products' => $this->productMatchCollection->toArray()
        ];
    }

    /**
     * @return array
     */
    private function invalidProductMatchCollection(): array
    {
        return [
            'success' => false,
            'message' => "Couldn'\t find any matches for your request. Try again just later."
        ];
    }

    /**
     * @param string $failMessage
     * @return array
     */
    private function impossibleGetCurrentCityTemperature(string $failMessage): array
    {
        return [
            'success' => false,
            'message' => $failMessage
        ];
    }

    /**
     * @param int $temperature
     * @return array
     */
    private function getWeatherTypesByTemperature(int $temperature): array
    {
        $weatherTypes = [];
        $weatherTypeCollection = $this->weatherTypeCollection->create();

        $weatherTypeCollection->getSelect()
            ->where('main_table.minimum_temperature_value <= ?', $temperature)
            ->where('main_table.maximum_temperature_value >= ?', $temperature);

        if ($weatherTypeCollection->getSize()) {
            foreach ($weatherTypeCollection as $weatherType) {
                $weatherTypes[] = $weatherType->getLabel();
            }
        }

        return $weatherTypes;
    }

    /**
     * @param array $weatherTypes
     * @param int $pageSize
     * @return ProductCollection
     */
    private function getProductMatchCollection(array $weatherTypes, int $pageSize): ProductCollection
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->setPageSize($pageSize)
            ->setStore($this->getStore());

        $weatherTypesCount = count($weatherTypes);

        for ($i = 0; $i < $weatherTypesCount; $i++) {
            if ($i < 1) {
                $collection
                    ->addAttributeToFilter('weather_type', ['like' => "%$weatherTypes[$i]%"]);
            } else {
                $collection->getSelectSql()
                    ->orWhere("at_weather_type.value LIKE '%$weatherTypes[$i]%'");
            }
        }

        return $collection;
    }

    /**
     * @return StoreInterface|string
     */
    private function getStore(): StoreInterface|string
    {
        try {
            $store = $this->storeManager->getStore();
        } catch (NoSuchEntityException) {
            $store = 'default';
        }

        return $store;
    }

    /**
     * @return PostRepository
     */
    private function setAdditionalDataToProducts(): PostRepository
    {
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($this->productMatchCollection as $product) {
            $product->addData([
                'image_url' => $this->productHelper->getImageUrl($product),
                'product_url' => $this->getProductUrl($product),
                'is_available' => $product->isAvailable(),
                'is_saleable' => $product->isSaleable(),
                'final_price' => $product->getFinalPrice()
            ]);
        }

        return $this;
    }

    /**
     * @param ProductInterface $product
     * @return string|null
     */
    private function getProductUrl(ProductInterface $product): ?string
    {
        $isVisible = $product->isVisibleInSiteVisibility();

        if (!$isVisible) {
            $childProductId = (int) $product->getId();
            $parentProductId = $this->getParentProductIdByChild($childProductId);
            $parentProduct = $this->getProductById($parentProductId);
            $productUrl = $parentProduct?->getProductUrl();
        } else {
            $productUrl = $product->getProductUrl();
        }

        return $productUrl;
    }

    /**
     * @param int $childProductId
     * @return int
     */
    private function getParentProductIdByChild(int $childProductId): int
    {
        $parentProductIds = [];
        $productTypes = $this->productType->getTypes();

        foreach ($productTypes as $productType) {
            $productTypeModel = $this->getProductTypeModel($productType);
            $parentProductIds += $productTypeModel?->getParentIdsByChild($childProductId);
        }

        if ($parentProductIds) {
            array_filter($parentProductIds);
        }

        return (int) array_shift($parentProductIds);
    }

    /**
     * @param array $productType
     * @return AbstractType|null
     */
    private function getProductTypeModel(array $productType): ?AbstractType
    {
        try {
            $productTypeModelName = $productType['model'] ?? $this->productType::DEFAULT_TYPE_MODEL;

            $productTypeModel = $this->productTypePool->get($productTypeModelName);
            $productTypeModel->setConfig($productType);
            $productTypeModel->setTypeId($productType['name']);
        } catch (\Magento\Framework\Exception\LocalizedException) {
            $productTypeModel = null;
        }

        return $productTypeModel;
    }

    /**
     * @param int $productId
     * @return ProductInterface|null
     */
    private function getProductById(int $productId): ?ProductInterface
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException) {
            $product = null;
        }

        return $product;
    }
}
