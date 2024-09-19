<?php

declare(strict_types=1);

namespace LP\LPShipping\Plugin\Checkout;

use LP\LPShipping\Helper\ShippingHelper;
use Magento\Checkout\Model\Session;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Quote\Model\PaymentMethodManagement;

class PaymentMethodManagementPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    public function __construct(
        Session $checkoutSession,
        ShippingHelper $shippingHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shippingHelper = $shippingHelper;
    }

    public function afterGetList(PaymentMethodManagement $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();
        if ($this->shippingHelper->isCodAvailable($quote)) {
            return $result;
        }

        return array_filter($result, function ($paymentMethod) {
            return $paymentMethod->getCode() !== Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;
        });
    }
}
