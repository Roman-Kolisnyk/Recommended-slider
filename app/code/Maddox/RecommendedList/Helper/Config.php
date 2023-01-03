<?php

declare(strict_types=1);

namespace Maddox\RecommendedList\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    private const XML_PATH_SLIDER_OPTIONS = 'slider/slider_options/';

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        private readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    /**
     * @param string $pageName
     * @return bool
     */
    public function isSliderEnabled(string $pageName): bool
    {
        return $this->getConfigFlag(self::XML_PATH_SLIDER_OPTIONS . 'enabled_on_' . $pageName);
    }

    /**
     * @return string|false
     */
    public function getSliderTitle(): string|false
    {
        return $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'slider_title');
    }

    /**
     * Retrieve the value of the items quantity in the slider
     *
     * @return int
     */
    public function getSliderSize(): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'items_qty');
    }

    /**
     * @return bool
     */
    public function isDotsEnabled(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_SLIDER_OPTIONS . 'is_dots_enabled');
    }

    /**
     * @return bool
     */
    public function isInfiniteEnabled(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_SLIDER_OPTIONS . 'is_infinite_enabled');
    }

    /**
     * @return int
     */
    public function getSlidesToShow(): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'slides_to_show');
    }

    /**
     * @return int
     */
    public function getSlidesToScroll(): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'slides_to_scroll');
    }

    /**
     * @return bool
     */
    public function isAutoplayEnabled(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_SLIDER_OPTIONS . 'is_autoplay_enabled');
    }

    /**
     * @return int
     */
    public function getAutoplaySpeed(): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'autoplay_speed');
    }

    /**
     * @return int
     */
    public function getSliderSpeed(): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'slider_speed');
    }

    /**
     * @return bool
     */
    public function isPauseOnHoverEnabled(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_SLIDER_OPTIONS . 'is_pause_on_hover_enabled');
    }

    /**
     * @return bool
     */
    public function isGeoIpSearchEnabled(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_SLIDER_OPTIONS . 'is_geo_ip_enabled');
    }

    /**
     * @return string
     */
    public function getGeoIpBaseUrl(): string
    {
        return $this->getConfigValue(self::XML_PATH_SLIDER_OPTIONS . 'geo_ip_base_url');
    }

    /**
     * @param string $configPath
     * @return mixed
     */
    private function getConfigValue(string $configPath): mixed
    {
        try {
            return $this->scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException) {
            return false;
        }
    }

    /**
     * @param string $configPath
     * @return bool
     */
    private function getConfigFlag(string $configPath): bool
    {
        try {
            return $this->scopeConfig->isSetFlag(
                $configPath,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException) {
            return false;
        }
    }
}
