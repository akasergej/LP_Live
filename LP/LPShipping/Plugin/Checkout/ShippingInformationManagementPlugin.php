<?php

namespace LP\LPShipping\Plugin\Checkout;

use LP\LPShipping\Helper\ShippingHelper;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\LocalizedException;

class ShippingInformationManagementPlugin
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository $_quoteRepository
     */
    protected $_quoteRepository;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->_quoteRepository = $quoteRepository;
    }

    /**
     * Process before checkout shipping info save
     *
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        $selectedTerminal = $extAttributes->getLpexpressTerminal();

        $quote = $this->_quoteRepository->getActive($cartId);
        if (
            ShippingHelper::isTerminalShippingMethod($addressInformation->getShippingMethodCode())
            && $selectedTerminal == null
        ) {
            throw new LocalizedException(__('Please select terminal.'));
        }

        $quote->setLpexpressTerminal($selectedTerminal);
    }
}
