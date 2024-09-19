<?php

namespace LP\LPShipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AvailableServices implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [ 'value' => '0', 'label' => __('All') ],
            [ 'value' => '1', 'label' => __('Lithuanian Post') ],
            [ 'value' => '2', 'label' => __('Unisend') ],
        ];
    }
}
