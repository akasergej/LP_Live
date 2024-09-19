<?php

namespace LP\LPShipping\Controller\Adminhtml\MassAction;

use LP\LPShipping\Helper\ApiHelper;
use LP\LPShipping\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class CancelShippingLabels extends Action
{
    /**
     * @var ShipmentTrackRepositoryInterface $_shipmentTrackRepository
     */
    protected $_shipmentTrackRepository;

    /**
     * @var Filter $_filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory $_orderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * CancelShippingLabels constructor.
     * @param Config $config
     * @param ApiHelper $apiHelper
     * @param ShipmentTrackRepositoryInterface $shipmentTrackRepository
     * @param Filter $filter
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        Config $config,
        ApiHelper $apiHelper,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->_shipmentTrackRepository = $shipmentTrackRepository;
        $this->_filter                  = $filter;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_config                  = $config;
        $this->_apiHelper               = $apiHelper;
        $this->_orderRepository         = $orderRepository;

        parent::__construct($context);
    }

    /**
     * Delete tracking info
     *
     * @param Shipment $orderShipment
     * @throws CouldNotDeleteException
     */
    protected function deleteTracks(Shipment $orderShipment)
    {
        foreach ($orderShipment->getTracks() as $track) {
            $this->_shipmentTrackRepository->delete($track);
        }
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

            /** @var Order $order */
            foreach ($orderCollection->getItems() as $order) {
                $orderNumber = $order->getIncrementId();
                if (!in_array($order->getStatus(), ['pending', Config::SHIPMENT_CREATED_STATUS])) {
                    $this->messageManager->addErrorMessage(
                        sprintf('%s %s', __('Could not cancel label for order, wrong order status'), $order->getIncrementId())
                    );
                    continue;
                }
                if ($order->getLpShippingItemId()) {
                    if ($this->_apiHelper->cancelShipment($order)) {
                        $order->setStatus(Config::SHIPMENT_CANCELED);
                        $order->setLpUniqueId(uniqid('lp'));
                        $order->setLpShippingItemId(null);
                        $order->setLpReturnParcelId(null);
                        $order->setLpRequestId(null);
                        foreach ($order->getShipmentsCollection()->getFirstItem()->getTracks() as $track) {
                            $track->delete();
                        }

                        $this->_orderRepository->save($order);
                    } else {
                        $this->messageManager->addErrorMessage(
                            sprintf('%s %s', __('Could not cancel label for order'), $order->getIncrementId())
                        );
                    }
                }
            }

            $this->messageManager->addSuccessMessage(__('You have canceled the shipping labels.'));
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
