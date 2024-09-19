<?php

namespace LP\LPShipping\Api;

interface CN22RepositoryInterface
{
    /**
     * @param $id
     * @return \LP\LPShipping\Api\Data\CN22Interface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param $orderId
     * @return \LP\LPShipping\Api\Data\CN22Interface|bool
     */
    public function getByOrderId($orderId);

    /**
     * @param Data\CN22Interface $CN22
     * @return \LP\LPShipping\Api\Data\CN22Interface
     */
    public function save(\LP\LPShipping\Api\Data\CN22Interface $CN22);
}
