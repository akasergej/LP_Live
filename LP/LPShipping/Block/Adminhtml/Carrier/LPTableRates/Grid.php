<?php

namespace LP\LPShipping\Block\Adminhtml\Carrier\LPTableRates;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * @var \LP\LPShipping\Model\LPTableRates $_tablerate
     */
    protected $_tablerate;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\LPTableRates\CollectionFactory $_collectionFactory
     */
    protected $_collectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \LP\LPShipping\Model\ResourceModel\LPTableRates\CollectionFactory $collectionFactory
     * @param \LP\LPShipping\Model\LPTableRates $rates
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context                                $context,
        \Magento\Backend\Helper\Data                                           $backendHelper,
        \LP\LPShipping\Model\ResourceModel\LPTableRates\CollectionFactory $collectionFactory,
        \LP\LPShipping\Model\LPTableRates                                 $rates,
        array                                                                  $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_tablerate = $rates;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('lp_shippingTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param $websiteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();

        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteId()
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }

        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param string $name
     * @return $this
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;

        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        /** @var \LP\LPShipping\Model\ResourceModel\LPTableRates\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'country',
            [
                'header' => __('Country code'),
                'index' => 'country'
            ]
        );
        $this->addColumn(
            'weight_to',
            [
                'header' => __('Weight To'),
                'index' => 'weight_to'
            ]
        );
        $this->addColumn(
            'p2h_untracked_price',
            [
                'header' => __("Shipment from the post office to the receiver's address/post office pricing"),
                'index' => 'p2h_untracked_price'
            ]
        );
        $this->addColumn(
            'p2h_tracked_price',
            [
                'header' => __("Shipment from the post office to the receiver's address without a signature pricing"),
                'index' => 'p2h_tracked_price'
            ]
        );
        $this->addColumn(
            'p2h_signed_price',
            [
                'header' => __("Shipment from the post office to the receiver's address/post office with signature pricing"),
                'index' => 'p2h_signed_price'
            ]
        );

        return parent::_prepareColumns();
    }
}
