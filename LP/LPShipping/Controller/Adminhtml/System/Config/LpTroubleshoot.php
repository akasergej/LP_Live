<?php

namespace LP\LPShipping\Controller\Adminhtml\System\Config;

class LpTroubleshoot extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
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
     * @var \Magento\Framework\Message\ManagerInterface $_messageManager
     */
    protected $_messageManager;

    /**
     * @var \LP\LPShipping\Model\ApiTokenFactory $_apiTokenFactory
     */
    protected $_apiTokenFactory;

    /**
     * Troubleshoot constructor.
     * @param \LP\LPShipping\Helper\ApiHelper $apiHelper
     * @param \LP\LPShipping\Model\ApiTokenFactory $apiTokenFactory
     * @param \LP\LPShipping\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param $sectionChecker
     */
    public function __construct(
        \LP\LPShipping\Helper\ApiHelper $apiHelper,
        \LP\LPShipping\Model\ApiTokenFactory $apiTokenFactory,
        \LP\LPShipping\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure
    ) {
        $this->_apiHelper           = $apiHelper;
        $this->_config              = $config;
        $this->_resourceConnection  = $resourceConnection;
        $this->_messageManager      = $messageManager;
        $this->_apiTokenFactory     = $apiTokenFactory;
        parent::__construct($context, $configStructure, null);
    }

    /**
     * Truncate table
     * @param $tableName
     */
    protected function truncateTable($tableName)
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
     * First recreate API token
     * If system goes down API token won't work
     * In API and Module tokens won't be the same
     */
    protected function recreateToken()
    {
        if ($accessTokenObject = $this->_apiHelper->requestAccessToken(
            $this->_config->getApiUsername(),
            $this->_config->getApiPassword()
        )) {
            // Truncate api credentials
            $this->truncateTable($this->_config->getApiTokenDbTableName());

            // If token expires
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
     * @inheritDoc
     */
    public function execute()
    {
        if ($this->recreateToken()) {
            $this->_messageManager->addSuccessMessage(
                __('Troubleshoot complete.')
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Error. Please check your credentials.')
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        return $resultRedirect;
    }
}
