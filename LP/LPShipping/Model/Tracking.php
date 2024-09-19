<?php

namespace LP\LPShipping\Model;

class Tracking extends \Magento\Framework\Model\AbstractModel implements \LP\LPShipping\Api\Data\TrackingInterface
{
    public function _construct()
    {
        $this->_init(\LP\LPShipping\Model\ResourceModel\Tracking::class);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingCode()
    {
        return $this->getData(self::TRACKING_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setTrackingCode($trackingCode)
    {
        return $this->setData(self::TRACKING_CODE, $trackingCode);
    }

    /**
     * @inheritDoc
     */
    public function getStateCode()
    {
        return $this->getData(self::STATE);
    }

    /**
     * @inheritDoc
     */
    public function setStateCode($stateCode)
    {
        return $this->setData(self::STATE, $stateCode);
    }

    /**
     * @inheritDoc
     */
    public function getEvents()
    {
        return $this->getData(self::EVENTS);
    }

    /**
     * @inheritDoc
     */
    public function setEvents($events)
    {
        return $this->setData(self::EVENTS, $events);
    }
}
