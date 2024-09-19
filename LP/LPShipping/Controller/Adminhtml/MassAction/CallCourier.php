<?php

namespace LP\LPShipping\Controller\Adminhtml\MassAction;

use LP\LPShipping\Helper\ApiHelper;
use LP\LPShipping\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Ui\Component\MassAction\Filter;

class CallCourier extends Action
{
    /**
     * @var ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var Filter $_filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory $_orderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var ShipmentNotifier
     */
    protected $_shippingNotifier;

    /**
     * @var PrintManifests
     */
    protected $_printManifests;


    /**
     * CallCourier constructor.
     * @param ApiHelper $apiHelper
     * @param Config $config
     * @param Filter $filter
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context* @param ShipmentNotifier $shippingNotifier
     * @param PrintManifests $printManifests
     */
    public function __construct(
        ApiHelper $apiHelper,
        Config  $config,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Context $context,
        ShipmentNotifier $shippingNotifier,
        PrintManifests $printManifests
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_config = $config;
        $this->_filter = $filter;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderRepository = $orderRepository;
        $this->_shippingNotifier = $shippingNotifier;
        $this->_printManifests = $printManifests;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $orderNumber = null;
        try {
            $orderCollection = $this->_filter->getCollection($this->_orderCollectionFactory->create());
            /** @var Order $order */
            foreach ($orderCollection->getItems() as $order) {
                $orderNumber = $order->getIncrementId();
                if (!$this->_config->isCallCourierAllowed($order)) {
                    $this->messageManager->addErrorMessage(sprintf('%s %s', __('Courier call not allowed for order'), $order->getIncrementId()));
                    continue;
                }

                if ($this->_apiHelper->callCourier([$order->getLpShippingItemId()])) {
                    $shipment = $order->getShipmentsCollection()->getFirstItem();
                    if ($shipment) {
                        $this->_shippingNotifier->notify($shipment);
                    }
                    $order->setStatus($this->_config::COURIER_CALLED_STATUS);
                    $this->_orderRepository->save($order);
                } else {
                    $this->messageManager->addErrorMessage(sprintf('%s %s', __('Could not call courier for order'), $order->getIncrementId()));
                }
            }
            $this->_printManifests->execute();
            $this->messageManager->addSuccessMessage(__('Call Courier action complete'));
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
