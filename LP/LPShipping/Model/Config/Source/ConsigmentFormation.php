<?php

namespace LP\LPShipping\Model\Config\Source;

class ConsigmentFormation implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [ 'value' => 'priority', 'label' => __('Priority') ],
            [ 'value' => 'registered', 'label' => __('Registerd') ]
        ];
    }
}
