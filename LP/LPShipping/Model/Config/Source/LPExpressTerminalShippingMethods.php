<?php

namespace LP\LPShipping\Model\Config\Source;

class LPExpressTerminalShippingMethods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [ 'value' => 'HC', 'label' => 'HC' ],
            [ 'value' => 'CC', 'label' => 'CC' ]
        ];
    }
}
