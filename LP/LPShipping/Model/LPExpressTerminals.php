<?php

namespace LP\LPShipping\Model;

class LPExpressTerminals extends \Magento\Framework\Model\AbstractModel implements \LP\LPShipping\Api\Data\LPExpressTerminalsInterface
{
    public function _construct()
    {
        $this->_init(
            \LP\LPShipping\Model\ResourceModel\LPExpressTerminals::class
        );
    }

    /**
     * @inheritDoc
     */
    public function getTerminalId()
    {
        return $this->getData(self::TERMINAL_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTerminalId($terminalId)
    {
        return $this->setData(self::TERMINAL_ID, $terminalId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    public function setCountryCode(string $countryCode)
    {
        return $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode()
    {
        return $this->getData(self::COUNTRY_CODE);
    }
}
