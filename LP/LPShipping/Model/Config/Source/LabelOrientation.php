<?php

declare(strict_types=1);

namespace LP\LPShipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LabelOrientation implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'portrait', 'label' => 'Portrait'],
            ['value' => 'landscape', 'label' => 'Landscape']
        ];
    }
}
