<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Helper\ApiHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class PrintManifest extends Action
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
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * PrintManifest constructor.
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
        $this->_resultRawFactory = $resultRawFactory;
        $this->_fileFactory = $fileFactory;
        $this->_apiHelper = $apiHelper;
        $this->_orderRepository = $orderRepository;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $order = $this->_orderRepository->get($this->getRequest()->getParam('order'));
        if ($manifestData = $this->_apiHelper->getManifest($order->getLpShippingItemId())) {
            $order->setLpManifestCreated(date('Y-m-d H:i:s'));
            $this->_fileFactory->create(
                sprintf('LP_Manifest_%s.pdf', $order->getIncrementId()),
                $manifestData,
                DirectoryList::VAR_DIR,
                'application/pdf',
                ''
            );

            $this->_orderRepository->save($order);
        } else {
            $this->messageManager->addErrorMessage(
                'Manifest does not exist.'
            );

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        return $this->_resultRawFactory->create();
    }
}
