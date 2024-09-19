<?php

namespace LP\LPShipping\Model\ResourceModel;

class ApiToken extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lp_api_token', 'id');
    }
}
