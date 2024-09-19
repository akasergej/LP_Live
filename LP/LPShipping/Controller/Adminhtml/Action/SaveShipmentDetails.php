<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Api\CN22RepositoryInterface;
use LP\LPShipping\Api\CN23RepositoryInterface;
use LP\LPShipping\Api\Data\CN22Interface;
use LP\LPShipping\Api\Data\CN23Interface;
use LP\LPShipping\Api\Data\SenderInterface;
use LP\LPShipping\Api\SenderRepositoryInterface;
use LP\LPShipping\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class SaveShipmentDetails extends Action
{
    /**
     * @var CN22RepositoryInterface $_CN22Repository
     */
    protected $_CN22Repository;

    /**
     * @var CN22Interface $_CN22
     */
    protected $_CN22;

    /**
     * @var CN23RepositoryInterface $_CN23Repository
     */
    protected $_CN23Repository;

    /**
     * @var CN23Interface $_CN23
     */
    protected $_CN23;

    /**
     * @var SenderRepositoryInterface $_senderRepository
     */
    protected $_senderRepository;

    /**
     * @var SenderInterface $_sender
     */
    protected $_sender;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * SaveShipmentDetails constructor.
     */
    public function __construct(
        CN22RepositoryInterface $CN22Repository,
        CN22Interface $CN22,
        CN23RepositoryInterface $CN23Repository,
        CN23Interface $CN23,
        SenderInterface $sender,
        SenderRepositoryInterface $senderRepository,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->_CN22Repository   = $CN22Repository;
        $this->_CN22             = $CN22;
        $this->_CN23Repository   = $CN23Repository;
        $this->_CN23             = $CN23;
        $this->_senderRepository = $senderRepository;
        $this->_sender           = $sender;
        $this->_config           = $config;
        $this->_orderRepository  = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest()->getParams();
        $order = $this->_orderRepository->get($request['order_id']);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        if ($order) {
            /**
             * Order shipment info
             */
            if (key_exists('shipping_type', $request)) {
                $order->setLpShippingType($request['shipping_type']);

                // If size not selected LP EXPRESS
                if ($this->_config->isLpExpressMethod($order->getShippingMethod())) {
                    if ($request['shipping_type'] != 'EBIN' && !key_exists('shipping_size', $request)) {
                        $this->messageManager->addErrorMessage(
                            __('Please select shipping size. Changes have not been saved.')
                        );

                        return $resultRedirect;
                    }
                }

                // For tracked types
                if ($request['shipping_type'] == 'SMALL_CORESPONDENCE_TRACKED') {
                    $request['shipping_size'] = 'Small';
                }

                if ($request['shipping_type'] == 'MEDIUM_CORESPONDENCE_TRACKED') {
                    $request['shipping_size'] = 'Medium';
                }
            }

            // Shipping Size
            if (key_exists('shipping_size', $request)) {
                $order->setLpShippingSize($request['shipping_size']);
            } else {
                $order->setLpShippingSize(null);
            }

            // COD Value
            if (key_exists('cod', $request)) {
                $order->setLpCod($request['cod']);
            }

            // LP Express terminal
            if (key_exists('terminal_id', $request)) {
                $order->setLpexpressTerminal($request['terminal_id']);
            }

            if (key_exists('package_weight', $request)) {
                $order->setLpPackageWeight($request['package_weight']);
            }

            // Package Quantity
            if (key_exists('package_quantity', $request)) {
                $order->setLpShippingPackageQuantity($request['package_quantity']);
            }

            $this->_orderRepository->save($order);

            /**
             * Sender info
             */
            if (key_exists('sender', $request)) {
                $requestSenderData = $request['sender'];
                $sender = $this->_senderRepository->getByOrderId($order->getEntityId());

                // If not exists create new
                if (!$sender->getId()) {
                    $sender = $this->_sender;
                }

                $sender->setOrderId($order->getEntityId());
                $sender->setName($requestSenderData['sender_name']);
                $sender->setPhone($requestSenderData['sender_phone']);
                $sender->setEmail($requestSenderData['sender_email']);
                $sender->setCountryId($requestSenderData['sender_country']);
                $sender->setCity($requestSenderData['sender_city']);
                $sender->setStreet($requestSenderData['sender_street']);
                $sender->setBuildingNumber($requestSenderData['sender_building']);
                $sender->setApartment($requestSenderData['sender_apartment']);
                $sender->setPostcode($requestSenderData['sender_postcode']);
                $sender->setAddressLine1($requestSenderData['addressLine1']);
                $sender->setAddressLine2($requestSenderData['addressLine2']);

                $this->_senderRepository->save($sender);
            }

            /**
             * CN22 Info
             */
            if (key_exists('cn22', $request)) {
                $requestCN22Data = $request['cn22'];
                $CN22 = $this->_CN22Repository->getByOrderId($order->getEntityId());

                // If not exists create new
                if (!$CN22->getId()) {
                    $CN22 = $this->_CN22;
                }

                $CN22->setOrderId($order->getEntityId());
                $CN22->setParcelType($requestCN22Data['parcel_type']);
                $CN22->setParcelDescription($requestCN22Data['parcel_description']);
                $CN22->setCnParts(json_encode($requestCN22Data['items']));

                $this->_CN22Repository->save($CN22);
            }

            /**
             * CN23 Info
             */
            if (key_exists('cn23', $request)) {
                $requestCN23Data = $request['cn23'];

                $CN23 = $this->_CN23Repository->getByOrderId($order->getEntityId());

                if (!$CN23->getId()) {
                    $CN23 = $this->_CN23;
                }

                $CN23->setOrderId($order->getEntityId());
                $CN23->setParcelType($requestCN23Data['parcel_type']);
                $CN23->setExporterCustomsCode($requestCN23Data['exporter_customs_code']);
                $CN23->setLicense($requestCN23Data['license']);
                $CN23->setCertificate($requestCN23Data['certificate']);
                $CN23->setInvoice($requestCN23Data['invoice']);
                $CN23->setNotes($requestCN23Data['notes']);
                $CN23->setFailureInstruction($requestCN23Data['failure_instruction']);
                $CN23->setImporterCode($requestCN23Data['importer_code']);
                $CN23->setImporterCustomsCode($requestCN23Data['importer_customs_code']);
                $CN23->setImporterEmail($requestCN23Data['importer_email']);
                $CN23->setImporterFax($requestCN23Data['importer_fax']);
                $CN23->setImporterPhone($requestCN23Data['importer_phone']);
                $CN23->setImporterTaxCode($requestCN23Data['importer_tax_code']);
                $CN23->setImporterVatCode($requestCN23Data['importer_vat_code']);
                $CN23->setDescription($requestCN23Data['description']);
                $CN23->setCnParts(json_encode($requestCN23Data['items']));

                $this->_CN23Repository->save($CN23);
            }

            $this->messageManager->addSuccessMessage(
                __('Shipment details has been successfully saved.')
            );
        }

        return $resultRedirect;
    }
}
