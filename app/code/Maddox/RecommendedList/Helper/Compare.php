<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Helper;

class Compare extends \Magento\Catalog\Helper\Product\Compare
{
    /**
     * Get parameters used for build add product to compare list urls
     *
     * @param array $product
     * @return string
     */
    public function getAddToComparePostParams(array $product): string
    {
        $params = ['product' => $product['entity_id']];
        $requestingPageUrl = $this->_getRequest()->getParam('requesting_page_url');

        if (!empty($requestingPageUrl)) {
            $encodedUrl = $this->urlEncoder->encode($requestingPageUrl);
            $params[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED] = $encodedUrl;
        }

        return $this->postHelper->getPostData($this->getAddUrl(), $params);
    }
}
