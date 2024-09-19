<?php

namespace LP\LPShipping\Model;

class CN22Repository implements \LP\LPShipping\Api\CN22RepositoryInterface
{
    /**
     * @var \LP\LPShipping\Model\CN22Factory $_CN22Factory
     */
    private $_CN22Factory;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\CN22 $_CN22Resource
     */
    private $_CN22Resource;

    /**
     * CN22Repository constructor.
     * @param \LP\LPShipping\Model\CN22Factory $CN22Factory
     * @param \LP\LPShipping\Model\ResourceModel\CN22 $CN22Resource
     */
    public function __construct(
        \LP\LPShipping\Model\CN22Factory $CN22Factory,
        \LP\LPShipping\Model\ResourceModel\CN22 $CN22Resource
    ) {
        $this->_CN22Factory = $CN22Factory;
        $this->_CN22Resource = $CN22Resource;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $CN22 = $this->_CN22Factory->create();
        $this->_CN22Resource->load($CN22, $id);

        /** @var \LP\LPShipping\Model\CN22 $CN22 */
        if (!$CN22->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Unable to find CN22 with ID "%1"', $id)
            );
        }

        return $CN22;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        $CN22 = $this->_CN22Factory->create();
        $this->_CN22Resource->load($CN22, $orderId, 'order_id');

        return $CN22;
    }

    /**
     * @param \LP\LPShipping\Api\Data\CN22Interface $CN22
     * @return \LP\LPShipping\Api\Data\CN22Interface|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\LP\LPShipping\Api\Data\CN22Interface $CN22)
    {
        $this->_CN22Resource->save($CN22);
        return $CN22;
    }
}
