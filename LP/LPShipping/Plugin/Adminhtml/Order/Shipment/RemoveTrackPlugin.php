<?php

namespace LP\LPShipping\Plugin\Adminhtml\Order\Shipment;

use LP\LPShipping\Helper\ApiHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\RemoveTrack;

class RemoveTrackPlugin
{
    /**
     * @var ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * @var ShipmentRepositoryInterface $_shipmentRepository
     */
    protected $_shipmentRepository;

    /**
     * RemoveTrackPlugin constructor.
     * @param ApiHelper $apiHelper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ApiHelper $apiHelper,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_apiHelper           = $apiHelper;
        $this->_shipmentRepository  = $shipmentRepository;
        $this->_orderRepository     = $orderRepository;
    }

    /**
     * Cancel label on track delete
     * @param RemoveTrack $subject
     */
    public function beforeExecute(RemoveTrack $subject)
    {
        $shipment = $this->_shipmentRepository->get($subject->getRequest()->getParam('shipment_id'));
        $order = $this->_orderRepository->get($shipment->getOrderId());

        if ($order->getLpShippingItemId()) {
            if ($this->_apiHelper->cancelShipment($order)) {
                $order->setLpRequestId(null);
                $order->setLpShippingItemId(null);
                $order->setStatus('processing');
                $this->_orderRepository->save($order);
            } else {
                die('<b>Could not cancel shipping label. Please try to refresh this page and try again.</b>');
            }
        }
    }
}
