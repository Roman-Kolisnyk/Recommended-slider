<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Controller\Adminhtml\Weather\Type;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Maddox_WeatherType::weather_type';

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Maddox_WeatherType::weather_type');
        $resultPage->getConfig()->getTitle()->prepend((__('Weather Types')));

        return $resultPage;
    }
}
