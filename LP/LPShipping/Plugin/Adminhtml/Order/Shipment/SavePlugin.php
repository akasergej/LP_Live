<?php

namespace LP\LPShipping\Plugin\Adminhtml\Order\Shipment;

use LP\LPShipping\Model\Config;

class SavePlugin
{
    /**
     * @var \LP\LPShipping\Model\Config $_config
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * SavePlugin constructor.
     * @param \LP\LPShipping\Model\Config $config
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \LP\LPShipping\Model\Config $config,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->_config                  = $config;
        $this->_orderRepository         = $orderRepository;
    }

    public function afterExecute(
        \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save $subject,
        $result
    ) {
        $order_id = $subject->getRequest()->getParam('order_id');
        $order = $this->_orderRepository->get($order_id);

        // Set custom order status
        $this->_config->setOrderStatus($order, Config::SHIPMENT_CREATED_STATUS);
        $this->_orderRepository->save($order);

        return $result;
    }
}
