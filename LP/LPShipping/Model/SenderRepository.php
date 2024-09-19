<?php

namespace LP\LPShipping\Model;

use LP\LPShipping\Api\SenderRepositoryInterface;

class SenderRepository implements SenderRepositoryInterface
{
    /**
     * @var \LP\LPShipping\Model\SenderFactory $_senderFactory
     */
    protected $_senderFactory;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\Sender $_senderResource
     */
    protected $_senderResource;

    /**
     * SenderRepository constructor.
     * @param \LP\LPShipping\Model\SenderFactory $senderFactory
     * @param \LP\LPShipping\Model\ResourceModel\Sender $senderResource
     */
    public function __construct(
        \LP\LPShipping\Model\SenderFactory $senderFactory,
        \LP\LPShipping\Model\ResourceModel\Sender $senderResource
    ) {
        $this->_senderFactory   = $senderFactory;
        $this->_senderResource  = $senderResource;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $sender = $this->_senderFactory->create();
        $this->_senderResource->load($sender, $id);

        /** @var \LP\LPShipping\Model\Sender $sender */
        if (!$sender->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Unable to find Sender with ID "%1"', $id)
            );
        }

        return $sender;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        $sender = $this->_senderFactory->create();
        $this->_senderResource->load($sender, $orderId, 'order_id');

        return $sender;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\LP\LPShipping\Api\Data\SenderInterface $sender)
    {
        $this->_senderResource->save($sender);
        return $sender;
    }
}
