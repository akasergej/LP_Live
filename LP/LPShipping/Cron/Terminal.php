<?php

namespace LP\LPShipping\Cron;

use LP\LPShipping\Plugin\SaveConfigPlugin;

class Terminal
{
    /**
     * @var \LP\LPShipping\Helper\ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var \LP\LPShipping\Model\Config $_config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\ResourceConnection $_resourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \LP\LPShipping\Model\LPExpressTerminalsFactory $_terminalsFactory
     */
    protected $_terminalsFactory;

    /**
     * Terminal constructor.
     * @param \LP\LPShipping\Helper\ApiHelper $apiHelper
     * @param \LP\LPShipping\Model\Config $config
     * @param \LP\LPShipping\Model\LPExpressTerminalsFactory $terminalsFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \LP\LPShipping\Helper\ApiHelper $apiHelper,
        \LP\LPShipping\Model\Config $config,
        \LP\LPShipping\Model\LPExpressTerminalsFactory $terminalsFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_config = $config;
        $this->_terminalsFactory = $terminalsFactory;
        $this->_resourceConnection = $resourceConnection;
    }

    /**
     * Delete all terminals from database
     */
    protected function truncateTerminals()
    {
        $connection = $this->_resourceConnection->getConnection();
        $connection->query('TRUNCATE ' . $this->_resourceConnection->getTableName(
            $this->_config->getTerminalDbTableName()
        ));
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->_config->isEnabled()) {
            return;
        }
        $this->truncateTerminals();
        foreach (SaveConfigPlugin::SUPPORTED_COUNTRIES as $countryData) {
            if ($terminalList = $this->_apiHelper->getLPExpressTerminalList($countryData['code'])) {
                foreach ($terminalList as $terminal) {
                    /** @var \LP\LPShipping\Model\LPExpressTerminals $terminalListModel */
                    $terminalListModel = $this->_terminalsFactory->create();
                    $terminalListModel->setTerminalId($terminal->id)
                        ->setName($terminal->name)
                        ->setAddress($terminal->address)
                        ->setCity($terminal->city)
                        ->setCountryCode($countryData['code'])
                        ->save();
                }
            }
        }
    }
}
