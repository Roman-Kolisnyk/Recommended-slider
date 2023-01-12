<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Model;

use Maddox\WeatherType\Api\WeatherTypeInterface;
use Maddox\WeatherType\Model\ResourceModel\WeatherType as WeatherTypeResource;
use Maddox\WeatherType\Model\WeatherTypeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class WeatherTypeRepository implements \Maddox\WeatherType\Api\WeatherTypeRepositoryInterface
{
    /**
     * @param WeatherTypeResource $weatherTypeResource
     * @param WeatherTypeFactory $weatherTypeFactory
     */
    public function __construct(
        private readonly WeatherTypeResource $weatherTypeResource,
        private readonly WeatherTypeFactory $weatherTypeFactory
    ) {}

    /**
     * @param WeatherTypeInterface $weatherType
     * @return WeatherTypeInterface
     * @throws LocalizedException
     */
    public function save(WeatherTypeInterface $weatherType): WeatherTypeInterface
    {
        try {
            $this->weatherTypeResource->save($weatherType);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    "Couldn'\t save the weather type with name: %name. %exceptionMessage",
                    [
                        'name' => $weatherType->getLabel(),
                        'exceptionMessage' => $e->getMessage()
                    ]
                )
            );
        }

        return $weatherType;
    }

    /**
     * @param WeatherTypeInterface $weatherType
     * @return void
     * @throws LocalizedException
     */
    public function delete(WeatherTypeInterface $weatherType): void
    {
        try {
            $this->weatherTypeResource->delete($weatherType);
        } catch (\Exception) {
            throw new LocalizedException(
                __("Couldn'\t remove the weather type with id: #%1", $weatherType->getEntityId())
            );
        }
    }

    /**
     * @param int|string $weatherTypeId
     * @return void
     * @throws LocalizedException
     */
    public function deleteById(int|string $weatherTypeId): void
    {
        if (is_numeric($weatherTypeId)) {
            $weatherType = $this->get($weatherTypeId);
            $this->delete($weatherType);
        } else {
            throw new LocalizedException(__("The requested id is not valid"));
        }
    }

    /**
     * @param int|string $weatherTypeId
     * @return WeatherTypeInterface
     * @throws NoSuchEntityException
     */
    public function get(int|string $weatherTypeId): WeatherTypeInterface
    {
        $weatherType = null;

        if (is_numeric($weatherTypeId)) {
            $weatherType = $this->weatherTypeFactory->create();
            $this->weatherTypeResource->load($weatherType, $weatherTypeId, WeatherTypeInterface::ENTITY_ID);
        }

        if (!$weatherType?->getData('entity_id')) {
            throw new NoSuchEntityException(__("Couldn'\t find the weather type with id: #%1", $weatherTypeId));
        }

        return $weatherType;
    }

    /**
     * @param string $weatherTypeName
     * @return WeatherTypeInterface
     * @throws NoSuchEntityException
     */
    public function getByName(string $weatherTypeName): WeatherTypeInterface
    {
        $weatherType = null;

        if ($weatherTypeName) {
            $weatherType = $this->weatherTypeFactory->create();
            $this->weatherTypeResource->load($weatherType, $weatherTypeName, WeatherTypeInterface::LABEL);
        }

        if (!$weatherType?->getData('entity_id')) {
            throw new NoSuchEntityException(__("Couldn'\t find the weather type with name: %1", $weatherTypeName));
        }

        return $weatherType;
    }
}
