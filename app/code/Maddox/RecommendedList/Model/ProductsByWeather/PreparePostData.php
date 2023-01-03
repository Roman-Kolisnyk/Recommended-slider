<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Model\ProductsByWeather;

use Maddox\RecommendedList\Helper\Cart as CartHelper;
use Maddox\RecommendedList\Helper\Wishlist as WishlistHelper;
use Maddox\RecommendedList\Helper\Compare as CompareHelper;
use Magento\Framework\Exception\NoSuchEntityException;

class PreparePostData
{
    /**
     * @param CartHelper $cartHelper
     * @param WishlistHelper $wishlistHelper
     * @param CompareHelper $compareHelper
     */
    public function __construct(
        private readonly CartHelper $cartHelper,
        private readonly WishlistHelper $wishlistHelper,
        private readonly CompareHelper $compareHelper
    ) {}

    /**
     * Set post parameters.
     *
     * @param array $products
     * @return void
     * @throws NoSuchEntityException
     */
    public function setPostParams(array &$products): void
    {
        if (!$products) {
            return;
        }

        foreach ($products as &$product) {
            if (is_array($product) && !empty($product)) {
                $this->setAddToCartPostParams($product);
                $this->setAddToWishlistPostParams($product);
                $this->setAddToComparePostParams($product);
            }
        }
    }

    /**
     * @param array $product
     * @return void
     */
    private function setAddToCartPostParams(array &$product): void
    {
        $product['to_cart_action_data'] = $this->cartHelper->getAddToCartPostParams($product);
    }

    /**
     * @param array $product
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setAddToWishlistPostParams(array &$product): void
    {
        $product['to_wishlist_action_data'] = $this->wishlistHelper->getAddToWishlistPostParams($product);
    }

    /**
     * @param array $product
     * @return void
     */
    private function setAddToComparePostParams(array &$product): void
    {
        $product['to_compare_action_data'] = $this->compareHelper->getAddToComparePostParams($product);
    }
}
