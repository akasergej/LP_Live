<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Helper\ApiHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Sales\Model\Order;

class PrintCN23Form extends Action
{
    /**
     * @var RawFactory $_resultRawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var FileFactory $_fileFactory
     */
    protected $_fileFactory;

    /**
     * @var ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * LpPrintCN23Form constructor.
     * @param RawFactory $resultRawFactory
     * @param FileFactory $fileFactory
     * @param Context $context
     * @param ApiHelper $apiHelper
     */
    public function __construct(
        RawFactory $resultRawFactory,
        FileFactory $fileFactory,
        Context $context,
        ApiHelper $apiHelper
    ) {
        parent::__construct($context);
        $this->_resultRawFactory = $resultRawFactory;
        $this->_fileFactory = $fileFactory;
        $this->_apiHelper = $apiHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $objectManager = ObjectManager::getInstance();
            $orderRepository = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');

            /** @var Order $order */
            $order = $orderRepository->get($this->getRequest()->getParam('order'));

            $fileName = sprintf('LP_CN23_Form_%s.pdf', $order->getIncrementId());

            if ($CnData = $this->_apiHelper->getCnForm([$order->getLpShippingItemId()])) {
                $this->_fileFactory->create(
                    $fileName,
                    $CnData,
                    DirectoryList::VAR_DIR,
                    'application/pdf',
                    ''
                );
            } else {
                return $this->_redirect($this->_redirect->getRefererUrl());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        return $this->_resultRawFactory->create();
    }
}
