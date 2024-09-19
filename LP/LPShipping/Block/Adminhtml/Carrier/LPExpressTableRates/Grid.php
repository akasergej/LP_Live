<?php

namespace LP\LPShipping\Block\Adminhtml\Carrier\LPExpressTableRates;

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
     * @var \LP\LPShipping\Model\LPExpressTableRates|\LP\LPShipping\Model\LPTableRates $_tablerate
     */
    protected $_tablerate;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\LPExpressTableRates\CollectionFactory $_collectionFactory
     */
    protected $_collectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \LP\LPShipping\Model\ResourceModel\LPExpressTableRates\CollectionFactory $collectionFactory
     * @param \LP\LPShipping\Model\LPExpressTableRates $rates
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context                                       $context,
        \Magento\Backend\Helper\Data                                                  $backendHelper,
        \LP\LPShipping\Model\ResourceModel\LPExpressTableRates\CollectionFactory $collectionFactory,
        \LP\LPShipping\Model\LPExpressTableRates                                 $rates,
        array                                                                         $data = []
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
        $this->setId('lpexpress_shippingTablerateGrid');
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
        /** @var \LP\LPShipping\Model\ResourceModel\LPExpressTableRates\Collection $collection */
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
            'h2h_hands_price',
            [
                'header' => __("Shipment from home/office delivered by UNISEND courier to the receiver's address pricing"),
                'index' => 'h2h_hands_price'
            ]
        );
        $this->addColumn(
            't2h_hands_price',
            [
                'header' => __("Shipment from UNISEND self-service terminal/locker, delivered by UNISEND courier to the receiver's address pricing"),
                'index' => 't2h_hands_price'
            ]
        );
        $this->addColumn(
            't2t_terminal_price',
            [
                'header' => __("Shipment from and to UNISEND self-service terminal/ locker pricing"),
                'index' => 't2t_terminal_price'
            ]
        );
        $this->addColumn(
            'h2t_terminal_price',
            [
                'header' => __("Shipment from home/office delivered to UNISEND self-service terminal/ locker pricing"),
                'index' => 'h2t_terminal_price'
            ]
        );
        $this->addColumn(
            't2s_terminal_price',
            [
                'header' => __("Shipment from and to UNISEND self-service terminal/ locker within 72 hours pricing"),
                'index' => 't2s_terminal_price'
            ]
        );
        $this->addColumn(
            'h2p_tracked_signed_price',
            [
                'header' => __("Shipment from home/office delivered to the receiver's post office pricing"),
                'index' => 'h2p_tracked_signed_price',
            ]
        );

        return parent::_prepareColumns();
    }
}
