<?php

namespace LP\LPShipping\Model\ResourceModel\Tracking;

class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string $_idFieldName
     */
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init(
            \LP\LPShipping\Model\Tracking::class,
            \LP\LPShipping\Model\ResourceModel\Tracking::class
        );
    }
}
