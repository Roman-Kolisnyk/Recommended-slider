<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Ui\Component\Control\WeatherType;

use Maddox\WeatherType\Model\WeatherTypeRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class GenericButton
{
    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param WeatherTypeRepository $weatherTypeRepository
     */
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        private readonly RequestInterface $request,
        private readonly WeatherTypeRepository $weatherTypeRepository
    ) {}

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @return string|null
     */
    public function getWeatherTypeId(): ?string
    {
        $weatherTypeId = $this->request->getParam('entity_id');

        if (!$weatherTypeId) {
            return null;
        }

        try {
            $weatherType = $this->weatherTypeRepository->get($weatherTypeId);
        } catch (\Exception) {
            $weatherType = null;
        }

        return $weatherType->getEntityId();
    }
}
