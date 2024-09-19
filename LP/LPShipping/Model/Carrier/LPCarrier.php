<?php

namespace LP\LPShipping\Model\Carrier;

use LP\LPShipping\Helper\ShippingHelper;
use LP\LPShipping\Model\Config;
use LP\LPShipping\Model\Config\Source\LPDeliveryMethods;
use LP\LPShipping\Model\Config\Source\LPExpressDeliveryMethods;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Xml\Security;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Store\Api\Data\StoreInterface;

class LPCarrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'lpcarrier';

    /**
     * @var \Psr\Log\LoggerInterface $_logger
     */
    protected $_logger;

    /**
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory $_rateResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $_rateMethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\LPTableRates $_LPTableRates
     */
    protected $_LPTableRates;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\LPExpressTableRates $_LPExpressTableRates
     */
    protected $_LPExpressTableRates;

    /**]
     * @var \LP\LPShipping\Model\ResourceModel\LPCountries\Collection $_LPCountries
     */
    protected $_LPCountries;

    /**
     * @var \LP\LPShipping\Helper\ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var \LP\LPShipping\Helper\ShippingTemplate $_shippingTemplateHelper
     */
    protected $_shippingTemplateHelper;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory $_trackFactory
     */
    protected $_trackFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory $_trackStatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * @var \LP\LPShipping\Api\TrackingRepositoryInterface $_trackingRepository
     */
    protected $_trackingRepository;

    /**
     * @var \LP\LPShipping\Api\SenderRepositoryInterface $_senderRepository
     */
    protected $_senderRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * @var \LP\LPShipping\Model\Config $_config
     */
    protected $_config;

    static protected $labels = [];

    protected $_test;
    private $checkoutSession;
    private $LPExpressDeliveryMethods;
    private $LPDeliveryMethods;
    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    /**
     * @var FileFactory $_fileFactory
     */
    protected $_fileFactory;
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $locale;

    public function __construct(
        \LP\LPShipping\Model\ResourceModel\LPTableRates           $LPTableRates,
        \LP\LPShipping\Model\ResourceModel\LPExpressTableRates    $LPExpressTableRates,
        \LP\LPShipping\Model\ResourceModel\LPCountries\Collection $LPCountries,
        \LP\LPShipping\Helper\ApiHelper                           $apiHelper,
        \LP\LPShipping\Helper\ShippingTemplate                    $shippingTemplateHelper,
        \LP\LPShipping\Api\TrackingRepositoryInterface            $trackingRepository,
        \LP\LPShipping\Api\SenderRepositoryInterface              $senderRepository,
        \LP\LPShipping\Model\Config                               $config,
        \Magento\Framework\App\Config\ScopeConfigInterface             $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory     $rateErrorFactory,
        \Psr\Log\LoggerInterface                                       $logger,
        Security                                                       $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory               $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory                     $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory    $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory                 $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory           $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory          $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory                         $regionFactory,
        \Magento\Directory\Model\CountryFactory                        $countryFactory,
        \Magento\Directory\Model\CurrencyFactory                       $currencyFactory,
        \Magento\Directory\Helper\Data                                 $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface           $stockRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface                    $orderRepository,
        Session                                                        $checkoutSession,
        LPExpressDeliveryMethods                                       $LPExpressDeliveryMethods,
        LPDeliveryMethods                                              $LPDeliveryMethods,
        ShippingHelper                                                 $shippingHelper,
        FileFactory                                                    $fileFactory,
        \Magento\Framework\Locale\Resolver                             $locale,
        array                                                          $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_LPTableRates = $LPTableRates;
        $this->_LPExpressTableRates = $LPExpressTableRates;
        $this->_LPCountries = $LPCountries;
        $this->_logger = $logger;
        $this->_apiHelper = $apiHelper;
        $this->_shippingTemplateHelper = $shippingTemplateHelper;
        $this->_trackFactory = $trackFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->_trackingRepository = $trackingRepository;
        $this->_senderRepository = $senderRepository;
        $this->_config = $config;
        $this->_orderRepository = $orderRepository;
        $this->_test = [];
        $this->checkoutSession = $checkoutSession;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateResultFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        $this->LPExpressDeliveryMethods = $LPExpressDeliveryMethods;
        $this->LPDeliveryMethods = $LPDeliveryMethods;
        $this->shippingHelper = $shippingHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_fileFactory = $fileFactory;
        $this->locale = $locale;
    }

    /**
     * Available services by contract
     * @return array
     */
    protected function getAvailableServices(): array
    {
        return [
            0 => ['lpcarriershipping_lp', 'lpcarriershipping_lpexpress'],
            1 => ['lpcarriershipping_lp'],
            2 => ['lpcarriershipping_lpexpress']
        ];
    }

    protected function getCarrierTitle($key): string
    {
        $services = [
            'lpcarriershipping_lp'        => __('Lithuanian Post'),
            'lpcarriershipping_lpexpress' => __('Unisend')
        ];

        return $services[$key] ?? '';
    }

    /**
     * Get enabled services
     * @return array
     */
    protected function getEnabledServices()
    {
        return $this->getAvailableServices()[$this->getConfigData('available_services')];
    }

    /**
     * Create methods structure code => label
     * @return array
     */
    protected function getAllMethods(): array
    {
        return array_merge($this->LPExpressDeliveryMethods->getUnisendShippingMethods(), $this->LPDeliveryMethods->getLpShippingMethods());
    }

    /**
     * Get formatted allowed methods
     * @return array
     */
    protected function getEnabledMethods()
    {
        if (!$this->_config->isSenderDataSet()) {
            return [];
        }

        // Filtered array
        $allowedMethods = [];

        // Cycle through allowed methods config values
        foreach ($this->getEnabledServices() as $availableService) {
            // Filter only allowed methods
            $allowedMethods[$availableService] =
                array_filter($this->getAllMethods(), function ($method, $key) use ($availableService) {
                    return in_array($key, explode(',', $this->getConfigData($availableService)['allowedmethods'] ?? ''));
                },
                    ARRAY_FILTER_USE_BOTH
                );
        }

        // Convert Phrase Object to assoc array
        return json_decode(json_encode($allowedMethods), true);
    }

    /**
     * Allowed methods not in lithuania
     * @return array
     */
    protected function getOnlyForeignMethods(): array
    {
        return [
            LPDeliveryMethods::METHOD_TRACKED,
        ];
    }

    /**
     * Get service config
     * @param $service
     * @param $field
     * @return mixed
     */
    protected function getServiceConfigData($service, $field)
    {
        return $this->getConfigData($service) [$field];
    }

    /**
     * @return Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || !$this->getConfigFlag('status')) {
            return false;
        }

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();

        foreach ($this->getEnabledMethods() as $service => $methods) {
            foreach ($methods as $method => $label) {
                if (in_array($method, $this->getOnlyForeignMethods()) && $request->getDestCountryId() === 'LT') {
                    continue;
                }

                $weight = $this->shippingHelper->getQuoteWeight($this->getAllItems($request), true);
                $receiverCountryCode = $request->getDestCountryId();
                if (!$this->shippingHelper->isShippingMethodAvailable($method, $weight, $receiverCountryCode)) {
                    continue;
                }

                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $methodFactory = $this->_rateMethodFactory->create();

                $methodFactory->setCarrier($this->_code);
                $methodFactory->setCarrierTitle($this->getCarrierTitle($service));
                $methodFactory->setMethod($this->_code . $method);
                $methodFactory->setMethodTitle($this->_config->getMethodTitle($method, $this->locale->getLocale()) ?: $label);

                $freeShippingReached = $this->isFreeShipping($service, $method);
                // Fixed rates
                if ($shippingPrice = $this->getConfigData($service . "/{$method}_pricing")[sprintf('%s_price', $method)]) {
                    $methodFactory->setPrice($freeShippingReached ? 0 : $shippingPrice);
                    $methodFactory->setCost($freeShippingReached ? 0 : $shippingPrice);
                } else {
                    $weight = $this->shippingHelper->getQuoteWeight($this->getAllItems($request));
                    // Table rates
                    $rate = null;
                    if ($service === 'lpcarriershipping_lp') {
                        $rate = $this->_LPTableRates->getRate($weight, $method, $receiverCountryCode);
                    } elseif ($service === 'lpcarriershipping_lpexpress') {
                        $rate = $this->_LPExpressTableRates->getRate($weight, $method, $receiverCountryCode);
                    }
                    if ($rate !== null && $rate !== -1) {
                        $methodFactory->setPrice($freeShippingReached ? 0 : $rate);
                        $methodFactory->setCost($freeShippingReached ? 0 : $rate);
                    } else {
                        continue;
                    }
                }

                $result->append($methodFactory);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $toReturn = [];
        foreach ($this->getEnabledMethods() as $methods) {
            foreach ($methods as $methodCode => $method) {
                $toReturn[$methodCode] = $method;
            }
        }

        return $toReturn;
    }

    protected function getShipmentData($order, bool $reverseReceiverAndSender = false)
    {
        // Sender info
        $sender = $this->_senderRepository->getByOrderId($order->getEntityId());

        // Get sender data from module configuration or from shipment details
        $sender_name = $sender->getName() ?? $this->shippingHelper->getSenderName();
        $sender_phone = $sender->getPhone() ?? $this->shippingHelper->getSenderPhone();
        $sender_email = $sender->getEmail() ?? $this->shippingHelper->getSenderEmail();
        // Sender country code by id
        $country = $this->_LPCountries->addFieldToFilter(
            'country_id',
            $sender->getCountryId() ?? $this->shippingHelper->getSenderCountryId()
        );
        $sender_country = $country->getData()[0]['country_code'];
        $sender_city = $sender->getCity() ?? $this->shippingHelper->getSenderCity();
        $sender_street = $sender->getStreet() ?? $this->shippingHelper->getSenderStreet();
        $sender_building = $sender->getBuildingNumber() ?? $this->shippingHelper->getSenderBuilding();
        $sender_postcode = $sender->getPostcode() ?? $this->shippingHelper->getSenderPostCode();
        $senderAddress1 = $sender->getAddressLine1() ?? $this->shippingHelper->getSenderAddressLine1();
        $senderAddress2 = $sender->getAddressLine2() ?? $this->shippingHelper->getSenderAddressLine2();

        // Receiver info
        $shippingAddress = $order->getShippingAddress();
        $receiver_phone = $shippingAddress->getTelephone();
        $receiver_name = sprintf('%s %s', $shippingAddress->getFirstName(), $shippingAddress->getLastName());

        // Convert to 370xxxxxxx
        if (substr($receiver_phone, 0, 1) === "8") {
            $receiver_phone = substr_replace($receiver_phone, "370", 0, 1);
        }

        if ($shippingAddress->getCountryId() !== 'LT') {
            $address1 = $shippingAddress->getStreet()[0] ?? null;
            $address2 = $shippingAddress->getStreet()[1] ?? null;
        }

        $receiverData = [
            'name' => $receiver_name,
            'companyName' => $shippingAddress->getCompany() ?? $receiver_name,
            'contacts' => [
                'phone' => $receiver_phone,
                'email' => $shippingAddress->getEmail(),
                'fax' => $shippingAddress->getFax(),
            ],
            'address' => [
                'address' => implode(',', $shippingAddress->getStreet()),
                'address1' => $address1 ?? null,
                'address2' => $address2 ?? null,
                'locality' => $shippingAddress->getCity(),
                'postalCode' => $shippingAddress->getPostcode(),
                'countryCode' => $shippingAddress->getCountryId(),
            ],
        ];
        $senderData = [
            'name' => $sender_name,
            'companyName' => $sender_name,
            'contacts' => [
                'phone' => $sender_phone,
                'email' => $sender_email,
            ],
            'address' => [
                'address' => $sender_street,
                'building' => $sender_building,
                'locality' => $sender_city,
                'postalCode' => $sender_postcode,
                'countryCode' => $sender_country,
                'address1' => $senderAddress1,
                'address2' => $senderAddress2,
            ]
        ];

        $shipmentData = [
            'idRef' => $reverseReceiverAndSender ? uniqid('lp_r') : $order->getLpUniqueId(),
            'plan' => [
                'code' => ShippingHelper::getShippingPlan($order->getShippingMethod()),
            ],
            'parcel' => [
                'type' => ShippingHelper::getShippingParcelType($order->getShippingMethod()),
                'weight' => ($order->getLpPackageWeight() ?: $this->shippingHelper->getQuoteWeight($order->getAllItems(), true)),
                'size' => $order->getLpShippingSize() ?: ($this->_config->getDefaultShipmentSize($order->getShippingMethod()) ?: 'XS'),
                'partCount' => $order->getLpShippingPackageQuantity() ?? 1,
                'document' => true,
            ],
        ];
        $shipmentData['sender'] = $senderData;
        $shipmentData['receiver'] = $receiverData;

        if ($reverseReceiverAndSender) {
            $shipmentData['sender'] = $receiverData;
            $shipmentData['receiver'] = $senderData;
        }

        if (!empty($services = $this->getAdditionalServices($order))) {
            $shipmentData['services'] = $services;
        }

        // Terminal
        if (ShippingHelper::isTerminalShippingMethod($order->getShippingMethod())) {
            $shipmentData['receiver']['address']['terminalId'] = $order->getLpexpressTerminal();
        }

        if (!empty($additionalServices = $this->getAdditionalServices($order))) {
            $shipmentData['services'] = $additionalServices;
        }

        if ($cnData = $this->_shippingTemplateHelper->getCnData($order)) {
            $shipmentData['documents']['cn'] = $cnData;
        }
        $shipmentData['source'] = 'Magento';

        return $shipmentData;
    }

    /**
     * @param $trackings
     * @return mixed
     * @throws \Exception
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create();
        $tracking = $this->_trackStatusFactory->create();

        foreach ($trackings as $trackingCode) {
            $packageEvents = [];
            $trackItem = $this->_trackingRepository->getByTrackingCode($trackingCode);
            $tracking->setTracking($trackingCode);

            $resultArr = [];
            $resultArr['carrier_title'] = __('Lithuania Post');

            if ($trackItem && $trackItem->getEvents()) {
                foreach (json_decode($trackItem->getEvents()) as $event) {
                    array_push($packageEvents, [
                        'deliverydate' => explode(' ', $event->eventDate)[0],
                        'deliverytime' => explode(' ', $event->eventDate)[1],
                        'activity' => $event->eventTitle
                    ]);
                }

                $resultArr['status'] = $this->_trackingRepository
                    ->getEventDescriptionByCode($trackItem->getStateCode());
                $resultArr['progressdetail'] = $packageEvents;
            }

            $tracking->addData($resultArr);
            $result->append($tracking);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $orders = !empty($_REQUEST['selected']) ? $_REQUEST['selected'] : [$request->getOrderShipment()->getOrder()->getId()];
        // Add labels pdf and tracking number
        $result = new DataObject();

        /** @var Order $order */
        $order = $request->getOrderShipment()->getOrder();
        $shippingItemIds = [];
        $parcels = [];
        $shipmentData = $this->getShipmentData($order);
        try {
            if (!$shippingItemId = $order->getLpShippingItemId()) {
                $shippingItemId = $this->_apiHelper->createShippingItem($shipmentData);
                $order->setLpShippingItemId($shippingItemId);
            }
            $shippingItemIds['parcelIds'][] = $shippingItemId;
            $parcels[] = $shippingItemId;
            self::$labels[] = $shippingItemId;

            if ($this->_config->shouldSendReturnLabel() && !$order->getLpReturnParcelId()) {
                $returnParcelData = $this->getShipmentData($order, true);
                $parcelId = $this->_apiHelper->createShippingItem($returnParcelData);
                $order->setLpReturnParcelId($parcelId);

                $shippingItemIds['parcelIds'][] = $parcelId;
                $parcels[] = $parcelId;
                self::$labels[] = $parcelId;
            }

            if ($requestId = $this->_apiHelper->initiateShipping($shippingItemIds)) {
                $order->setLpRequestId($requestId);
                $trackingNumber = $this->_apiHelper->getBarcode($requestId);
            }

            if ($label = $this->_apiHelper->createSticker($parcels)) {
                $result->setShippingLabelContent($label);
                $result->setTrackingNumber($trackingNumber ?? null);
                // Set custom order status
                $this->_config->setOrderStatus($order, Config::SHIPMENT_CREATED_STATUS);

                $order->setLpShipmentTrackingUpdated(date('Y-m-d H:i:s'));
            }
            //it goes in reverse order so this if is "when last order"
            if ($orders[0] === $order->getId()) {
                if ($label = $this->_apiHelper->createSticker(self::$labels)) {
                    $this->_fileFactory->create(
                        sprintf('LP_Label_%s.pdf', $order->getIncrementId()),
                        $label,
                        DirectoryList::VAR_DIR,
                        'application/pdf',
                        ''
                    );
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        } finally {
            $this->_orderRepository->save($order);
        }

        return $result;
    }

    public function isShippingLabelsAvailable()
    {
        return true;
    }

    public function processAdditionalValidation(DataObject $request)
    {
        return true;
    }

    private function isFreeShipping(string $service, string $method): bool
    {
        $freeFromString = $this->getConfigData($service . "/{$method}_pricing")[sprintf('%s_free_shipping', $method)];
        if (null === $freeFromString) {
            return false;
        }
        $freeFromFloat = (float)$freeFromString;
        //do not trust editor here, getGrandTotal returns string not float
        $orderTotalFloat = (float)$this->checkoutSession->getQuote()->getGrandTotal();

        return $freeFromFloat <= $orderTotalFloat;
    }

    private function getAdditionalServices($order): array
    {
        $additionalServices = [];
        if ($order->getPayment()->getMethod() === Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE) {
            $additionalServices[] = [
                'code' => ShippingHelper::ADDITIONAL_SERVICE_COD,
                'value' => $order->getLpCod() ?? $order->getGrandTotal(),
            ];
        }
        if (
            str_contains($order->getShippingMethod(), LPDeliveryMethods::METHOD_UNTRACKED)
            || str_contains($order->getShippingMethod(), LPDeliveryMethods::METHOD_SIGNED)
        ) {
            $additionalServices[] = [
                'code' => ShippingHelper::ADDITIONAL_SERVICE_SIGNED,
                'value' => 1,
            ];
        }
        if (str_contains($order->getShippingMethod(), LPDeliveryMethods::METHOD_TRACKED)) {
            $additionalServices[] = [
                'code' => ShippingHelper::ADDITIONAL_SERVICE_PRIORITY,
                'value' => 1,
            ];
        }

        return $additionalServices;
    }
}
