<?php

namespace LP\LPShipping\Model\ResourceModel\LPExpressTerminals;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Identification field
     *
     * @var string $_idFieldName
     */
    protected $_idFieldName = 'id';

    /**
     * Connect model with resource model
     */
    public function _construct()
    {
        $this->_init(
            \LP\LPShipping\Model\LPExpressTerminals::class,
            \LP\LPShipping\Model\ResourceModel\LPExpressTerminals::class
        );
    }
}
