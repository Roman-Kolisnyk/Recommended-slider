<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\WishlistFactory;

class Wishlist extends Data
{
    private Escaper $escaper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param StoreManagerInterface $storeManager
     * @param PostHelper $postDataHelper
     * @param View $customerViewHelper
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Session $customerSession,
        WishlistFactory $wishlistFactory,
        StoreManagerInterface $storeManager,
        PostHelper $postDataHelper,
        View $customerViewHelper,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Escaper $escaper
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $customerSession,
            $wishlistFactory,
            $storeManager,
            $postDataHelper,
            $customerViewHelper,
            $wishlistProvider,
            $productRepository,
            $escaper
        );
        $this->escaper = $escaper;
    }

    /**
     * Retrieve params for adding product to wishlist
     *
     * @param array $product
     * @param array $params
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAddToWishlistPostParams(array $product, array $params = []): string
    {
        $productId = (int) $product['entity_id'];
        $url = $this->_storeManager->getStore($product['store_id'])->getUrl('wishlist/index/add');

        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->_postDataHelper->getPostData(
            $this->escaper->escapeUrl($url),
            $params
        );
    }
}
