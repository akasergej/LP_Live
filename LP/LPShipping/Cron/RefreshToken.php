<?php

namespace LP\LPShipping\Cron;

class RefreshToken
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
     * RefreshToken constructor.
     * @param \LP\LPShipping\Helper\ApiHelper $apiHelper
     * @param \LP\LPShipping\Model\Config $config
     */
    public function __construct(
        \LP\LPShipping\Helper\ApiHelper $apiHelper,
        \LP\LPShipping\Model\Config $config
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_config = $config;
    }

    /**
     * Execute refresh token
     */
    public function execute()
    {
        if ($this->_config->isEnabled()) {
            $this->_apiHelper->requestRefreshToken();
        }
    }
}
