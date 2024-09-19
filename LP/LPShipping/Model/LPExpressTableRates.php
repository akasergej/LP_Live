<?php

namespace LP\LPShipping\Model;

use Magento\Framework\Model\AbstractModel;

class LPExpressTableRates extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\LPExpressTableRates::class);
    }

    public function getWeightTo()
    {
        return $this->getData('weight_to');
    }

    public function getH2hHandsPrice()
    {
        return $this->getData('h2h_hands_price');
    }

    public function getT2hHandsPrice()
    {
        return $this->getData('t2h_hands_price');
    }

    public function getT2tTerminalPrice()
    {
        return $this->getData('t2t_terminal_price');
    }

    public function getH2tTerminalPrice()
    {
        return $this->getData('h2t_terminal_price');
    }

    public function getT2sTerminalPrice()
    {
        return $this->getData('t2s_terminal_price');
    }

    public function getH2pTrackedSignedPrice()
    {
        return $this->getData('h2p_tracked_signed_price');
    }

    public function setWeightTo($data)
    {
        return $this->setData('weight_to', $data);
    }

    public function setH2hHandsPrice($data)
    {
        return $this->setData('h2h_hands_price', $data);
    }

    public function setT2hHandsPrice($data)
    {
        return $this->setData('t2h_hands_price', $data);
    }

    public function setT2tTerminalPrice($data)
    {
        return $this->setData('t2t_terminal_price', $data);
    }

    public function setH2tTerminalPrice($data)
    {
        return $this->setData('h2t_terminal_price', $data);
    }

    public function setT2sTerminalPrice($data)
    {
        return $this->setData('t2s_terminal_price', $data);
    }

    public function setH2pTrackedSignedPrice($data)
    {
        return $this->setData('h2p_tracked_signed_price', $data);
    }

    public function setCountry($data)
    {
        return $this->setData('country', $data);
    }
}
