<?php

namespace LP\LPShipping\Controller\Adminhtml\Action;

use LP\LPShipping\Api\CN23RepositoryInterface;
use LP\LPShipping\Api\Data\CN23InterfaceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class SaveCN23Form extends Action
{
    /**
     * @var ManagerInterface $_messageManger
     */
    protected $_messageManger;

    /**
     * @var CN23InterfaceFactory $_CN23Factory
     */
    protected $_CN23Factory;

    /**
     * @var CN23RepositoryInterface $_CN23Repository
     */
    protected $_CN23Repository;

    /**
     * LpSaveCN23Form constructor.
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param CN23InterfaceFactory $CN23Factory
     * @param CN23RepositoryInterface $CN23Repository
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        CN23InterfaceFactory $CN23Factory,
        CN23RepositoryInterface $CN23Repository
    ) {
        $this->_messageManger = $messageManager;
        $this->_CN23Factory = $CN23Factory;
        $this->_CN23Repository = $CN23Repository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $CN23FormData = $this->getRequest()->getParams();

        if ($orderId = $CN23FormData['order']) {
            // Check if record exists
            $CN23 = $this->_CN23Repository->getByOrderId($orderId);
            if (!$CN23) {
                $CN23 = $this->_CN23Factory->create();
            }

            $CN23->setOrderId($orderId);
            $CN23->setCnParts(json_encode($CN23FormData['items']));
            $CN23->setDescription($CN23FormData['description']);
            $CN23->setCertificate($CN23FormData['certificate']);
            $CN23->setExporterCustomsCode($CN23FormData['exporter_customs_code']);
            $CN23->setFailureInstruction($CN23FormData['failure_instruction']);
            $CN23->setInvoice($CN23FormData['invoice']);
            $CN23->setImporterCode($CN23FormData['importer_code']);
            $CN23->setImporterCustomsCode($CN23FormData['importer_customs_code']);
            $CN23->setImporterEmail($CN23FormData['importer_email']);
            $CN23->setImporterFax($CN23FormData['importer_fax']);
            $CN23->setImporterPhone($CN23FormData['importer_phone']);
            $CN23->setImporterTaxCode($CN23FormData['importer_tax_code']);
            $CN23->setImporterVatCode($CN23FormData['importer_vat_code']);
            $CN23->setLicense($CN23FormData['license']);
            $CN23->setNotes($CN23FormData['notes']);
            $CN23->setParcelType($CN23FormData['parcel_type']);

            $this->_CN23Repository->save($CN23);

            $this->_messageManger->addSuccessMessage(
                __('CN23 form saved successfully. Now you can create your shipping label.')
            );
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
