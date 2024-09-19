<?php

namespace LP\LPShipping\Model\ResourceModel;

class LPExpressTerminals extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lpexpress_terminal_list', 'id');
    }
}
