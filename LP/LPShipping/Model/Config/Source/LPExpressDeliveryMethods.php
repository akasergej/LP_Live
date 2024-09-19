<?php

namespace LP\LPShipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LPExpressDeliveryMethods extends AbstractLPDeliveryMethodsValidator implements OptionSourceInterface
{
    const H2H_HANDS = 'H2H_HANDS';
    const T2H_HANDS = 'T2H_HANDS';
    const T2T_TERMINAL = 'T2T_TERMINAL';
    const H2T_TERMINAL = 'H2T_TERMINAL';
    const T2S_TERMINAL = 'T2S_TERMINAL';
    const H2P_TRACKED_SIGNED = 'H2P_TRACKED_SIGNED';
    const P2H_UNTRACKED = 'P2H_UNTRACKED';
    const P2H_SIGNED = 'P2H_SIGNED';

    const ALL_METHODS = [
        self::H2H_HANDS,
        self::T2H_HANDS,
        self::T2T_TERMINAL,
        self::H2T_TERMINAL,
        self::T2S_TERMINAL,
        self::H2P_TRACKED_SIGNED,
    ];

    public function toOptionArray(): array
    {
        $adminLabels = [
            self::H2H_HANDS => __("Shipment from home/office delivered by UNISEND courier to the receiver's address"),
            self::T2H_HANDS => __("Shipment from UNISEND self-service terminal/locker, delivered by UNISEND courier to the receiver's address"),
            self::T2T_TERMINAL => __('Shipment from and to UNISEND self-service terminal/ locker.'),
            self::H2T_TERMINAL => __('Shipment from home/office delivered to UNISEND self-service terminal/ locker.'),
            self::T2S_TERMINAL => __('Shipment from and to UNISEND self-service terminal/ locker within 72 hours'),
            self::H2P_TRACKED_SIGNED => __("Shipment from home/office delivered to the receiver's post office"),
        ];
        $toReturn = [];
        foreach ($this->getUnisendShippingMethods(true) as $code => $label) {
            $toReturn[] = [
                'value' => $code, 'label' => $adminLabels[$code],
            ];
        }

        return $toReturn;
    }

    public function getUnisendShippingMethods(bool $validateInApi = false): array
    {
        //h2h - hands to hands
        //p2h - post office to hands
        //t2t - terminal to terminal
        $deliveryMethods = [
            self::H2H_HANDS => __("Courier to the receiver's address"),
            self::T2H_HANDS => __("Courier to the receiver's address"),
            self::T2T_TERMINAL => __('To UNISEND self-service terminal/ locker'),
            self::H2T_TERMINAL => __('To UNISEND self-service terminal/ locker'),
            self::T2S_TERMINAL => __('To UNISEND self-service terminal/ locker'),
            self::H2P_TRACKED_SIGNED => __("To receiver's post office"),
        ];

        if (!$validateInApi) {
            return $deliveryMethods;
        }

        return $this->getValidShippingMethods($deliveryMethods);
    }
}
