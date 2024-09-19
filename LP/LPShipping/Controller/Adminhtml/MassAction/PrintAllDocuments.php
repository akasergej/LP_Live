<?php

namespace LP\LPShipping\Controller\Adminhtml\MassAction;

use LP\LPShipping\Helper\ApiHelper;
use LP\LPShipping\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class PrintAllDocuments extends Action
{
    /**
     * @var Filter $_filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory $_orderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * @var FileFactory $_fileFactory
     */
    protected $_fileFactory;

    /**
     * @var RawFactory $_resultRawFactory
     */
    protected $_resultRawFactory;

    /**
     * PrintManifests constructor.
     * @param Config $config
     * @param ApiHelper $apiHelper
     * @param Filter $filter
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param Context $context
     */
    public function __construct(
        Config $config,
        ApiHelper $apiHelper,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        Context $context
    ) {
        $this->_filter                  = $filter;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_config                  = $config;
        $this->_apiHelper               = $apiHelper;
        $this->_orderRepository         = $orderRepository;
        $this->_fileFactory             = $fileFactory;
        $this->_resultRawFactory        = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $orderNumber = null;
        try {
            $orderCollection = $this->_filter->getCollection($this->_orderCollectionFactory->create());
            $merged = new \Zend_Pdf();

            /** @var Order $order */
            foreach ($orderCollection->getItems() as $order) {
                $orderNumber = $order->getIncrementId();

                try {
                    $stickerData = $this->_apiHelper->createSticker([$order->getLpShippingItemId()]);
                } catch (\Throwable $e) {
                    $stickerData = null;
                }
                if ($stickerData) {
                    $loadDoc = new \Zend_Pdf($stickerData, null, false);

                    foreach ($loadDoc->pages as $page) {
                        $clonedPage = clone $page;
                        $merged->pages[] = $clonedPage;
                    }
                }

                try {
                    $manifestData = $this->_apiHelper->getManifest($order->getLpShippingItemId());
                } catch (\Throwable $e) {
                    $manifestData = null;
                }
                if ($manifestData) {
                    $loadDoc = new \Zend_Pdf($manifestData, null, false);

                    foreach ($loadDoc->pages as $page) {
                        $clonedPage = clone $page;
                        $merged->pages[] = $clonedPage;
                    }
                }
                try {
                    $CnData = $this->_apiHelper->getCnForm([$order->getLpShippingItemId()]);
                } catch (\Throwable $e) {
                    $CnData = null;
                }
                if ($CnData) {
                    $loadDoc = new \Zend_Pdf($CnData, null, false);

                    foreach ($loadDoc->pages as $page) {
                        $clonedPage = clone $page;
                        $merged->pages[] = $clonedPage;
                    }
                }
            }

            $this->_fileFactory->create(
                sprintf('LP_Documents_%s.pdf', date('Ymd')),
                $merged->render(),
                DirectoryList::VAR_DIR,
                'application/pdf',
                ''
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                sprintf('%s: %s',
                    $orderNumber,
                    __($e->getMessage())
                )
            );

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        return $this->_resultRawFactory->create();
    }
}
