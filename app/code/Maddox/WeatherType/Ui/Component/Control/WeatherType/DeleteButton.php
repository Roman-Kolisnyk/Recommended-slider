<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Ui\Component\Control\WeatherType;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $currentWeatherTypeId = $this->getCurrentWeatherTypeId();

        if ($currentWeatherTypeId) {
            return [
                'id' => 'delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('" .__('Are you sure you want to delete this weather type?') ."', '"
                    . $this->getUrl('*/*/delete', ['selected' => $currentWeatherTypeId]) . "', {data: {}})",
                'class' => 'delete',
                'sort_order' => 10
            ];
        }

        return [];
    }
}
