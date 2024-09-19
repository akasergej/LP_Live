<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Api\CN22RepositoryInterface;
use LP\LPShipping\Api\Data\CN22InterfaceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class SaveCN22Form extends Action
{
    /**
     * @var ManagerInterface $_messageManger
     */
    protected $_messageManger;

    /**
     * @var CN22InterfaceFactory $_CN22Factory
     */
    protected $_CN22Factory;

    /**
     * @var CN22RepositoryInterface $_CN22Repository
     */
    protected $_CN22Repository;

    /**
     * @param Context $context
     * @param ManagerInterface $messageManger
     * @param CN22InterfaceFactory $CN22Factory
     * @param CN22RepositoryInterface $CN22Repository
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManger,
        CN22InterfaceFactory $CN22Factory,
        CN22RepositoryInterface $CN22Repository
    ) {
        $this->_messageManger = $messageManger;
        $this->_CN22Factory = $CN22Factory;
        $this->_CN22Repository = $CN22Repository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $CN22FormData = $this->getRequest()->getParams();

        if ($orderId = $CN22FormData['order']) {
            // Check if record exists
            $CN22 = $this->_CN22Repository->getByOrderId($orderId);
            if (!$CN22) {
                $CN22 = $this->_CN22Factory->create();
            }

            $CN22->setOrderId($orderId);
            $CN22->setParcelType($CN22FormData['parcel_type']);
            $CN22->setParcelDescription($CN22FormData['parcel_description']);
            $CN22->setCnParts(json_encode($CN22FormData['items']));

            $this->_CN22Repository->save($CN22);

            $this->_messageManger->addSuccessMessage(
                __('CN22 form saved successfully. Now you can create your shipping label.')
            );
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
