<?php

namespace LP\LPShipping\Plugin\Checkout;

class LayoutProcessorPlugin
{
    /**
     * Call method after layout process
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['component'] = 'uiComponent';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['displayArea'] = 'shippingAdditional';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['children']['lpexpress_terminal'] = [
            'component' => 'LP_LPShipping/js/methods/lpexpress',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'LP_LPShipping/lpexpress',
                'options' => [],
            ],
            'dataScope' => 'shippingAddress.lpexpress_terminal',
            'label' => '',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [ 'select-terminal-required' => true ],
            'sortOrder' => 200,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['children']['lp_delivery_time'] = [
            'component' => 'LP_LPShipping/js/methods/lpexpress',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'LP_LPShipping/delivery_time',
                'options' => [],
            ],
            'dataScope' => 'shippingAddress.lp_delivery_time',
            'label' => '',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 300,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation']['validate-phone-number'] = true;

        return $jsLayout;
    }
}
