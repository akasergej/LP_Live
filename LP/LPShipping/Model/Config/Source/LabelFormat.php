<?php

namespace LP\LPShipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LabelFormat implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'LAYOUT_MAX', 'label' => 'LAYOUT_MAX'],
            ['value' => 'LAYOUT_10x15', 'label' => 'LAYOUT_10x15']
        ];
    }
}
