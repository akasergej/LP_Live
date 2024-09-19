<?php

namespace LP\LPShipping\Model\ResourceModel;

class Tracking extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lp_tracking_events', 'id');
    }
}
