<?php

namespace LP\LPShipping\Model;

use LP\LPShipping\Api\Data\CN22Interface;
use Magento\Framework\Model\AbstractModel;

class CN22 extends AbstractModel implements CN22Interface
{
    public function _construct()
    {
        $this->_init(ResourceModel\CN22::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getParcelType()
    {
        return $this->getData(self::PARCEL_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setParcelType($parcelType)
    {
        return $this->setData(self::PARCEL_TYPE, $parcelType);
    }

    /**
     * @inheritDoc
     */
    public function getParcelDescription()
    {
        return $this->getData(self::PARCEL_DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setParcelDescription($parcelDescription)
    {
        return $this->setData(self::PARCEL_DESCRIPTION, $parcelDescription);
    }

    /**
     * @inheritDoc
     */
    public function getCnParts()
    {
        return $this->getData(self::CN_PARTS);
    }

    /**
     * @inheritDoc
     */
    public function setCnParts($cnParts)
    {
        return $this->setData(self::CN_PARTS, $cnParts);
    }
}
