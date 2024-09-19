<?php

namespace LP\LPShipping\Model\ResourceModel;

class LPCountries extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lp_country_list', 'id');
    }
}
