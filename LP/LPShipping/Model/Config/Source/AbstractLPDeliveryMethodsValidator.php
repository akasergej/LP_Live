<?php

declare(strict_types=1);

namespace LP\LPShipping\Model\Config\Source;

use LP\LPShipping\Helper\ApiHelper;
use LP\LPShipping\Helper\ShippingHelper;

abstract class AbstractLPDeliveryMethodsValidator
{
    private $apiHelper;

    public function __construct(ApiHelper $apiHelper)
    {
        $this->apiHelper = $apiHelper;
    }

    protected function getValidShippingMethods(array $localShippingMethods): array
    {
        $toReturn = $localShippingMethods;
        if (empty($methodsFromApi = $this->apiHelper->getShippingPlans())) {
            return [];
        }
        foreach ($localShippingMethods as $code => $label) {
            $parcelType = ShippingHelper::getParcelType($code);
            $serviceCode = ShippingHelper::getShippingPlanCode($code);
            $found = false;
            foreach ($methodsFromApi as $methodFromApi) {
                if ($serviceCode !== $methodFromApi['code']) {
                    continue;
                }

                foreach ($methodFromApi['shipping'] as $shippingMethod) {
                    if ($parcelType === $shippingMethod['parcelType']) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                unset($toReturn[$code]);
            }
        }

        return $toReturn;
    }
}
