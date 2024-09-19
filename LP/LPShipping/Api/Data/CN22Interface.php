<?php

namespace LP\LPShipping\Api\Data;

interface CN22Interface
{
    const ORDER_ID              = 'order_id';
    const PARCEL_TYPE           = 'parcel_type';
    const PARCEL_DESCRIPTION    = 'parcel_description';
    const CN_PARTS              = 'cn_parts';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return int
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return int
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getParcelType();

    /**
     * @param string $parcelType
     * @return string
     */
    public function setParcelType($parcelType);

    /**
     * @return string
     */
    public function getParcelDescription();

    /**
     * @param string $parcelDescription
     * @return string
     */
    public function setParcelDescription($parcelDescription);

    /**
     * @return string
     */
    public function getCnParts();

    /**
     * @param string $cnParts
     * @return string
     */
    public function setCnParts($cnParts);
}
