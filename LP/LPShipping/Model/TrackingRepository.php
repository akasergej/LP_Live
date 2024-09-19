<?php

namespace LP\LPShipping\Model;

class TrackingRepository implements \LP\LPShipping\Api\TrackingRepositoryInterface
{
    /**
     * @var \LP\LPShipping\Model\TrackingFactory $_trackingFactory
     */
    protected $_trackingFactory;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\Tracking $_trackingResource
     */
    protected $_trackingResource;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\Tracking\CollectionFactory $_trackingCollectionFactory
     */
    protected $_trackingCollectionFactory;

    /**
     * @var array
     */
    protected $_statusRepository;

    /**
     * TrackingRepository constructor.
     * @param \LP\LPShipping\Model\TrackingFactory  $trackingFactory
     * @param \LP\LPShipping\Model\ResourceModel\Tracking $trackingResource
     * @param \LP\LPShipping\Model\ResourceModel\Tracking\CollectionFactory $trackingCollectionFactory
     */
    public function __construct(
        \LP\LPShipping\Model\TrackingFactory $trackingFactory,
        \LP\LPShipping\Model\ResourceModel\Tracking $trackingResource,
        \LP\LPShipping\Model\ResourceModel\Tracking\CollectionFactory $trackingCollectionFactory
    ) {
        $this->_trackingFactory = $trackingFactory;
        $this->_trackingResource = $trackingResource;
        $this->_trackingCollectionFactory = $trackingCollectionFactory;

        $this->_statusRepository = [
            'ACCEPTED' => __('Accepted'),
            'BP_TERMINAL_REQUEST_ACCEPTED' => __('LP express terminal request accepted'),
            'BP_TERMINAL_REQUEST_FAILED' => __('LP express terminal request failed'),
            'BP_TERMINAL_REQUEST_REJECTEDACCEPTED' => __('LP express terminal request rejected'),
            'BP_TERMINAL_REQUEST_SENT' => __('BP terminal request submitted'),
            'CANCELLED' => __('Canceled'),
            'DA_ACCEPTED_LP' => __('The parcel was taken from the Lithuanian Post Office'),
            'DA_ACCEPTED' => __('Parcel accepted from sender'),
            'DA_DELIVERED_LP' => __('The parcel was delivered to the Lithuanian Post Office'),
            'DA_DELIVERED' => __('The shipment has been delivered to the receiver'),
            'DA_DELIVERY_FAILED' => __('Delivery failed'),
            'DA_EXPORTED' => __('Item shipped from Lithuania'),
            'DA_PASSED_FOR_DELIVERY' => __('The parcel has been handed over to the courier for delivery'),
            'DA_RETURNED' => __('Shipment returned'),
            'DA_RETURNING' => __('The parcel is being returned'),
            'DEAD' => __('The itemt was delivered for destruction'),
            'DELIVERED' => __('Delivered'),
            'DEP_RECEIVED' => __('Item accepted at distribution center'),
            'DEP_SENT' => __('The item shall be transported to another distribution center'),
            'DESTROYED' => __('Destroyed'),
            'DISAPPEARED' => __('It\'s gone'),
            'EDA' => __('The shipment was detained at the distribution center of the recipient country'),
            'EDB' => __('The item has been presented to the customs authorities of the country of destination'),
            'EDC' => __('The consignment is subject to customs controls in the country of destination'),
            'EDD' => __('Consignment at the distribution center in the country of destination'),
            'EDE' => __('The shipment was sent from a distribution center in the recipient country'),
            'EDF' => __('The shipment is on hold in the recipient\'s post office'),
            'EDG' => __('The shipment has been delivered for delivery'),
            'EDH' => __('The shipment was delivered to the place of collection'),
            'EMA' => __('Consignment accepted from sender'),
            'EMB' => __('Consignment at distribution center'),
            'EMC' => __('Consignment shipped from Lithuania'),
            'EMD' => __('Consignment in the country of destination'),
            'EME' => __('Consignment at the customs office of destination'),
            'EMF' => __('The shipment was sent to the recipient\'s post office'),
            'EMG' => __('Parcel at the recipient\'s post office'),
            'EMH' => __('Attempt to deliver failed'),
            'EMI' => __('The shipment has been delivered to the consignee'),
            'EXA' => __('The consignment has been presented to the customs authorities of the country of departure'),
            'EXB' => __('The consignment was detained at the office of departure'),
            'EXC' => __('The consignment has been checked at the customs office of dispatch'),
            'EXD' => __('Thei tem is detained at the dispatch center of the country of dispatch'),
            'EXPORTED' => __('Exported'),
            'EXX' => __('The shipment has been canceled from the sender\'s country'),
            'FETCHCODE' => __('The shipment was delivered to the parcel self-service terminal'),
            'HANDED_IN_BKIS' => __('Served (BKIS)'),
            'HANDED_IN_POST' => __('Served at the post office'),
            'HANDED_TO_GOVERNMENT' => __('Transferred to the State'),
            'IMPLICATED' => __('Included'),
            'INFORMED' => __('Receipt message'),
            'LABEL_CANCELLED' => __('Delivery tag canceled'),
            'LABEL_CREATED' => __('Delivery tag created'),
            'LP_DELIVERY_FAILED' => __('Delivery failed'),
            'LP_RECEIVED' => __('The parcel was received at the Lithuanian Post Office'),
            'NOT_INCLUDED' => __('Not included'),
            'NOT_SET' => __('Unknown'),
            'ON_HOLD' => __('Detained'),
            'PARCEL_DELIVERED' => __('The shipment was delivered to the parcel self-service terminal'),
            'PARCEL_DEMAND' => __('Secure on demand'),
            'PARCEL_DETAINED' => __('Detained'),
            'PARCEL_DROPPED' => __('The shipment is placed in the parcel self-service terminal for shipment'),
            'PARCEL_LOST' => __('The shipment is gone'),
            'PARCEL_PICKED_UP_AT_LP' => __('The shipment has been delivered to the receiver'),
            'PARCEL_PICKED_UP_BY_DELIVERYAGENT' => __('The parcel is taken by courier from the parcel self-service terminal'),
            'PARCEL_PICKED_UP_BY_RECIPIENT' => __('The shipment has been withdrawn by the receiver'),
            'RECEIVED_FROM_ANY_POST' => __('Received'),
            'RECEIVED' => __('Received'),
            'REDIRECTED_AT_HOME' => __('Forwarded'),
            'REDIRECTED_IN_POST' => __('Forwarded in post office'),
            'REDIRECTED' => __('Forwarded-Served'),
            'REDIRECTING' => __('Forwarding started'),
            'REFUND_AT_HOME' => __('Refunded'),
            'REFUNDED_IN_POST' => __('Returned to post office'),
            'REFUNDED' => __('Refunded'),
            'REFUNDING' => __('Return started'),
            'SENT' => __('Sent'),
            'STORING' => __('Transferred to storage'),
            'TRANSFERRED_FOR_DELIVERY' => __('Passed on for deliver'),
            'UNDELIVERED' => __('Not delivered'),
            'UNSUCCESSFUL_DELIVERY' => __('Delivery failed')
        ];
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $track = $this->_trackingFactory->create();
        $this->_trackingResource->load($track, $id);

        /** @var \LP\LPShipping\Model\Tracking $CN23 */
        if (!$track->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Unable to find track with ID "%1"', $id)
            );
        }

        return $track;
    }

    /**
     * @inheritDoc
     */
    public function getByTrackingCode($trackingCode)
    {
        return $this->_trackingCollectionFactory->create()
            ->addFieldToFilter('tracking_code', [ 'eq' => $trackingCode ])
            ->getFirstItem();
    }

    /**
     * @inheritDoc
     */
    public function getEventDescriptionByCode($code)
    {
        if (key_exists($code, $this->_statusRepository)) {
            return $this->_statusRepository [ $code ];
        }

        return $code;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\LP\LPShipping\Api\Data\TrackingInterface $tracking)
    {
        /** @var \LP\LPShipping\Model\Tracking $tracking */
        $this->_trackingResource->save($tracking);
        return $tracking;
    }
}
