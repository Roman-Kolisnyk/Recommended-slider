<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Controller\Adminhtml\Weather\Type;

use Maddox\WeatherType\Model\WeatherTypeRepository;
use Maddox\WeatherType\Api\WeatherTypeInterface;
use Maddox\WeatherType\Model\WeatherTypeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param WeatherTypeFactory $weatherTypeFactory
     * @param WeatherTypeRepository $weatherTypeRepository
     */
    public function __construct(
        Context $context,
        private readonly WeatherTypeFactory $weatherTypeFactory,
        private readonly WeatherTypeRepository $weatherTypeRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $generalInputsData = $request->getParam('general');

        if (!$request->isPost() || empty($generalInputsData)) {
            $this->messageManager->addErrorMessage(__('Wrong request'));
            $resultRedirect->setPath('*/*/add');

            return $resultRedirect;
        }

        try {
            $weatherTypeId = $generalInputsData[WeatherTypeInterface::ENTITY_ID];
            $weatherType = $this->weatherTypeRepository->get($weatherTypeId);
        } catch (\Exception) {
            $weatherType = $this->weatherTypeFactory->create();
        }

        $weatherType->addData([
            WeatherTypeInterface::LABEL => $generalInputsData[WeatherTypeInterface::LABEL],
            WeatherTypeInterface::MINIMUM_TEMPERATURE_VALUE => $generalInputsData[WeatherTypeInterface::MINIMUM_TEMPERATURE_VALUE],
            WeatherTypeInterface::MAXIMUM_TEMPERATURE_VALUE => $generalInputsData[WeatherTypeInterface::MAXIMUM_TEMPERATURE_VALUE]
        ]);

        try {
            $this->weatherTypeRepository->save($weatherType);
            $this->processRedirectAfterSuccessSave($resultRedirect, $weatherType->getEntityId());
            $this->messageManager->addSuccessMessage(__('Weather type was saved'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("Failed to save weather type: %1", $e->getMessage()));
            $resultRedirect->setPath('*/*/add');
        }

        return $resultRedirect;
    }

    /**
     * @param Redirect $resultRedirect
     * @param string $id
     * @return void
     */
    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, string $id): void
    {
        if ($this->getRequest()->getParam('back')) {
            $resultRedirect->setPath(
                '*/*/edit',
                [
                    WeatherTypeInterface::ENTITY_ID => $id,
                    '_current' => true,
                ]
            );
        } elseif ($this->getRequest()->getParam('redirect_to_new')) {
            $resultRedirect->setPath(
                '*/*/add',
                [
                    '_current' => true,
                ]
            );
        } else {
            $resultRedirect->setPath('*/*/');
        }
    }
}
