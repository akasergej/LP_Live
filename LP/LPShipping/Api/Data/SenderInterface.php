<?php

namespace LP\LPShipping\Api\Data;

interface SenderInterface
{
    const ORDER_ID          = 'order_id';
    const NAME              = 'name';
    const PHONE             = 'phone';
    const EMAIL             = 'email';
    const COUNTRY_ID        = 'country_id';
    const CITY              = 'city';
    const STREET            = 'street';
    const BUILDING_NUMBER   = 'building_number';
    const APARTMENT         = 'apartment';
    const POSTCODE          = 'postcode';
    const ADDRESS_LINE_1          = 'address_line_1';
    const ADDRESS_LINE_2          = 'address_line_2';

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
    public function getName();

    /**
     * @param string $name
     * @return string
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @param string $phone
     * @return string
     */
    public function setPhone($phone);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return string
     */
    public function setEmail($email);

    /**
     * @return int
     */
    public function getCountryId();

    /**
     * @param int $countryId
     * @return int
     */
    public function setCountryId($countryId);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $city
     * @return string
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @param string $street
     * @return string
     */
    public function setStreet($street);

    /**
     * @return string
     */
    public function getBuildingNumber();

    /**
     * @param string $buildingNumber
     * @return string
     */
    public function setBuildingNumber($buildingNumber);

    /**
     * @return string
     */
    public function getApartment();

    /**
     * @param string $apartment
     * @return string
     */
    public function setApartment($apartment);

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @param string $postcode
     * @return string
     */
    public function setPostcode($postcode);

    /**
     * @return string
     */
    public function getAddressLine1();

    /**
     * @param string $val
     * @return string
     */
    public function setAddressLine1($val);

    /**
     * @return string
     */
    public function getAddressLine2();

    /**
     * @param string $val
     * @return string
     */
    public function setAddressLine2($val);
}
