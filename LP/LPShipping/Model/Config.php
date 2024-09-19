<?php

namespace LP\LPShipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use LP\LPShipping\Model\Config\Source\LPDeliveryMethods;
use LP\LPShipping\Model\Config\Source\LPExpressDeliveryMethods;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Path constants
     */
    const CONFIG_PATH_STATUS                                = 'carriers/lpcarrier/status';
    const CONFIG_PATH_SENDER_STATUS                         = 'carriers/lpcarrier/sender_status';
    const CONFIG_PATH_ENABLED                               = 'carriers/lpcarrier/active';
    const CONFIG_PATH_API_USERNAME                          = 'carriers/lpcarrier/lpcarrierapi/api_login';
    const CONFIG_PATH_API_PASSWORD                          = 'carriers/lpcarrier/lpcarrierapi/api_password';
    const CONFIG_PATH_SENDER_NAME                           = 'carriers/lpcarrier/lpcarriersender/sender_name';
    const CONFIG_PATH_SENDER_PHONE                          = 'carriers/lpcarrier/lpcarriersender/sender_phone';
    const CONFIG_PATH_SENDER_EMAIL                          = 'carriers/lpcarrier/lpcarriersender/sender_email';
    const CONFIG_PATH_SENDER_COUNTRY                        = 'carriers/lpcarrier/lpcarriersender/sender_country';
    const CONFIG_PATH_SENDER_CITY                           = 'carriers/lpcarrier/lpcarriersender/sender_city';
    const CONFIG_PATH_SENDER_STREET                         = 'carriers/lpcarrier/lpcarriersender/sender_street';
    const CONFIG_PATH_SENDER_BUILDING                       = 'carriers/lpcarrier/lpcarriersender/sender_building_number';
    const CONFIG_PATH_SENDER_APARTMENT                      = 'carriers/lpcarrier/lpcarriersender/sender_apartment_number';
    const CONFIG_PATH_SENDER_POSTCODE                       = 'carriers/lpcarrier/lpcarriersender/sender_postcode';
    const CONFIG_PATH_SENDER_ADDRESS_LINE_1                 = 'carriers/lpcarrier/lpcarriersender/sender_address_line_1';
    const CONFIG_PATH_SENDER_ADDRESS_LINE_2                 = 'carriers/lpcarrier/lpcarriersender/sender_address_line_2';
    const CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER              = 'carriers/lpcarrier/lpcarriersender/warehouse_same_as_sender';
    const CONFIG_PATH_WAREHOUSE_NAME                        = 'carriers/lpcarrier/warehouse/name';
    const CONFIG_PATH_WAREHOUSE_PHONE                       = 'carriers/lpcarrier/warehouse/phone';
    const CONFIG_PATH_WAREHOUSE_EMAIL                       = 'carriers/lpcarrier/warehouse/email';
    const CONFIG_PATH_WAREHOUSE_COUNTRY                     = 'carriers/lpcarrier/warehouse/country';
    const CONFIG_PATH_WAREHOUSE_CITY                        = 'carriers/lpcarrier/warehouse/city';
    const CONFIG_PATH_WAREHOUSE_STREET                      = 'carriers/lpcarrier/warehouse/street';
    const CONFIG_PATH_WAREHOUSE_BUILDING                    = 'carriers/lpcarrier/warehouse/building_number';
    const CONFIG_PATH_WAREHOUSE_APARTMENT                   = 'carriers/lpcarrier/warehouse/apartment_number';
    const CONFIG_PATH_WAREHOUSE_POSTCODE                    = 'carriers/lpcarrier/warehouse/postcode';
    const CONFIG_PATH_WAREHOUSE_ADDRESS_LINE_1              = 'carriers/lpcarrier/warehouse/address_line_1';
    const CONFIG_PATH_WAREHOUSE_ADDRESS_LINE_2              = 'carriers/lpcarrier/warehouse/address_line_2';
    const CONFIG_PATH_LPEXPRESS_CALL_COURIER_AUTOMATICALLY  = 'carriers/lpcarrier/lpcarriershipping_lpexpress/call_courier_automatically';
    const CONFIG_PATH_LPEXPRESS_COURIER_ARRIVAL_TIME        = 'carriers/lpcarrier/lpcarriershipping_lpexpress/courier_arrival_time';
    const CONFIG_PATH_LABEL_SIZE                            = 'carriers/lpcarrier/lpcarriershipping_other_settings/label_format';
    const CONFIG_PATH_LABEL_ORIENTATION                     = 'carriers/lpcarrier/lpcarriershipping_other_settings/label_orientation';
    const CONFIG_PATH_SEND_RETURN_LABEL                     = 'carriers/lpcarrier/lpcarriershipping_other_settings/send_return_label';
    const CONFIG_PATH_CONSIGMENT_FORMATION                  = 'carriers/lpcarrier/lpcarriershipping_other_settings/consigment_formation';
    const CONFIG_CALL_COURIER = 'carriers/lpcarrier/call_courier';

    /**
     * Database tables
     */
    const CONFIG_DB_TERMINAL_TABLE_NAME = 'lpexpress_terminal_list';
    const CONFIG_DB_API_TABLE_NAME = 'lp_api_token';

    /**
     * Order statuses
     */
    const COURIER_CALLED_STATUS = 'lp_courier_called';
    const COURIER_NOT_CALLED_STATUS = 'lp_courier_not_called';
    const SHIPMENT_CREATED_STATUS = 'lp_shipment_created';
    const SHIPMENT_NOT_CREATED_STATUS = 'lp_shipment_not_created';
    const SHIPMENT_CANCELED = 'lp_shipment_canceled';

    /**
     * @var ScopeConfigInterface $_config
     */
    protected $_config;

    /**
     * @var WriterInterface $_configWriter
     */
    protected $_configWriter;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $config
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $config,
        WriterInterface $configWriter
    ) {
        $this->_config = $config;
        $this->_configWriter = $configWriter;
    }

    public static function getMethodsRequiringCourierCall(): array
    {
        return [
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2H_HANDS,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2T_TERMINAL,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2P_TRACKED_SIGNED,
        ];
    }

    /**
     * Get is module enabled
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->_config->getValue(self::CONFIG_PATH_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get terminal db name
     * @return string
     */
    public function getTerminalDbTableName()
    {
        return self::CONFIG_DB_TERMINAL_TABLE_NAME;
    }

    /**
     * Get api_token db name
     * @return string
     */
    public function getApiTokenDbTableName()
    {
        return self::CONFIG_DB_API_TABLE_NAME;
    }

    /**
     * Get API credentials username
     * @return mixed
     */
    public function getApiUsername()
    {
        return $this->_config->getValue(self::CONFIG_PATH_API_USERNAME, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get API credentials password
     * @return mixed
     */
    public function getApiPassword()
    {
        return $this->_config->getValue(self::CONFIG_PATH_API_PASSWORD, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender name
     * @return mixed
     */
    public function getSenderName()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_NAME, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender phone
     * @return mixed
     */
    public function getSenderPhone()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_PHONE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender email
     * @return mixed
     */
    public function getSenderEmail()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_EMAIL, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender country
     * @return mixed
     */
    public function getSenderCountryId()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_COUNTRY, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender city
     * @return mixed
     */
    public function getSenderCity()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_CITY, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender street
     * @return mixed
     */
    public function getSenderStreet()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_STREET, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender building
     * @return mixed
     */
    public function getSenderBuilding()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_BUILDING, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender apartment
     * @return mixed
     */
    public function getSenderApartment()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_APARTMENT, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get sender postcode
     * @return mixed
     */
    public function getSenderPostCode()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_POSTCODE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderAddressLine1()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_ADDRESS_LINE_1, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSenderAddressLine2()
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_ADDRESS_LINE_2, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get if call courier automatically for LP Express
     * @return mixed
     */
    public function getCallCourierAutomatically()
    {
        return $this->_config->getValue(self::CONFIG_PATH_LPEXPRESS_CALL_COURIER_AUTOMATICALLY, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Set module status
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->_configWriter->save(self::CONFIG_PATH_STATUS, $status);
    }

    public function setOrderStatus(Order $order, string $status)
    {
        $order->setStatus($status);
    }

    /**
     * Get module status
     * @return mixed
     */
    public function getStatus()
    {
        return $this->_config->getValue(self::CONFIG_PATH_STATUS, ScopeInterface::SCOPE_WEBSITE);
    }

    public function setSenderStatus($status)
    {
        $this->_configWriter->save(self::CONFIG_PATH_SENDER_STATUS, $status);
    }

    public function getSenderStatus(): bool
    {
        return $this->_config->getValue(self::CONFIG_PATH_SENDER_STATUS, ScopeInterface::SCOPE_WEBSITE);
    }

    public function isLpMethod($method): bool
    {
        return in_array($method, [
            'lpcarrier_lpcarrier' . LPDeliveryMethods::METHOD_UNTRACKED,
            'lpcarrier_lpcarrier' . LPDeliveryMethods::METHOD_TRACKED,
            'lpcarrier_lpcarrier' . LPDeliveryMethods::METHOD_SIGNED,
        ]);
    }

    public function isLpExpressMethod($method): bool
    {
        return in_array($method, [
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2H_HANDS,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::T2H_HANDS,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::T2T_TERMINAL,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2T_TERMINAL,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::T2S_TERMINAL,
            'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2P_TRACKED_SIGNED,
        ]);
    }

    public function isCallCourierRequired($method): bool
    {
        return in_array($method, self::getMethodsRequiringCourierCall());
    }

    /**
     * Check if manual courier call allowed and is lpExpress method
     * @param Order $order
     * @return bool
     */
    public function isCallCourierAllowed($order)
    {
        return $this->isCallCourierRequired($order->getShippingMethod())
            && !$this->getCallCourierAutomatically();
    }

    /**
     * Get label size
     * @return mixed
     */
    public function getLabelSize()
    {
        return $this->_config->getValue(self::CONFIG_PATH_LABEL_SIZE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getConsigmentFormation()
    {
        return $this->_config->getValue(self::CONFIG_PATH_CONSIGMENT_FORMATION, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseName()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_NAME, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehousePhone()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_PHONE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseEmail()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_EMAIL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseCountryId()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_COUNTRY, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseCity()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_CITY, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseStreet()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_STREET, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseBuilding()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_BUILDING, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseApartment()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_APARTMENT, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehousePostCode()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_POSTCODE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseAddressLine1()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_ADDRESS_LINE_1, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseAddressLine2()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_ADDRESS_LINE_2, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWarehouseSameAsSender()
    {
        return $this->_config->getValue(self::CONFIG_PATH_WAREHOUSE_SAME_AS_SENDER, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getCourierArrivalTime()
    {
        return $this->_config->getValue(self::CONFIG_PATH_LPEXPRESS_COURIER_ARRIVAL_TIME, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getLabelOrientation()
    {
        return $this->_config->getValue(self::CONFIG_PATH_LABEL_ORIENTATION, ScopeInterface::SCOPE_WEBSITE);
    }

    public function shouldSendReturnLabel(): bool
    {
        return (bool)$this->_config->getValue(self::CONFIG_PATH_SEND_RETURN_LABEL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getDeliveryTimeByType(string $type)
    {
        if (null !== $val = $this->_config->getValue('carriers/lpcarrier/lpcarriershipping_lp/' . $type . '_pricing/' . $type . '_delivery_time', ScopeInterface::SCOPE_WEBSITE)) {
            return $val;
        }

        return $this->_config->getValue('carriers/lpcarrier/lpcarriershipping_lpexpress/' . $type . '_pricing/' . $type . '_delivery_time', ScopeInterface::SCOPE_WEBSITE);
    }

    public function isSenderDataSet(): bool
    {
        $addressSet = $this->getSenderStreet()
            ? $this->getSenderStreet() && $this->getSenderBuilding()
            : $this->getSenderAddressLine1();

        return $this->getSenderPostCode()
            && $this->getSenderCity()
            && $this->getSenderCountryId()
            && $this->getSenderPhone()
            && $addressSet
            ;
    }

    public function getTimeZone()
    {
        return $this->_config->getValue('general/locale/timezone', ScopeInterface::SCOPE_WEBSITE);
    }

    public function getCallCourierExecutionTime()
    {
        return $this->_config->getValue(self::CONFIG_CALL_COURIER);
    }

    public function setCallCourierExecutionTime($dateTime)
    {
        $this->_configWriter->save(self::CONFIG_CALL_COURIER, $dateTime);
    }

    public function getMethodTitle(string $shippingMethod, string $locale)
    {
        $langCode = strstr($locale, '_', true);

        if (null !== $val = $this->_config->getValue('carriers/lpcarrier/lpcarriershipping_lp/' . $shippingMethod . '_pricing/' . $shippingMethod . '_title_' . $langCode, ScopeInterface::SCOPE_WEBSITE)) {
            return $val;
        }

        return $this->_config->getValue('carriers/lpcarrier/lpcarriershipping_lpexpress/' . $shippingMethod . '_pricing/' . $shippingMethod . '_title_' . $langCode, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getDefaultShipmentSize(string $shippingMethod)
    {
        $method = str_replace('lpcarrier_lpcarrier', '', $shippingMethod);
        if (null !== $val = $this->_config->getValue('carriers/lpcarrier/lpcarriershipping_lpexpress/' . $method . '_pricing/' . $method . '_delivery_size', ScopeInterface::SCOPE_WEBSITE)) {
            return $val;
        }

        return $this->_config->getValue('carriers/lpcarrier/lpcarriershipping_lp/' . $method . '_pricing/' . $method . '_delivery_size', ScopeInterface::SCOPE_WEBSITE);
    }

    public function setCarrierTitle()
    {
        $this->_configWriter->save('carriers/lpcarrier/title', 'Unisend');
    }
}
