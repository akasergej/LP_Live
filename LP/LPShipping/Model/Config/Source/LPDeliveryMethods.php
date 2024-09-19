<?php

namespace LP\LPShipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LPDeliveryMethods extends AbstractLPDeliveryMethodsValidator implements OptionSourceInterface
{
    const METHOD_UNTRACKED = 'P2H_UNTRACKED';
    const METHOD_TRACKED = 'P2H_TRACKED';
    const METHOD_SIGNED = 'P2H_SIGNED';

    public function toOptionArray(): array
    {
        $adminLabels = [
            self::METHOD_UNTRACKED => __("Shipment from the post office to the receiver's address/post office"),
            self::METHOD_TRACKED => __("Shipment from the post office to the receiver's address without a signature"),
            self::METHOD_SIGNED => __("Shipment from the post office to the receiver's address/post office with signature"),
        ];
        $toReturn = [];
        foreach ($this->getLpShippingMethods(true) as $code => $label) {
            $toReturn[] = [
                'value' => $code, 'label' => $adminLabels[$code]
            ];
        }

        return $toReturn;
    }

    public function getLpShippingMethods(bool $validateInApi = false): array
    {
        //h2h - hands to hands
        //p2h - post office to hands
        //t2t - terminal to terminal

        $deliveryMethods = [
            self::METHOD_UNTRACKED => __('To address/post office'),
            self::METHOD_TRACKED => __('To address without a signature'),
            self::METHOD_SIGNED => __('To address/post office with signature'),
        ];

        if (!$validateInApi) {
            return $deliveryMethods;
        }

        return $this->getValidShippingMethods($deliveryMethods);
    }
}
