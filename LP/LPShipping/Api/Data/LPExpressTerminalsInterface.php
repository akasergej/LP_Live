<?php

namespace LP\LPShipping\Api\Data;

interface LPExpressTerminalsInterface
{
    const TERMINAL_ID   = 'terminal_id';
    const NAME          = 'name';
    const ADDRESS       = 'address';
    const CITY          = 'city';
    const COUNTRY_CODE  = 'country_code';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getTerminalId();

    /**
     * @param int $terminalId
     * @return int
     */
    public function setTerminalId($terminalId);

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
    public function getAddress();

    /**
     * @param string $address
     * @return string
     */
    public function setAddress($address);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $city
     * @return string
     */
    public function setCity($city);

    public function setCountryCode(string $countryCode);

    /**
     * @return string|null
     */
    public function getCountryCode();
}
