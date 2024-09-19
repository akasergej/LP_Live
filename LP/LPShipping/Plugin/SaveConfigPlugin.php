<?php

namespace LP\LPShipping\Plugin;

use LP\LPShipping\Cron\CallCourier;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Interceptor;

class SaveConfigPlugin
{
    const SUPPORTED_COUNTRIES = [
        118 => [
            'code'    => 'LT',
            'country' => 'Lietuva',
        ],
        229 => [
            'code'    => 'LV',
            'country' => 'Latvija',
        ],
        169 => [
            'code'    => 'EE',
            'country' => 'Estija',
        ],
    ];

    /**
     * @var \LP\LPShipping\Helper\ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var \LP\LPShipping\Model\Config $_config
     */
    protected $_config;

    /**
     * @var \LP\LPShipping\Model\ApiTokenFactory $_apiTokenFactory
     */
    protected $_apiTokenFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface $_messageManager
     */
    protected $_messageManager;

    /**
     * @var \LP\LPShipping\Api\Data\LPExpressTerminalsInterfaceFactory $_terminalsFactory
     */
    protected $_terminalsFactory;

    /**
     * @var \LP\LPShipping\Api\LPExpressTerminalRepositoryInterface $_terminalRepository
     */
    protected $_terminalRepository;

    /**
     * @var \LP\LPShipping\Model\LPCountriesFactory $_countriesFactory
     */
    protected $_countriesFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection $_resourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var CallCourier
     */
    private $callCourier;

    public function __construct(
        \LP\LPShipping\Helper\ApiHelper $apiHelper,
        \LP\LPShipping\Model\Config $config,
        \LP\LPShipping\Model\ApiTokenFactory $apiTokenFactory,
        \LP\LPShipping\Api\Data\LPExpressTerminalsInterfaceFactory $terminalsFactory,
        \LP\LPShipping\Api\LPExpressTerminalRepositoryInterface $terminalRepository,
        \LP\LPShipping\Model\LPCountriesFactory $countriesFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        CallCourier $callCourier
    ) {
        $this->_apiHelper                   = $apiHelper;
        $this->_config                      = $config;
        $this->_apiTokenFactory             = $apiTokenFactory;
        $this->_messageManager              = $messageManager;
        $this->_terminalsFactory            = $terminalsFactory;
        $this->_terminalRepository          = $terminalRepository;
        $this->_countriesFactory            = $countriesFactory;
        $this->_resourceConnection          = $resourceConnection;
        $this->callCourier = $callCourier;
    }

    /**
     * Truncate table to clear old data
     * @param $tableName
     */
    private function truncateTable($tableName)
    {
        $connection = $this->_resourceConnection->getConnection();
        $connection->query(
            sprintf(
                'TRUNCATE %s',
                $this->_resourceConnection->getTableName($tableName)
            )
        );
    }

    /**
     * Save access token from API
     * @param $username
     * @param $password
     * @return bool
     */
    private function saveAccessToken($username, $password)
    {
        if ($accessTokenObject = $this->_apiHelper->requestAccessToken(
            $username,
            $password
        )) {
            // Truncate api credentials
            $this->truncateTable($this->_config->getApiTokenDbTableName());

            // Set timezone
            date_default_timezone_set('Europe/Vilnius');

            /** @var \LP\LPShipping\Model\ApiToken $apiTokenModel */
            $apiTokenModel = $this->_apiTokenFactory->create();
            $apiTokenModel
                ->setAccessToken($accessTokenObject->access_token)
                ->setRefreshToken($accessTokenObject->refresh_token)
                ->setExpires(date(
                    'Y-m-d H:i:s',
                    time() + $accessTokenObject->expires_in
                ))
                ->save();

            return true;
        }

        return false;
    }

    /**
     * Save LP EXPRESS terminals from API
     */
    private function saveLPExpressTerminalList()
    {
        $this->truncateTable($this->_config->getTerminalDbTableName());
        foreach (self::SUPPORTED_COUNTRIES as $countryData) {
            if ($terminalList = $this->_apiHelper->getLPExpressTerminalList($countryData['code'])) {
                // Truncate LP EXPRESS terminal list

                foreach ($terminalList as $terminalAPI) {
                    /** @var \LP\LPShipping\Model\LPExpressTerminals $terminal */
                    $terminal = $this->_terminalsFactory->create();
                    $terminal->setTerminalId($terminalAPI->id)
                        ->setName($terminalAPI->name)
                        ->setAddress($terminalAPI->address)
                        ->setCity($terminalAPI->city)
                        ->setCountryCode($countryData['code'])
                    ;

                    $this->_terminalRepository->save($terminal);
                }
            }
        }
    }

    private function saveAvailableCountryList()
    {
        // Truncate LP available country list
        $this->truncateTable('lp_country_list');

        foreach (self::SUPPORTED_COUNTRIES as $id => $country) {
            /** @var \LP\LPShipping\Model\LPCountries $countryListModel */
            $countryListModel = $this->_countriesFactory->create();
            $countryListModel->setCountryId($id)
                ->setCountryCode($country['code'])
                ->setCountry($country['country'])
                ->save();
        }
    }

    /**
     * Before saving module settings check if credentials
     * Are correct and try to fetch access token. If it is successful
     * Then fetch terminal list and available countries from API
     * Also set module status to active so it can proceed it's purpose
     * @param Config $subject
     */
    public function beforeSave(
        Config $subject
    ) {
        if (!key_exists('lpcarrier', $subject->getData()['groups'])) {
            return;
        }
        // Is NEW value enabled
        $enabled = $subject->getData()['groups']['lpcarrier']['fields']['active']['value'];
        $this->_config->setCarrierTitle();
        if ($subject->getSection() === 'carriers' && $enabled) {
            // API username - new value ( not saved )
            $apiUsername = $subject->getData()['groups']['lpcarrier']['groups']['lpcarrierapi']
                           ['fields']['api_login']['value'] ?? null;
            // API password - new value ( not saved )
            $apiPassword = $subject->getData()['groups']['lpcarrier']['groups']['lpcarrierapi']
                           ['fields']['api_password']['value'] ?? null;

            // Only if new API values presented
            if (
                $this->_config->getApiUsername() !== $apiUsername
                || $this->_config->getApiPassword() !== $apiPassword
            ) {
                // Fetch and save API access token
                if ($this->saveAccessToken($apiUsername, $apiPassword)) {
                    // Fetch and save terminal list from API
                    $this->saveLPExpressTerminalList();

                    // Fetch and save available country list from API
                    $this->saveAvailableCountryList();

                    // Set module status to active
                    $this->_config->setStatus(true);
                } else {
                    // Set module status to inactive
                    $this->_config->setStatus(false);
                }
            }

            //if there is scheduled time for tomorrow and time changed, reflect time change on the scheduled time.
            if (isset($subject->getData()['groups']['lpcarrier']['groups']['lpcarriershipping_lpexpress']['fields']['courier_arrival_time'])) {
                $courierCallTime = $subject->getData()['groups']['lpcarrier']['groups']['lpcarriershipping_lpexpress']['fields']['courier_arrival_time']['value'];
                if (
                    (implode(',', $courierCallTime) !== $this->_config->getCourierArrivalTime())
                    && $this->_config->getCallCourierExecutionTime()
                ) {
                    $time = $this->callCourier->getCallCourierScheduledTime(implode(':', $courierCallTime));
                    $this->callCourier->rescheduleCallCourier($time);
                }
            }

            $this->_config->setSenderStatus($this->isSenderDataSet($subject->getData()));
        }
    }

    /**
     * After successful module configuration save
     * Verify sender postcode and city
     */
    public function afterSave(Config $subject)
    {
        if (
            $subject->getSection() !== 'carriers'
            || !$this->_config->isEnabled()
            || !$this->_config->getStatus()
        ) {
            return;
        }

        if ($this->_config->getSenderPostCode()) {
            $this->_apiHelper->verifySenderAddress();
        }
    }

    public static function getCountryCodeById(int $id): string
    {
        return self::SUPPORTED_COUNTRIES[$id]['code'] ?? '';
    }

    private function isSenderDataSet(array $data): bool
    {
        $senderFields = $data['groups']['lpcarrier']['groups']['lpcarriersender']['fields'] ?? [];
        if (empty($senderFields)) {
            return false;
        }

        $addressSet = $senderFields['sender_street']['value'] ?? null
            ? $senderFields['sender_street']['value'] ?? null && $senderFields['sender_building_number']['value'] ?? null
            : $senderFields['sender_address_line_1']['value'] ?? null;

        return ($senderFields['sender_postcode']['value'] ?? null)
            && ($senderFields['sender_city']['value'] ?? null)
            && ($senderFields['sender_country']['value'] ?? null)
            && ($senderFields['sender_phone']['value'] ?? null)
            && $addressSet;
    }
}
