<?php

namespace LP\LPShipping\Model\Config\Source;

class LPExpressShippingSizes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [ 'value' => 'XSmall', 'label' => 'XS' ],
            [ 'value' => 'Small', 'label' => 'S' ],
            [ 'value' => 'Medium', 'label' => 'M' ],
            [ 'value' => 'Large', 'label' => 'L' ],
            [ 'value' => 'XLarge', 'label' => 'XL' ],
        ];
    }
}
