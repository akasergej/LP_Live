<?php

namespace LP\LPShipping\Model\Config\Backend\LPTableRates;

use Magento\Framework\App\Config\Value;

class Rates extends Value
{
    private $_LPTableRatesFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \LP\LPShipping\Model\ResourceModel\LPTableRatesFactory $LPTableRatesFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_LPTableRatesFactory = $LPTableRatesFactory;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * After config save information call resource model
     * to save price vs weight table
     *
     * @return Value
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        /** @var \LP\LPShipping\Model\ResourceModel\LPTableRates $LPRates */
        $LPRates = $this->_LPTableRatesFactory->create();
        $LPRates->uploadAndImport();

        return parent::afterSave();
    }
}
