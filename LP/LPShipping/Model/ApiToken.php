<?php

namespace LP\LPShipping\Model;

class ApiToken extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\LP\LPShipping\Model\ResourceModel\ApiToken::class);
    }
}
