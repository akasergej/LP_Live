<?php

namespace LP\LPShipping\Api;

interface CN23RepositoryInterface
{
    /**
     * @param int $id
     * @return \LP\LPShipping\Api\Data\CN23Interface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param int $orderId
     * @return \LP\LPShipping\Api\Data\CN23Interface|bool
     */
    public function getByOrderId($orderId);

    /**
     * @param \LP\LPShipping\Api\Data\CN23Interface $CN23
     * @return \LP\LPShipping\Api\Data\CN23Interface
     */
    public function save(\LP\LPShipping\Api\Data\CN23Interface $CN23);
}
