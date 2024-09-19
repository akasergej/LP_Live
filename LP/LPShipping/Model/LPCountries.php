<?php

namespace LP\LPShipping\Model;

class LPCountries extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \LP\LPShipping\Model\ResourceModel\LPCountries::class
        );
    }
}
