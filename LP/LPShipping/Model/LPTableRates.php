<?php

namespace LP\LPShipping\Model;

use Magento\Framework\Model\AbstractModel;

class LPTableRates extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\LPTableRates::class);
    }

    public function setWeightTo($data)
    {
        return $this->setData('weight_to', $data);
    }

    public function setP2hUntrackedPrice($data)
    {
        return $this->setData('p2h_untracked_price', $data);
    }

    public function setP2hTrackedPrice($data)
    {
        return $this->setData('p2h_tracked_price', $data);
    }

    public function setP2hSignedPrice($data)
    {
        return $this->setData('p2h_signed_price', $data);
    }

    public function getWeightTo()
    {
        return $this->getData('weight_to');
    }

    public function getP2hUntrackedPrice()
    {
        return $this->getData('p2h_untracked_price');
    }

    public function getP2hTrackedPrice()
    {
        return $this->getData('p2h_tracked_price');
    }

    public function getP2hSignedPrice()
    {
        return $this->getData('p2h_signed_price');
    }

    public function setCountry($data)
    {
        return $this->setData('country', $data);
    }
}
