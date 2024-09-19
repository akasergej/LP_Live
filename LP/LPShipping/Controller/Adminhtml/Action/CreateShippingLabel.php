<?php

declare(strict_types=1);

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Controller\Adminhtml\MassAction\CreateShippingLabels;
use LP\LPShipping\Helper\ShippingHelper;
use LP\LPShipping\Model\Config;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Ui\Component\MassAction\Filter;

class CreateShippingLabel extends CreateShippingLabels
{
    protected $_convertOrder;
    protected $_shipmentRepository;
    protected $_orderRepository;
    private $shippingHelper;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        Order $convertOrder,
        LabelGenerator $labelGenerator,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        Config $config,
        ShippingHelper $shippingHelper
    ) {
        $this->_convertOrder = $convertOrder;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_orderRepository = $orderRepository;
        $this->shippingHelper = $shippingHelper;
        parent::__construct($context, $filter, $orderCollectionFactory, $convertOrder, $labelGenerator, $shipmentRepository, $orderRepository, $config, $shippingHelper);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create($this->resultFactory::TYPE_REDIRECT);
        $redirect = $resultRedirect->setPath($this->_redirect->getRefererUrl());

        try {
            /** @var Order $order */
            $order = $this->_orderRepository->get($this->getRequest()->getParam('orderid'));
            if ($order->getLpShippingItemId() && Config::SHIPMENT_CANCELED === $order->getStatus()) {
                $this->messageManager->addErrorMessage(
                    sprintf(
                        '%s %s',
                        __('You have already created label for order'),
                        $order->getIncrementId()
                    )
                );
                return $redirect;
            }

            if (!$order->getLpUniqueId()) {
                $order->setLpUniqueId(uniqid('lp'));
            }
            if (
                ShippingHelper::isParcelWeightRequired($order->getShippingMethod())
                && (
                    !$order->getLpPackageWeight()
                    && !$this->shippingHelper->getQuoteWeight($order->getAllItems(), true)
                )
            ) {
                $this->messageManager->addErrorMessage(
                    sprintf(
                        '%s %s',
                        __('Parcel weight is required for order, please specify in shipment details'),
                        $order->getIncrementId()
                    )
                );
                return $redirect;
            }
            // Create shipment if order doesn't have one
            if (!$order->hasShipments()) {
                if (!$order->canShip()) {
                    $this->messageManager->addErrorMessage(
                        sprintf(
                            '%s %s',
                            __('You can\'t create shipping labels for order'),
                            $order->getIncrementId()
                        )
                    );
                    return $redirect;
                }

                // Convert order to shipment
                $orderShipment = $this->_convertOrder->toShipment($order);
                foreach ($order->getAllItems() as $orderItem) {
                    // Check if virtual item and item Quantity
                    if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    // Convert to shipment item
                    $orderShipment->addItem($this->_convertOrder->itemToShipmentItem($orderItem)
                        ->setQty($orderItem->getQtyToShip()));

                    if (!$orderShipment->getId()) {
                        $orderShipment->register();
                    }

                    $this->_shipmentRepository->save($orderShipment);
                    $this->_orderRepository->save($orderShipment->getOrder());
                }

                $this->createShippingLabel($orderShipment);
            } else {
                // Create label if order has shipment
                $orderShipment = $this->_convertOrder->toShipment($order);
                if (!$orderShipment->getId()) {
                    $orderShipment->register();
                }

                $this->createShippingLabel($order->getShipmentsCollection()->getFirstItem());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect;
    }
}
