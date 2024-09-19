<?php

declare(strict_types=1);

namespace LP\LPShipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LPPackageSizes implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'XS', 'label' => 'XS'],
            ['value' => 'S', 'label' => 'S'],
            ['value' => 'M', 'label' => 'M'],
            ['value' => 'L', 'label' => 'L'],
        ];
    }
}
