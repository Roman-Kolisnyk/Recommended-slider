<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Controller\Adminhtml\Weather\Type;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->setActiveMenu('Maddox_WeatherType::weather_type')
            ->addBreadcrumb(__('New Weather Type'), __('Weather Type'));
        $result->getConfig()->getTitle()->prepend(__('New Weather Type'));

        return $result;
    }
}
