<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Helper;

use Magento\Framework\App\ActionInterface;

class Cart extends \Magento\Checkout\Helper\Cart
{
    /**
     * @param array $product
     * @return array
     */
    public function getAddToCartPostParams(array $product): array
    {
        $url = $this->getAddToCartUrl($product);

        return [
            'action' => $url,
            'data' => [
                'product' => $product['entity_id'],
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    /**
     * Retrieve url for add product to cart
     *
     * @param array $product
     * @return string
     */
    private function getAddToCartUrl(array $product): string
    {
        $uenc = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $uenc,
            'product' => $product['entity_id'],
            '_secure' => $this->_request->isSecure()
        ];

        if ($this->_request->getRouteName() === 'checkout' && $this->_request->getControllerName() === 'cart') {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/add', $routeParams);
    }
}
