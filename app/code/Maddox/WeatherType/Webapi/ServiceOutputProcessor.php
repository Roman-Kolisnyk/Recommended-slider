<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Webapi;

use Magento\Framework\Webapi\ServiceOutputProcessor as ParentServiceOutputProcessor;

/**
 * Data object converter
 */
class ServiceOutputProcessor extends ParentServiceOutputProcessor
{
    /**
     * Convert associative array into proper data object.
     *
     * @param array $data
     * @param string $type
     * @return array|object
     */
    public function convertValue($data, $type): array|object
    {
        if (is_array($data)) {
            $result = [];
            $arrayElementType = substr($type, 0, -2);

            foreach ($data as $key => $datum) {
                if (is_object($datum)) {
                    $datum = $this->processDataObject(
                        $this->dataObjectProcessor->buildOutputDataArray($datum, $arrayElementType)
                    );
                }

                $result[$key] = $datum;
            }

            return $result;
        } elseif (is_object($data)) {
            return $this->processDataObject(
                $this->dataObjectProcessor->buildOutputDataArray($data, $type)
            );
        } elseif ($data === null) {
            return [];
        } else {
            /** No processing is required for scalar types */
            return $data;
        }
    }
}
