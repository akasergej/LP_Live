<?php

namespace LP\LPShipping\Block\Adminhtml\Order\Create;

use LP\LPShipping\Helper\ShippingHelper;

class Terminal extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var \LP\LPShipping\Api\LPExpressTerminalRepositoryInterface $_terminalRepository
     */
    protected $_terminalRepository;

    /**
     * Terminal constructor.
     * @param \LP\LPShipping\Api\LPExpressTerminalRepositoryInterface $terminalRepository
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \LP\LPShipping\Api\LPExpressTerminalRepositoryInterface $terminalRepository,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_terminalRepository = $terminalRepository;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Show select if lpexpress terminal shipping method selected
     */
    public function showSelect(): bool
    {
        return ShippingHelper::isTerminalShippingMethod($this->getCreateOrderModel()->getShippingAddress()->getShippingMethod());
    }

    /**
     * Get terminal list from repository
     * @return array
     */
    public function getTerminals()
    {
        return $this->_terminalRepository->getList();
    }
}
