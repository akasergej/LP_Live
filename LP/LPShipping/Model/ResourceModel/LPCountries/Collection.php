<?php

namespace LP\LPShipping\Model\ResourceModel\LPCountries;

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
    protected function _construct()
    {
        $this->_init(
            \LP\LPShipping\Model\LPCountries::class,
            \LP\LPShipping\Model\ResourceModel\LPCountries::class
        );
    }
}
