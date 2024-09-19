<?php

namespace LP\LPShipping\Model;

class CN23Repository implements \LP\LPShipping\Api\CN23RepositoryInterface
{
    /**
     * @var \LP\LPShipping\Model\CN23Factory $_CN23Factory
     */
    protected $_CN23Factory;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\CN23
     */
    protected $_CN23Resource;

    /**
     * CN23Repository constructor.
     * @param CN23Factory $CN23Factory
     * @param ResourceModel\CN23 $CN23Resource
     */
    public function __construct(
        \LP\LPShipping\Model\CN23Factory $CN23Factory,
        \LP\LPShipping\Model\ResourceModel\CN23 $CN23Resource
    ) {
        $this->_CN23Factory = $CN23Factory;
        $this->_CN23Resource = $CN23Resource;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $CN23 = $this->_CN23Factory->create();
        $this->_CN23Resource->load($CN23, $id);

        /** @var \LP\LPShipping\Model\CN23 $CN23 */
        if (!$CN23->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Unable to find CN23 with ID "%1"', $id)
            );
        }

        return $CN23;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        $CN23 = $this->_CN23Factory->create();
        $this->_CN23Resource->load($CN23, $orderId, 'order_id');

        return $CN23;
    }

    /**
     * @param \LP\LPShipping\Api\Data\CN23Interface $CN23
     * @return \LP\LPShipping\Api\Data\CN23Interface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\LP\LPShipping\Api\Data\CN23Interface $CN23)
    {
        /** @var \LP\LPShipping\Model\CN23 $CN23 */
        $this->_CN23Resource->save($CN23);
        return $CN23;
    }
}
