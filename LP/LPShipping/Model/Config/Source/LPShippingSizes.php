<?php

namespace LP\LPShipping\Model\Config\Source;

class LPShippingSizes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [ 'value' => 'S', 'label' => 'S' ],
            [ 'value' => 'M', 'label' => 'M' ],
            [ 'value' => 'L', 'label' => 'L' ],
        ];
    }

    /**
     * @return array
     */
    public function toOverseasSizesArray()
    {
        return [
            [ 'value' => 'S', 'label' => 'S' ],
            [ 'value' => 'M', 'label' => 'M' ],
            [ 'value' => 'L', 'label' => 'L' ],
        ];
    }

    /**
     * @return array
     */
    public function toOverseasOptionArray()
    {
        return [
            [ 'value' => 'SMALL_CORESPONDENCE', 'label' => 'S' ],
            [ 'value' => 'BIG_CORESPONDENCE', 'label' => 'M' ],
            [ 'value' => 'PACKAGE', 'label' => 'L' ],
            [ 'value' => 'SMALL_CORESPONDENCE_TRACKED', 'label' => 'SMALL_CORESPONDENCE_TRACKED - S' ],
            [ 'value' => 'MEDIUM_CORESPONDENCE_TRACKED', 'label' => 'MEDIUM_CORESPONDENCE_TRACKED - M' ]
        ];
    }
}
