<?php

namespace LP\LPShipping\Model\ResourceModel;

class CN23 extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lp_cn23_form_data', 'id');
    }
}
