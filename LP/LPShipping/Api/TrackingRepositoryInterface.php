<?php

namespace LP\LPShipping\Api;

interface TrackingRepositoryInterface
{
    /**
     * @param int $id
     * @return \LP\LPShipping\Api\Data\TrackingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param string $trackingCode
     * @return \LP\LPShipping\Api\Data\TrackingInterface|bool
     */
    public function getByTrackingCode($trackingCode);

    /**
     * @param string $code
     * @return string
     */
    public function getEventDescriptionByCode($code);

    /**
     * @param \LP\LPShipping\Api\Data\TrackingInterface $tracking
     * @return \LP\LPShipping\Api\Data\TrackingInterface
     */
    public function save(\LP\LPShipping\Api\Data\TrackingInterface $tracking);
}
