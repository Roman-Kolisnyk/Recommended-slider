<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Block;

use Maddox\RecommendedList\Model\ProductsByWeather\Api as ProductsByWeatherApi;
use Magento\Framework\Exception\NoSuchEntityException;
use Maddox\RecommendedList\Helper\Wishlist as WishlistHelper;
use Magento\Catalog\Block\Product\Context;
use Maddox\RecommendedList\Helper\Config as RecommendedListConfig;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\View\LayoutFactory;

/**
 * Crosssell block for product
 */
class Slider extends \Magento\Catalog\Block\Product\AbstractProduct
{
    protected $_template = 'Maddox_RecommendedList::product/list/items.phtml';

    /**
     * @param Context $context
     * @param ProductsByWeatherApi $productsByWeatherApi
     * @param WishlistHelper $wishlistHelper
     * @param RecommendedListConfig $recommendedListConfig
     * @param ProductFactory $productFactory
     * @param LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly ProductsByWeatherApi $productsByWeatherApi,
        private readonly WishlistHelper $wishlistHelper,
        private readonly RecommendedListConfig $recommendedListConfig,
        private readonly ProductFactory $productFactory,
        private readonly LayoutFactory $layoutFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItems(): array
    {
        $items = [];

        if ($this->isSliderAvailable()) {
            $items = $this->productsByWeatherApi->getProductsByWeatherType();
            $this->setProductDetailsHtml($items);
        }

        return $items;
    }

    /**
     * @return bool
     */
    private function isSliderAvailable(): bool
    {
        $currentPageName = $this->getPageName();

        if (!$currentPageName) {
            return false;
        }

        return $this->recommendedListConfig->isSliderEnabled($currentPageName);
    }

    /**
     * @return bool
     */
    public function isAllowWishlist(): bool
    {
        return $this->wishlistHelper->isAllow();
    }

    /**
     * @return string|false
     */
    public function getSliderTitle(): string|false
    {
        return $this->recommendedListConfig->getSliderTitle();
    }

    /**
     * @return array
     */
    public function getSliderOptions(): array
    {
        return [
            'dots' => $this->recommendedListConfig->isDotsEnabled(),
            'infinite' => $this->recommendedListConfig->isInfiniteEnabled(),
            'slidesToShow' => $this->recommendedListConfig->getSlidesToShow(),
            'slidesToScroll' => $this->recommendedListConfig->getSlidesToScroll(),
            'autoplay' => $this->recommendedListConfig->isAutoplayEnabled(),
            'autoplaySpeed' => $this->recommendedListConfig->getAutoplaySpeed(),
            'speed' => $this->recommendedListConfig->getSliderSpeed(),
            'pauseOnHover' => $this->recommendedListConfig->isPauseOnHoverEnabled()
        ];
    }

    /**
     * @param array $products
     * @return void
     */
    private function setProductDetailsHtml(array &$products): void
    {
        foreach ($products as &$product) {
            $productModel = $this->productFactory->create()->setData($product);
            $product['product_options_html'] = $this->getProductDetailsHtml($productModel);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDetailsRendererList(): \Magento\Framework\View\Element\RendererList|bool|\Magento\Framework\View\Element\BlockInterface
    {
        if (empty($this->rendererListBlock)) {
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }

        return $this->rendererListBlock;
    }
}
