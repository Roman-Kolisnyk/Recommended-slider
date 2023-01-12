<?php

declare(strict_types=1);

namespace Maddox\WeatherTypeGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Maddox\WeatherType\Model\WeatherTypeRepository;
use Maddox\WeatherType\Model\WeatherTypeFactory;

class CreateWeatherType implements ResolverInterface
{
    private const REQUIRED_FIELDS = [
        'label',
        'minimum_temperature_value',
        'maximum_temperature_value'
    ];

    /**
     * @param WeatherTypeRepository $weatherTypeRepository
     * @param WeatherTypeFactory $weatherTypeFactory
     */
    public function __construct(
        private readonly WeatherTypeRepository $weatherTypeRepository,
        private readonly WeatherTypeFactory $weatherTypeFactory
    ) {}

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string[]
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $inputData = $args['input'];
        $isInputDataValid = $this->validateInputData($inputData);

        if (!$isInputDataValid) {
            $response = $this->prepareResponse(false, 'Invalid input data');
        }

        if ($isInputDataValid) {
            $result = $this->createWeatherType($inputData);
            $response = $result['success']
                ? $this->prepareResponse($result['success'])
                : $this->prepareResponse($result['success'], $result['errorMessage']);
        }

        return $response;
    }

    /**
     * @param array $inputData
     * @return bool
     */
    private function validateInputData(array $inputData): bool
    {
        $isValid = true;

        foreach (self::REQUIRED_FIELDS as $requiredField) {
            if (!array_key_exists($requiredField, $inputData) || empty($inputData[$requiredField])) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }

    /**
     * @param string $weatherTypeName
     * @return bool
     */
    private function isWeatherTypeExist(string $weatherTypeName): bool
    {
        try {
            $this->weatherTypeRepository->getByName($weatherTypeName);
            $isExist = true;
        } catch (NoSuchEntityException) {
            $isExist = false;
        }

        return $isExist;
    }

    /**
     * @param array $data
     * @return array
     */
    private function createWeatherType(array $data): array
    {
        $weatherType = $this->weatherTypeFactory->create();
        $weatherType->addData($data);

        try {
            $this->weatherTypeRepository->save($weatherType);
            $result = ['success' => true];
        } catch (LocalizedException $e) {
            $result = ['success' => false, 'errorMessage' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * @param bool $status
     * @param string $errorMessage
     * @return string[]
     */
    private function prepareResponse(bool $status, string $errorMessage = ''): array
    {
        $response = [
            'created' => $status
        ];

        if ($errorMessage) {
            $response['errorMessage'] = $errorMessage;
        }

        return $response;
    }
}
