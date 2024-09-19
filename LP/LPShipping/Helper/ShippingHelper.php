<?php

declare(strict_types=1);

namespace LP\LPShipping\Helper;

use LP\LPShipping\Model\CheckoutProvider;
use LP\LPShipping\Model\Config;
use LP\LPShipping\Model\Config\Source\LPDeliveryMethods;
use LP\LPShipping\Model\Config\Source\LPExpressDeliveryMethods;
use LP\LPShipping\Model\ResourceModel\LPCountries\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order;

class ShippingHelper extends AbstractHelper
{
    const SIZE_TABLE = [
        [
            'weight' => 50,
            'size' => 'XS',
        ],
        [
            'weight' => 500,
            'size' => 'S',
        ],
        [
            'weight' => 2000,
            'size' => 'M',
        ],
        [
            'weight' => 30000,
            'size' => 'L',
        ],
    ];

    const PLAN_TABLE = [
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2H_HANDS => 'HANDS',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::T2H_HANDS => 'HANDS',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::T2T_TERMINAL => 'TERMINAL',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2T_TERMINAL => 'TERMINAL',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::T2S_TERMINAL => 'TERMINAL',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2P_TRACKED_SIGNED =>'TRACKED_SIGNED',
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_UNTRACKED =>'UNTRACKED',
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_SIGNED =>'SIGNED',
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_TRACKED => 'TRACKED'
    ];

    const TYPE_TABLE = [
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2H_HANDS => 'H2H',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::T2H_HANDS => 'T2H',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::T2T_TERMINAL => 'T2T',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2T_TERMINAL => 'H2T',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::T2S_TERMINAL => 'T2S',
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2P_TRACKED_SIGNED =>'H2P',
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_UNTRACKED =>'P2H',
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_SIGNED =>'P2H',
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_TRACKED =>'P2H',
    ];

    const PARCEL_WEIGHT_REQUIRED = [
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_UNTRACKED,
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2H_HANDS,
        'lpcarrier_lpcarrier'. LPExpressDeliveryMethods::H2P_TRACKED_SIGNED,
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_SIGNED,
        'lpcarrier_lpcarrier'. LPDeliveryMethods::METHOD_TRACKED,
    ];

    const ADDITIONAL_SERVICE_COD = 'COD';
    const ADDITIONAL_SERVICE_SIGNED = 'Signed';
    const ADDITIONAL_SERVICE_PRIORITY = 'Priority';

    private $apiHelper;
    private $LPCountries;

    public function __construct(
        Context $context,
        ApiHelper $apiHelper,
        ScopeConfigInterface $scopeConfig,
        Collection $LPCountries
    ) {
        parent::__construct($context);
        $this->apiHelper = $apiHelper;
        $this->scopeConfig = $scopeConfig;
        $this->LPCountries = $LPCountries;
    }

    public static function getParcelType(string $shippingMethod): string
    {
        return substr($shippingMethod, 0, 3);
    }

    public static function getShippingPlanCode(string $shippingMethod): string
    {
        return substr($shippingMethod, 4);
    }

    public static function isTerminalShippingMethod(string $shippingMethod): bool
    {
        return in_array($shippingMethod, CheckoutProvider::TERMINAL_SHIPPING_METHODS, true);
    }

    public static function isParcelWeightRequired(string $shippingMethod): bool
    {
        return in_array($shippingMethod, self::PARCEL_WEIGHT_REQUIRED, true);
    }

    /**
     * @return string|null
     */
    public static function getSizeByWeight($weight)
    {
        foreach (self::SIZE_TABLE as $weightSize) {
            if ($weight <= $weightSize['weight']) {
                return $weightSize['size'];
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public static function getShippingPlan($shippingMethod)
    {
        return self::PLAN_TABLE[$shippingMethod] ?? null;
    }

    /**
     * @return string|null
     */
    public static function getShippingParcelType($shippingMethod)
    {
        return self::TYPE_TABLE[$shippingMethod] ?? null;
    }

    public function isCodAvailable($order): bool
    {
        $shippingMethod = $order->getShippingMethod() ?? $order->getShippingAddress()->getShippingMethod();
        $country = $order->getShippingAddress()->getCountryId();
        if (!$shippingPlan = ShippingHelper::getShippingPlan($shippingMethod)) {
            return false;
        }

        $parcelType = ShippingHelper::getShippingParcelType($shippingMethod);
        $estimatedPlan = $this->apiHelper->getShippingPlanEstimate($country, $shippingPlan, $parcelType);
        if (null === $estimatedPlan) {
            return false;
        }

        $filteredByParcelType = [];
        foreach ($estimatedPlan as $plan) {
            if ($plan->code === $shippingPlan) {
                $filteredByParcelType = array_filter($plan->shipping, function ($shipping) use ($parcelType) {
                    return $shipping->parcelType == $parcelType;
                });
            }
        }

        foreach ($filteredByParcelType as $shippingPlan) {
            foreach ($shippingPlan->options as $option) {
                foreach ($option->services as $service) {
                    if ($service->code === self::ADDITIONAL_SERVICE_COD) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getAvailableSizes(Order $order): array
    {
        $methodWithoutCarrier = str_replace('lpcarrier_lpcarrier', '', $order->getShippingMethod());
        if (in_array($methodWithoutCarrier, LPExpressDeliveryMethods::ALL_METHODS)) {
            return ['XS', 'S', 'M', 'L', 'XL'];
        }

        $country = $this->LPCountries->addFieldToFilter('country_id', $this->getSenderCountryId());
        $senderCountry = $country->getData()[0]['country_code'];
        if (LPDeliveryMethods::METHOD_UNTRACKED) {
            $sizes = ['XS', 'S'];
            if ($order->getShippingAddress()->getCountryId() !== $senderCountry) {
                $sizes[] = 'M';
            }

            return $sizes;
        }

        if (LPDeliveryMethods::METHOD_TRACKED) {
            return ['S', 'M'];
        }

        if (LPDeliveryMethods::METHOD_SIGNED) {
            return ['XS', 'S', 'M', 'L'];
        }

        return [];
    }

    //@TODO this could be cached. $cacheKey = $shippingMethod . $weight . $receiverCountryCode;
    public function isShippingMethodAvailable(string $shippingMethod, $weight, string $receiverCountryCode): bool
    {
        $parcelType = self::getParcelType($shippingMethod);
        $planCode = self::getShippingPlanCode($shippingMethod);
        $size = $this->getSizeByWeight($weight);
        $response = $this->apiHelper->getShippingAvailable($receiverCountryCode, $planCode, $parcelType, $weight, $size);

        return $response ? $response['available'] ?? false : false;
    }

    public function getQuoteWeight(array $items, bool $weightInGrams = false)
    {
        $weight = 0;
        foreach ($items as $item) {
            $weight += ($item->getWeight() * ($item->getQty() ?: $item->getQtyToShip()));
        }
        if ($weightInGrams) {
            $weightUnit = $this->scopeConfig->getValue('general/locale/weight_unit', ScopeInterface::SCOPE_STORE);
            $gramsPerWeighTUnit = [
                'lbs' => 454,
                'kgs' => 1000,
            ];
            $grams = $gramsPerWeighTUnit[$weightUnit] ?? 1000;

            $weight = $weight * $grams;
        }

        return $weight;
    }

    public function getSenderName()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_NAME, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_NAME, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderPhone()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_PHONE, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_PHONE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_EMAIL, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_EMAIL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderCountryId()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_COUNTRY, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_COUNTRY, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderCity()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_CITY, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_CITY, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderStreet()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_STREET, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_STREET, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderBuilding()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_BUILDING, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_BUILDING, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderApartment()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_APARTMENT, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_APARTMENT, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderPostCode()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_POSTCODE, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_POSTCODE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderAddressLine1()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_ADDRESS_LINE_1, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_ADDRESS_LINE_1, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderAddressLine2()
    {
        return $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER)
            ? $this->scopeConfig->getValue(Config::CONFIG_PATH_SENDER_ADDRESS_LINE_2, ScopeInterface::SCOPE_WEBSITE)
            : $this->scopeConfig->getValue(Config::CONFIG_PATH_WAREHOUSE_ADDRESS_LINE_2, ScopeInterface::SCOPE_WEBSITE);
    }
}
