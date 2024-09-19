<?php

namespace LP\LPShipping\Model\Config\Source;

class LPExpressPostOfficeMethods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [ 'value' => 'AB', 'label' => 'AB' ]
        ];
    }
}
