<?php

declare(strict_types=1);

namespace LP\LPShipping\Cron;

use LP\LPShipping\Helper\ApiHelper;
use LP\LPShipping\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class CallCourier
{
    /**
     * @var ApiHelper $apiHelper
     */
    protected $apiHelper;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param ApiHelper $apiHelper
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ApiHelper $apiHelper,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->apiHelper = $apiHelper;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->config->getCallCourierAutomatically() || !$this->canCallCourier()) {
            return;
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('status', Config::SHIPMENT_CREATED_STATUS, 'in')
            ->addFilter('shipping_method', Config::getMethodsRequiringCourierCall(), 'in')
            ->create();
        $orders = $this->orderRepository->getList($criteria)->getItems();
        $parcelIds = [];
        foreach ($orders as $order) {
            $parcelIds[] = $order->getLpShippingItemId();
        }
        if (!empty($parcelIds)) {
            $this->apiHelper->callCourier($parcelIds);
        }

        /** @var Order $order */
        foreach ($orders as $order) {
            $order->setStatus(Config::COURIER_CALLED_STATUS);

            $this->orderRepository->save($order);
        }

        $this->rescheduleCallCourier();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function canCallCourier(): bool
    {
        $storeTimeZone = new \DateTimeZone($this->config->getTimeZone());
        $callCourierExecutionTime = $this->config->getCallCourierExecutionTime();

        $callCourierExecutionTime = $callCourierExecutionTime
            ? new \DateTime($callCourierExecutionTime, $storeTimeZone)
            : $this->getCallCourierScheduledTime();

        return $callCourierExecutionTime < new \DateTime('now', $storeTimeZone);
    }

    public function getCallCourierScheduledTime(string $time = null): \DateTime
    {
        $storeTimeZone = new \DateTimeZone($this->config->getTimeZone());
        $courierArrivalTime = $time ?: str_replace(',', ':', $this->config->getCourierArrivalTime());

        return new \DateTime($courierArrivalTime, $storeTimeZone);
    }

    public function rescheduleCallCourier(\DateTime $scheduledTime = null)
    {
        $scheduledDateToWorkOn = $scheduledTime ?: $this->getCallCourierScheduledTime();
        $nextCall = $scheduledDateToWorkOn->modify('+1 day');

        $this->config->setCallCourierExecutionTime($nextCall->format('Y-m-d H:i:s'));
    }
}
