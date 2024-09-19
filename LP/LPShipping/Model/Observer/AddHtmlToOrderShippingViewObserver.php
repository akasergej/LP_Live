<?php

namespace LP\LPShipping\Model\Observer;

use LP\LPShipping\Api\CN22RepositoryInterface;
use LP\LPShipping\Api\CN23RepositoryInterface;
use LP\LPShipping\Api\LPExpressTerminalRepositoryInterface;
use LP\LPShipping\Api\SenderRepositoryInterface;
use LP\LPShipping\Helper\ApiHelper;
use LP\LPShipping\Helper\ShippingHelper;
use LP\LPShipping\Helper\ShippingTemplate;
use LP\LPShipping\Model\Config;
use LP\LPShipping\Model\Config\Source\AvailableCountries;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class AddHtmlToOrderShippingViewObserver implements ObserverInterface
{
    /**
     * @var Template $_block
     */
    protected $_block;

    /**
     * @var LPExpressTerminalRepositoryInterface $_terminalRepository
     */
    protected $_terminalRepository;

    /**
     * @var ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * @var UrlInterface $_backendUrl
     */
    protected $_backendUrl;

    /**
     * @var CN22RepositoryInterface $_CN22
     */
    protected $_CN22;

    /**
     * @var CN23RepositoryInterface $_CN23
     */
    protected $_CN23;

    /**
     * @var SenderRepositoryInterface $_senderRepository
     */
    protected $_senderRepository;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var AvailableCountries $_availableCountries
     */
    protected $_availableCountries;

    /**
     * @var ShippingTemplate $_shippingTemplateHelper
     */
    protected $_shippingTemplateHelper;

    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    /**
     * AddHtmlToOrderShippingViewObserver constructor.
     */
    public function __construct(
        Template $block,
        UrlInterface $backendUrl,
        LPExpressTerminalRepositoryInterface $terminalRepository,
        ApiHelper $apiHelper,
        CN22RepositoryInterface $CN22,
        CN23RepositoryInterface $CN23,
        SenderRepositoryInterface $senderRepository,
        AvailableCountries $availableCountries,
        ShippingTemplate $shippingTemplateHelper,
        Config $config,
        ShippingHelper $shippingHelper
    ) {
        $this->_block = $block;
        $this->_terminalRepository = $terminalRepository;
        $this->_apiHelper = $apiHelper;
        $this->_backendUrl = $backendUrl;
        $this->_CN22 = $CN22;
        $this->_CN23 = $CN23;
        $this->_senderRepository = $senderRepository;
        $this->_availableCountries = $availableCountries;
        $this->_config = $config;
        $this->_shippingTemplateHelper = $shippingTemplateHelper;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Pass additional template to the order_view and shipment_view
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $shipment = $order = null;
        $orderShippingViewBlock = $observer->getLayout()
            ->getBlock('order_shipping_view') ?: $observer->getLayout()
                ->getBlock('shipment_tracking');

        if ($orderShippingViewBlock) {
            $order = $orderShippingViewBlock->getOrder();
        }

        // If in shipment page
        if ($order === null && $orderShippingViewBlock != null) {
            $shipId = $orderShippingViewBlock->getRequest()->getParam('shipment_id');
            if ($shipId !== null) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $shipmentCollection = $objectManager->create('Magento\Sales\Model\Order\Shipment');
                $shipment = $shipmentCollection->load($shipId);
                $order = $shipment->getOrder();
            }
        }

        if ($order) {
            // Do not execute if not LP or LPEXPRESS method
            if (!$this->_config->isLpMethod($order->getShippingMethod()) &&
                !$this->_config->isLpExpressMethod($order->getShippingMethod())) {
                return;
            }
        }

        // Show terminal on order or shipment page
        if ($observer->getElementName() === 'order_shipping_view' || $observer->getElementName() === 'shipment_tracking') {

            /** @var Order $order */
            if ($order !== null) {
                $block = $this->_block;
                if ($order->getLpexpressTerminal() !== null && ShippingHelper::isTerminalShippingMethod($order->getShippingMethod())) {

                    // Get terminal by terminal_id from terminal collection
                    $terminal = $this->_terminalRepository
                        ->getByTerminalId($order->getLpexpressTerminal());

                    if ($terminal !== null) {
                        $block->setMethodName(__('Terminal'));
                        $block->setMethodInfo(
                            !$terminal->isEmpty() ? sprintf(
                                '%s - %s',
                                $terminal->getName(),
                                $terminal->getAddress()
                            )
                                : __('Something wen\'t wrong here..')
                        );

                        if ($shipment !== null) {
                            echo sprintf(
                                '<b>' . __('Terminal') . '</b> %s - %s',
                                $terminal->getName(),
                                $terminal->getAddress()
                            );
                        }
                    }
                }

                $block->setTemplate('LP_LPShipping::order_info_shipping_info.phtml');

                // Set output to order view
                $html = $observer->getTransport()->getOutput() . $block->toHtml();
                $observer->getTransport()->setOutput($html);
            }
        }

        // Shipment modalbox
        if (strpos($observer->getElementName(), 'LP_LPShipping_create_shipment')) {
            $block               = $this->_block;
            $availableCountries  = $this->_availableCountries;

            $sender = $this->_senderRepository->getByOrderId($order->getEntityId());

            $block->setData([
                'order' => $order,
                'actionUrl' => $this->_backendUrl->getUrl('lp_action/action/saveshipmentdetails'),
                'countries' => $availableCountries,
                'terminals' => $this->_terminalRepository->getList(),
                'packageQuantity' => $order->getLpShippingPackageQuantity() ?? 1,
                'isCodAvailable' => $this->shippingHelper->isCodAvailable($order),
                'weight' => ($order->getLpPackageWeight() ?: $this->shippingHelper->getQuoteWeight($order->getAllItems(), true)),
                'size' => $order->getLpShippingSize() ?: $this->_config->getDefaultShipmentSize($order->getShippingMethod()),
                'availableSizes' => $this->shippingHelper->getAvailableSizes($order),
                'sender' => [
                    'name' => $sender->getName() ?? $this->shippingHelper->getSenderName(),
                    'phone' => $sender->getPhone() ?? $this->shippingHelper->getSenderPhone(),
                    'email' => $sender->getEmail() ?? $this->shippingHelper->getSenderEmail(),
                    'country' => $sender->getCountryId() ?? $this->shippingHelper->getSenderCountryId(),
                    'city' => $sender->getCity() ?? $this->shippingHelper->getSenderCity(),
                    'street' => $sender->getStreet() ?? $this->shippingHelper->getSenderStreet(),
                    'building' => $sender->getBuildingNumber() ?? $this->shippingHelper->getSenderBuilding(),
                    'apartment' => $sender->getApartment() ?? $this->shippingHelper->getSenderApartment(),
                    'postcode' => $sender->getPostcode() ?? $this->shippingHelper->getSenderPostCode(),
                    'addressLine1' => $sender->getAddressLine1() ?? $this->shippingHelper->getSenderAddressLine1(),
                    'addressLine2' => $sender->getAddressLine2() ?? $this->shippingHelper->getSenderAddressLine2(),
                ],
                'CN22' => $this->_shippingTemplateHelper->isCN22($order)
                    ? $this->_CN22->getByOrderId($order->getId())
                    : null,

                'CN23' => $this->_shippingTemplateHelper->isCN23($order)
                    ? $this->_CN23->getByOrderId($order->getId())
                    : null,
            ]);

            $block->setTemplate('LP_LPShipping::form_shipment_modalbox.phtml');

            // Set output to order view
            $html = $observer->getTransport()->getOutput() . $block->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}
