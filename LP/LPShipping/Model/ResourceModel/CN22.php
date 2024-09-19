<?php

namespace LP\LPShipping\Model\ResourceModel;

class CN22 extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lp_cn22_form_data', 'id');
    }
}
