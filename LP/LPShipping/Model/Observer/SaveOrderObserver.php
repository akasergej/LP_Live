<?php

namespace LP\LPShipping\Model\Observer;

use LP\LPShipping\Helper\ShippingHelper;
use LP\LPShipping\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;

class SaveOrderObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface $_objectManager
     */
    protected $_objectManager;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        ShippingHelper $shippingHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_config = $config;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        // Save LP EXPRESS terminal to order
        /** @var Order $order */
        $order = $observer->getOrder();

        if (ShippingHelper::isTerminalShippingMethod($order->getShippingMethod())) {
            /** @var QuoteRepository $quoteRepository */
            $quoteRepository = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
            $quote = $quoteRepository->get($order->getQuoteId());
            $terminal = $quote->getLpexpressTerminal();
            if (!$terminal && !empty($_POST['order'])) {
                $terminal = $_POST['order']['lpexpress_terminal'] ?? null;
            }
            $order->setLpexpressTerminal($terminal);
        }

        // Save method size and type
        $order->setLpPackageWeight($this->shippingHelper->getQuoteWeight($order->getAllItems(), true));
        $order->setLpShippingSize($this->_config->getDefaultShipmentSize($order->getShippingMethod()));

        return $this;
    }
}
