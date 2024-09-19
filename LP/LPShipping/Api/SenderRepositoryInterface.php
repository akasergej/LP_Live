<?php

namespace LP\LPShipping\Api;

interface SenderRepositoryInterface
{
    /**
     * @param int $id
     * @return \LP\LPShipping\Api\Data\SenderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param int $orderId
     * @return \LP\LPShipping\Api\Data\SenderInterface
     */
    public function getByOrderId($orderId);

    /**
     * @param Data\SenderInterface $sender
     * @return \LP\LPShipping\Api\Data\SenderInterface
     */
    public function save(\LP\LPShipping\Api\Data\SenderInterface $sender);
}
