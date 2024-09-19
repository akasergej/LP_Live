<?php

namespace LP\LPShipping\Controller\Adminhtml\MassAction;

use LP\LPShipping\Helper\ShippingHelper;
use LP\LPShipping\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Ui\Component\MassAction\Filter;

class CreateShippingLabels extends Action
{
    /**
     * @var CollectionFactory $_orderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Filter $_filter
     */
    protected $_filter;

    /**
     * @var Order $_convertOrder
     */
    protected $_convertOrder;

    /**
     * @var LabelGenerator $_labelGenerator
     */
    protected $_labelGenerator;

    /**
     * @var ShipmentRepositoryInterface $_shipmentRepository
     */
    protected $_shipmentRepository;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    /**
     * CreateShippingLabels constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $orderCollectionFactory
     * @param Order $convertOrder
     * @param LabelGenerator $labelGenerator
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     */
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
        $this->_filter = $filter;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_convertOrder = $convertOrder;
        $this->_labelGenerator = $labelGenerator;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_orderRepository = $orderRepository;
        $this->_config = $config;
        $this->shippingHelper = $shippingHelper;

        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    protected function createShippingLabel(Shipment $shipment)
    {
        $items = [];
        $weight = $price = 0;
        /** @var RequestInterface $request */
        $request = $this->_objectManager->create('Magento\Framework\App\RequestInterface');

        // Format packages
        foreach ($shipment->getAllItems() as $item) {
            $items [ $item->getOrderItemId() ] = [
                'qty' => $item->getQty(),
                'price' => $item->getPrice(),
                'name' => $item->getName(),
                'weight' => $item->getWeight(),
                'product_id' => $item->getId(),
                'order_item_id' => $item->getOrderItemId()
            ];

            $weight += $item->getWeight();
            $price += $item->getPrice();
        }

        // Set packages
        $request->setParams([
            'packages' => [
                '1' => [
                    'params' => [
                        'container' => '',
                        'weight' => $weight,
                        'customs_value' => $price,
                        'length' => 0,
                        'width' => 0,
                        'height' => 0,
                        'weight_units' => 'KILOGRAM',
                        'dimension_units' => 'CENTIMETER',
                        'content_type' => '',
                        'content_type_other' => ''
                    ],
                    'items' => $items
                ]
            ]
        ]);

        // Create the shipping label
        $this->_labelGenerator->create($shipment, $request);
        $this->_shipmentRepository->save($shipment);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute()
    {
        $orderNumber = null;
        try {
            $orderCollection = $this->_filter->getCollection($this->_orderCollectionFactory->create());

            /** @var \Magento\Sales\Model\Order $order */
            foreach ($orderCollection as $order) {
                $orderNumber = $order->getIncrementId();
                if (!$order->getLpShippingItemId() || Config::SHIPMENT_CANCELED === $order->getStatus()) {
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

                        continue;
                    }
                    // Create shipment if order doesn't have one
                    try {
                        if (!$order->hasShipments()) {
                            if (!$order->canShip()) {
                                $this->messageManager->addErrorMessage(
                                    sprintf(
                                        '%s %s',
                                        __('You can\'t create shipping labels for order'),
                                        $order->getIncrementId()
                                    )
                                );
                                continue;
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
                    } catch (\Throwable $e) {
                        $this->messageManager->addErrorMessage(sprintf('%s: %s', $orderNumber, __($e->getMessage())));
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        sprintf(
                            '%s %s',
                            __('You have already created label for order'),
                            $order->getIncrementId()
                        )
                    );
                }
            }
            $this->messageManager->addSuccessMessage(__('You created the shipping labels.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                sprintf('%s: %s',
                    $orderNumber,
                    __($e->getMessage())
                )
            );
        }

        $resultRedirect = $this->resultFactory->create($this->resultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath($this->_redirect->getRefererUrl());
    }
}
