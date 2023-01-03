<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Controller\Adminhtml\Weather\Type;

use Maddox\WeatherType\Model\WeatherTypeRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Edit extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Maddox_WeatherType::weather_type';

    /**
     * @param Context $context
     * @param WeatherTypeRepository $weatherTypeRepository
     */
    public function __construct(
        Context $context,
        private readonly WeatherTypeRepository $weatherTypeRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $weatherTypeId = $this->getRequest()->getParam('entity_id');

        try {
            $weatherType = $this->weatherTypeRepository->get($weatherTypeId);

            $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $result->setActiveMenu('Maddox_WeatherType::weather_type')
                ->addBreadcrumb(__('Edit Weather Type'), __('Weather Type'));
            $result->getConfig()
                ->getTitle()
                ->prepend(__('Edit Weather Type: %type', ['type' => $weatherType->getLabel()]));
        } catch (\Exception) {
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('Weather type with id "%id" does not exist', ['id' => $weatherTypeId])
            );
            $result->setPath('*/*/');
        }

        return $result;
    }
}
