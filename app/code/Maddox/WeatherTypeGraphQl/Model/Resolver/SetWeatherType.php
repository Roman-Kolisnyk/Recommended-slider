<?php

declare(strict_types=1);

namespace Maddox\WeatherTypeGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\ProductRepository;
use Maddox\WeatherType\Model\WeatherTypeRepository;

class SetWeatherType implements ResolverInterface
{
    /**
     * @var array
     */
    private array $errorMessages = [];

    /**
     * @param ProductRepository $productRepository
     * @param WeatherTypeRepository $weatherTypeRepository
     */
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly WeatherTypeRepository $weatherTypeRepository
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
        $inputParams = $args['input'];

        if (!$this->validate($inputParams)) {
            return $this->prepareResponse(false);
        }

        $products = $this->getProducts($inputParams);

        if ($this->errorMessages || !$products) {
            return $this->prepareResponse(false);
        }

        $weatherTypes = implode(',', array_unique($inputParams['weather_types']));
        $this->setWeatherTypesToProducts($products, $weatherTypes);

        return $this->prepareResponse(empty($this->errorMessages));
    }

    /**
     * @param array $inputParams
     * @return bool
     */
    private function validate(array $inputParams): bool
    {
        $isValid = true;

        if (!$this->validateInputParams($inputParams) || !$this->checkWeatherTypes($inputParams)) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param array $inputParams
     * @return bool
     */
    private function validateInputParams(array $inputParams): bool
    {
        $isValid = true;

        if (
            empty($inputParams['weather_types'])
            || (empty($inputParams['product_ids']) && empty($inputParams['product_skus']))
        ) {
            $isValid = false;
            $this->errorMessages[] = __('Invalid requested arguments. Some required arguments are empty.');
        }

        return $isValid;
    }

    /**
     * Check if a weather type exists
     *
     * @param array $inputParams
     * @return bool
     */
    private function checkWeatherTypes(array $inputParams): bool
    {
        $isValid = true;
        $weatherTypes = array_unique($inputParams['weather_types']);

        foreach ($weatherTypes as $weatherType) {
            try {
                $this->weatherTypeRepository->getByName($weatherType);
            } catch (NoSuchEntityException $e) {
                $isValid = false;
                $this->errorMessages[] = $e->getMessage();
            }
        }

        return $isValid;
    }

    /**
     * @param array $inputParams
     * @return ProductInterface[]
     */
    private function getProducts(array $inputParams): array
    {
        $products = [];

        if (!empty($inputParams['product_ids'])) {
            $productIds = array_unique($inputParams['product_ids']);
            $products = array_merge($products, $this->collectProductsByIds($productIds));
        }

        if (!empty($inputParams['product_skus'])) {
            $productSkus = array_unique($inputParams['product_skus']);
            $products = array_merge($products, $this->collectProductsBySkus($productSkus));
        }

        return $products;
    }

    /**
     * @param array $productIds
     * @return ProductInterface[]
     */
    private function collectProductsByIds(array $productIds): array
    {
        $products = [];

        foreach ($productIds as $productId) {
            try {
                $products[] = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $this->errorMessages[] = __(
                    'Product id #%product_id: %error_message',
                    [
                        'product_id' => $productId,
                        'error_message' => $e->getMessage()
                    ]
                );
            }
        }

        return $products;
    }

    /**
     * @param array $productSkus
     * @return ProductInterface[]
     */
    private function collectProductsBySkus(array $productSkus): array
    {
        $products = [];

        foreach ($productSkus as $productSku) {
            try {
                $products[] = $this->productRepository->get($productSku);
            } catch (NoSuchEntityException $e) {
                $this->errorMessages[] = __(
                    "Product sku '%product_sku': %error_message",
                    [
                        'product_sku' => $productSku,
                        'error_message' => $e->getMessage()
                    ]
                );
            }
        }

        return $products;
    }

    /**
     * @param ProductInterface[] $products
     * @param string $weatherTypes list of weather types separated by a comma: 'cold,hot,warm,cool'
     * @return void
     */
    private function setWeatherTypesToProducts(array $products, string $weatherTypes): void
    {
        foreach ($products as $product) {
            if ($product->getData('weather_type') !== $weatherTypes) {
                $product->setData('weather_type', $weatherTypes);
                $this->saveProduct($product);
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    private function saveProduct(ProductInterface $product): void
    {
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }
    }

    /**
     * @param bool $success
     * @return array
     */
    private function prepareResponse(bool $success): array
    {
        $response = [
            'success' => $success
        ];

        if ($this->errorMessages) {
            $response['message'] = $this->errorMessages;
        }

        return $response;
    }
}
