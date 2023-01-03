<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Controller\Adminhtml\Weather\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Maddox\WeatherType\Api\WeatherTypeInterface;
use Maddox\WeatherType\Model\WeatherTypeRepository;
use Magento\Framework\Controller\ResultInterface;
use Magento\Catalog\Model\ResourceModel\Attribute as CatalogAttributeResource;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param WeatherTypeRepository $weatherTypeRepository
     * @param CatalogAttributeResource $catalogAttributeResource
     */
    public function __construct(
        Context $context,
        private readonly WeatherTypeRepository $weatherTypeRepository,
        private readonly CatalogAttributeResource $catalogAttributeResource
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $weatherTypeIds = $this->getRequest()->getParam('selected');

        if (!$weatherTypeIds) {
            $this->messageManager->addErrorMessage(__('Something went wrong during removing'));

            return $resultRedirect->setPath('*/*/');
        }

        if (is_array($weatherTypeIds)) {
            foreach ($weatherTypeIds as $weatherTypeId) {
                $this->removeWeatherTypeById($weatherTypeId);
            }
        } elseif (is_numeric($weatherTypeIds)) {
            $this->removeWeatherTypeById($weatherTypeIds);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param string $weatherTypeId
     * @return void
     */
    private function removeWeatherTypeById(string $weatherTypeId): void
    {
        try {
            $weatherType = $this->weatherTypeRepository->get($weatherTypeId);
            $this->removeAttributeOptionsToProducts($weatherType);
            $this->weatherTypeRepository->delete($weatherType);
            $this->messageManager->addSuccessMessage(
                __('The weather type (%weatherTypeName) was removed successfully',
                    ['weatherTypeName' => $weatherType->getLabel()]
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param WeatherTypeInterface $weatherType
     * @return void
     */
    private function removeAttributeOptionsToProducts(WeatherTypeInterface $weatherType): void
    {
        $removedWeatherType = $weatherType->getLabel();

        $this->catalogAttributeResource->getConnection()->delete(
            $this->catalogAttributeResource->getTable('catalog_product_entity_varchar'),
            ['value = ?' => $removedWeatherType]
        );

        $select = $this->catalogAttributeResource->getConnection()->select()
            ->from(
                $this->catalogAttributeResource->getTable('catalog_product_entity_varchar')
            )->where(
                "value LIKE '%$removedWeatherType%'"
            );

        $selectResult = $this->catalogAttributeResource->getConnection()->fetchAll($select);

        if (count($selectResult)) {
            foreach ($selectResult as $item) {
                $itemValues = explode(',', $item['value']);
                $keyToRemove = array_search($removedWeatherType, $itemValues, true);

                if ($keyToRemove) {
                    unset($itemValues[$keyToRemove]);
                    $newItemValues = implode(',', $itemValues);

                    $this->catalogAttributeResource->getConnection()->update(
                        $this->catalogAttributeResource->getTable('catalog_product_entity_varchar'),
                        ['value' => $newItemValues],
                        ['value_id = ?' => $item['value_id']]
                    );
                }
            }
        }
    }
}
