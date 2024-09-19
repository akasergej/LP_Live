<?php

namespace LP\LPShipping\Model;

use LP\LPShipping\Api\LPExpressTerminalRepositoryInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use LP\LPShipping\Model\Config\Source\LPDeliveryMethods;
use LP\LPShipping\Model\Config\Source\LPExpressDeliveryMethods;

class CheckoutProvider extends AbstractHelper implements ConfigProviderInterface
{
    const TERMINAL_SHIPPING_METHODS = [
        'lpcarrier' . LPExpressDeliveryMethods::T2T_TERMINAL,
        'lpcarrier' . LPExpressDeliveryMethods::H2T_TERMINAL,
        'lpcarrier' . LPExpressDeliveryMethods::T2S_TERMINAL,
        'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::T2T_TERMINAL,
        'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::H2T_TERMINAL,
        'lpcarrier_lpcarrier' . LPExpressDeliveryMethods::T2S_TERMINAL,
    ];

    /**
     * @var LPExpressTerminalRepositoryInterface $_terminalCollection
     */
    protected $_terminalRepository;

    /**
     * @var Config $_config
     */
    protected $_config;

    public function __construct(
        LPExpressTerminalRepositoryInterface  $terminalRepository,
        Config                                $config,
        Context $context
    ) {
        $this->_terminalRepository = $terminalRepository;
        $this->_config = $config;

        parent::__construct($context);
    }

    /**
     * Retrieve assoc array of checkout configuration
     * Pass terminal list to the checkout frontend
     */
    public function getConfig(): array
    {
        return [
            'terminal' => [
                'list' => $this->_terminalRepository->getList()
            ],
            'terminal_shipping_methods' => self::TERMINAL_SHIPPING_METHODS,
            'lp_delivery_time' => [
                'lpcarrier' . LPExpressDeliveryMethods::H2H_HANDS => $this->_config->getDeliveryTimeByType(LPExpressDeliveryMethods::H2H_HANDS),
                'lpcarrier' . LPExpressDeliveryMethods::T2H_HANDS => $this->_config->getDeliveryTimeByType(LPExpressDeliveryMethods::T2H_HANDS),
                'lpcarrier' . LPExpressDeliveryMethods::T2T_TERMINAL => $this->_config->getDeliveryTimeByType(LPExpressDeliveryMethods::T2T_TERMINAL),
                'lpcarrier' . LPExpressDeliveryMethods::H2T_TERMINAL => $this->_config->getDeliveryTimeByType(LPExpressDeliveryMethods::H2T_TERMINAL),
                'lpcarrier' . LPExpressDeliveryMethods::T2S_TERMINAL => $this->_config->getDeliveryTimeByType(LPExpressDeliveryMethods::T2S_TERMINAL),
                'lpcarrier' . LPExpressDeliveryMethods::H2P_TRACKED_SIGNED => $this->_config->getDeliveryTimeByType(LPExpressDeliveryMethods::H2P_TRACKED_SIGNED),
                'lpcarrier' . LPDeliveryMethods::METHOD_UNTRACKED => $this->_config->getDeliveryTimeByType(LPDeliveryMethods::METHOD_UNTRACKED),
                'lpcarrier' . LPDeliveryMethods::METHOD_TRACKED => $this->_config->getDeliveryTimeByType(LPDeliveryMethods::METHOD_TRACKED),
                'lpcarrier' . LPDeliveryMethods::METHOD_SIGNED => $this->_config->getDeliveryTimeByType(LPDeliveryMethods::METHOD_SIGNED),
            ],
        ];
    }
}
