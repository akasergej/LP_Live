<?php

namespace LP\LPShipping\Model\Config\Backend\LPExpressTableRates;

class Rates extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \LP\LPShipping\Model\ResourceModel\LPExpressTableRatesFactory $_LPExpressTableRatesFactory
     */
    private $_LPExpressTableRatesFactory;

    /**
     * Rates constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \LP\LPShipping\Model\ResourceModel\LPExpressTableRatesFactory $LPExpressTableRatesFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \LP\LPShipping\Model\ResourceModel\LPExpressTableRatesFactory $LPExpressTableRatesFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_LPExpressTableRatesFactory = $LPExpressTableRatesFactory;

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
     * @return \Magento\Framework\App\Config\Value
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        /** @var \LP\LPShipping\Model\ResourceModel\LPExpressTableRates $LPExpressRates */
        $LPExpressRates = $this->_LPExpressTableRatesFactory->create();
        $LPExpressRates->uploadAndImport($this);

        return parent::afterSave();
    }
}
