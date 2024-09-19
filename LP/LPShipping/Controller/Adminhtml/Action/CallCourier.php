<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

class CallCourier extends \Magento\Backend\App\Action
{
    /**
     * @var \LP\LPShipping\Helper\ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var \LP\LPShipping\Model\Config $_config
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * CallCourier constructor.
     * @param \LP\LPShipping\Helper\ApiHelper $apiHelper
     * @param \LP\LPShipping\Model\Config $config
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \LP\LPShipping\Helper\ApiHelper $apiHelper,
        \LP\LPShipping\Model\Config $config,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_apiHelper           = $apiHelper;
        $this->_config              = $config;
        $this->_orderRepository     = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->_apiHelper->callCourier([ $this->getRequest()->getParam('itemid') ])) {
            $this->messageManager->addErrorMessage(__('Could not call courier'));
        } else {
            // Change order status
            $order = $this->_orderRepository->get($this->getRequest()->getParam('orderid'));
            $order->setStatus(
                $this->_config::COURIER_CALLED_STATUS
            );
            $this->_orderRepository->save($order);
            $this->messageManager->addSuccessMessage(__('Courier called successfully.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        return $resultRedirect;
    }
}
