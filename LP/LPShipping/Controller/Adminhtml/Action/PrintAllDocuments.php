<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Helper\ApiHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class PrintAllDocuments extends Action
{
    /**
     * @var ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var RawFactory $_resultRawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var FileFactory $_fileFactory
     */
    protected $_fileFactory;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * PrintAllDocuments constructor.
     * @param ApiHelper $apiHelper
     * @param RawFactory $resultRawFactory
     * @param FileFactory $fileFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        ApiHelper $apiHelper,
        RawFactory $resultRawFactory,
        FileFactory $fileFactory,
        OrderRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->_apiHelper           = $apiHelper;
        $this->_resultRawFactory    = $resultRawFactory;
        $this->_fileFactory         = $fileFactory;
        $this->_orderRepository     = $orderRepository;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $merged = new \Zend_Pdf();

        /** @var Order $order */
        $order = $this->_orderRepository->get($this->getRequest()->getParam('order'));

        // Sticker
        try {
            if ($stickerData = $this->_apiHelper->createSticker([$order->getLpShippingItemId()])) {
                $loadDoc = new \Zend_Pdf($stickerData, null, false);

                foreach ($loadDoc->pages as $page) {
                    $clonedPage = clone $page;
                    $merged->pages [] = $clonedPage;
                }
            }
        } catch (\Exception $e) {
        }

        // Manifest
        try {
            if ($manifestData = $this->_apiHelper->getManifest($order->getLpShippingItemId())) {
                $loadDoc = new \Zend_Pdf($manifestData, null, false);

                foreach ($loadDoc->pages as $page) {
                    $clonedPage = clone $page;
                    $merged->pages [] = $clonedPage;
                }
            }
        } catch (\Exception $e) {
        }

        // CNForm
        try {
            if ($CnData = $this->_apiHelper->getCnForm([$order->getLpShippingItemId()])) {
                $loadDoc = new \Zend_Pdf($CnData, null, false);

                foreach ($loadDoc->pages as $page) {
                    $clonedPage = clone $page;
                    $merged->pages [] = $clonedPage;
                }
            }
        } catch (\Exception $e) {
        }

        $this->_fileFactory->create(
            sprintf('LP_Documents_%s_%s.pdf', $order->getIncrementId(), date('Ymd')),
            $merged->render(),
            DirectoryList::VAR_DIR,
            'application/pdf',
            ''
        );
    }
}
