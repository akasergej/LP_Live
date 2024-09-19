<?php

namespace LP\LPShipping\Plugin\Adminhtml;

/**
 * @property \Magento\Framework\App\RequestInterface _request
 */
class ActionButtons
{
    /**
     * @var \Magento\Backend\Model\UrlInterface $_backendUrl
     */
    protected $_backendUrl;

    /**
     * @var \LP\LPShipping\Model\Config $_config
     */
    protected $_config;

    /**
     * @var \LP\LPShipping\Helper\ShippingTemplate $_shippingTemplateHelper
     */
    protected $_shippingTemplateHelper;

    public $_request;

    /**
     * ActionButtons constructor.
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \LP\LPShipping\Helper\ShippingTemplate $shippingTemplateHelper
     * @param \LP\LPShipping\Model\Config $config
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \LP\LPShipping\Helper\ShippingTemplate $shippingTemplateHelper,
        \LP\LPShipping\Model\Config $config
    ) {
        $this->_backendUrl              = $backendUrl;
        $this->_shippingTemplateHelper  = $shippingTemplateHelper;
        $this->_config                  = $config;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Button\ToolbarInterface $subject
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     */
    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\ToolbarInterface $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $this->_request = $context->getRequest();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = null;

        if ($this->_request->getFullActionName() == 'adminhtml_order_shipment_view') {
            $shipmentCollection = $objectManager->create('Magento\Sales\Model\Order\Shipment');
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $shipmentCollection->load($this->_request->getParam('shipment_id'));

            $orderRepository = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderRepository->get($shipment->getOrderId());
        }

        if ($this->_request->getFullActionName() == 'sales_order_view') {
            $orderRepository = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderRepository->get($this->_request->getParam('order_id'));
        }

        if ($order) {
            // Do not execute if not LP or LPEXPRESS method
            if (!$this->_config->isLpMethod($order->getShippingMethod()) &&
                !$this->_config->isLpExpressMethod($order->getShippingMethod())) {
                return;
            }
        }

        if ($this->_request->getFullActionName() == 'sales_order_view') {
            $buttonList->add(
                'lpshipping_ship',
                [
                    'label' => __('Ship'),
                    'onclick' => 'setLocation("' . $this->getCreateShipmentLabelUrl($order->getId()) . '")',
                    'class' => sprintf(
                        'action-secondary lpbutton primary %s',
                        $order->getLpShippingItemId() ? 'hidden' : ''
                    )
                ],
                -1
            );
            $buttonList->add(
                'LP_LPShipping_create_shipment',
                [
                    'label' => __('Edit Shipment'),
                    'onclick' => 'LP_LPShipping_Shipment_Modal.modal(\'openModal\')',
                    'class' => sprintf(
                        'action-secondary lpbutton primary %s',
                        $order->getLpShippingItemId() ? 'hidden' : ''
                    )
                ],
                -1
            );
        }

        if ($this->_request->getFullActionName() == 'adminhtml_order_shipment_view') {
            $buttonList->add(
                'LP_LPShipping_create_shipment',
                [
                    'label' => __('Edit Shipment'),
                    'onclick' => 'LP_LPShipping_Shipment_Modal.modal(\'openModal\')',
                    'class' => sprintf(
                        'action-secondary lpbutton primary %s',
                        $order->getLpShippingItemId() ? 'hidden' : ''
                    )
                ],
                -1
            );

            if ($this->_config->isCallCourierAllowed($order)) {
                $buttonList->add(
                    'LP_LPShipping_callcourier',
                    [
                        'label' => __('Call Courier'),
                        'onclick' => 'setLocation("' . $this->getCallCourierActionUrl(
                            $order->getLpShippingItemId(),
                            $order->getId()
                        ) . '")',
                        'class' => sprintf(
                            'action-secondary lpbutton primary %s',
                            $order->getLpShippingItemId() == null ? 'disabled' : ''
                        )
                    ],
                    -1
                );
            }

            $actionListOptions = [
                'all_documents' => [
                    'label'=>__('Print All Documents'),
                    'onclick'=> 'setLocation("' . $this->getPrintAllDocumentsActionUrl($order->getId()) . '")',
                ]
            ];

            if ($this->_config->isCallCourierAllowed($order)) {
                $actionListOptions [ 'manifest' ] = [
                        'label'=> __('Print Manifest'),
                        'onclick'=> 'setLocation("' . $this->getManifestActionUrl($order->getId()) . '")',
                ];
            }

            if ($this->_shippingTemplateHelper->isCN23($order, null)) {
                $actionListOptions [ 'cn23_print' ] = [
                        'label' => __('Print CN23 Declaration'),
                        'onclick' => 'setLocation("' . $this->getPrintC23FormActionUrl($order->getId()) . '")'
                ];
            }

            $buttonList->add(
                'LP_LPShipping_documents_actionlist',
                [
                    'label' => __('Documents'),
                    'class' => 'lpbutton',
                    'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'options' => $actionListOptions
                ],
                -1
            );
        }
    }

    /**
     * @param $orderId
     * @return string
     */
    private function getPrintC23FormActionUrl($orderId)
    {
        return $this->_backendUrl->getUrl(sprintf(
            'lp_action/action/printcn23form/order/%d',
            $orderId
        ));
    }

    /**
     * @param $orderId
     * @return string
     */
    private function getManifestActionUrl($orderId)
    {
        return $this->_backendUrl->getUrl(sprintf(
            'lp_action/action/printmanifest/order/%d',
            $orderId
        ));
    }

    /**
     * @param $shippingItemId
     * @param $orderId
     * @return string
     */
    private function getCallCourierActionUrl($shippingItemId, $orderId)
    {
        return $this->_backendUrl->getUrl(sprintf(
            'lp_action/action/callcourier/itemid/%d/orderid/%d',
            $shippingItemId,
            $orderId
        ));
    }

    private function getPrintAllDocumentsActionUrl($orderId)
    {
        return $this->_backendUrl->getUrl(sprintf(
            'lp_action/action/printalldocuments/order/%d',
            $orderId
        ));
    }

    private function getCreateShipmentLabelUrl($orderId)
    {
        return $this->_backendUrl->getUrl(sprintf('lp_action/action/createshippinglabel/orderid/%d', $orderId));
    }
}
