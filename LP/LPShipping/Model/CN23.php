<?php

namespace LP\LPShipping\Model;

use LP\LPShipping\Api\Data\CN23Interface;
use Magento\Framework\Model\AbstractModel;

class CN23 extends AbstractModel implements CN23Interface
{
    public function _construct()
    {
        $this->_init(ResourceModel\CN23::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getCnParts()
    {
        return $this->getData(self::CN_PARTS);
    }

    /**
     * @inheritDoc
     */
    public function setCnParts($cnParts)
    {
        return $this->setData(self::CN_PARTS, $cnParts);
    }

    /**
     * @inheritDoc
     */
    public function getExporterCustomsCode()
    {
        return $this->getData(self::EXPORTER_CUSTOMS_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setExporterCustomsCode($exporterCustomsCode)
    {
        return $this->setData(self::EXPORTER_CUSTOMS_CODE, $exporterCustomsCode);
    }

    /**
     * @inheritDoc
     */
    public function getParcelType()
    {
        return $this->getData(self::PARCEL_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setParcelType($parcelType)
    {
        return $this->setData(self::PARCEL_TYPE, $parcelType);
    }

    /**
     * @inheritDoc
     */
    public function getLicense()
    {
        return $this->getData(self::LICENSE);
    }

    /**
     * @inheritDoc
     */
    public function setLicense($license)
    {
        return $this->setData(self::LICENSE, $license);
    }

    /**
     * @inheritDoc
     */
    public function getCertificate()
    {
        return $this->getData(self::CERTIFICATE);
    }

    /**
     * @inheritDoc
     */
    public function setCertificate($certificate)
    {
        return $this->setData(self::CERTIFICATE, $certificate);
    }

    /**
     * @inheritDoc
     */
    public function getInvoice()
    {
        return $this->getData(self::INVOICE);
    }

    /**
     * @inheritDoc
     */
    public function setInvoice($invoice)
    {
        return $this->setData(self::INVOICE, $invoice);
    }

    /**
     * @inheritDoc
     */
    public function getNotes()
    {
        return $this->getData(self::NOTES);
    }

    /**
     * @inheritDoc
     */
    public function setNotes($notes)
    {
        return $this->setData(self::NOTES, $notes);
    }

    /**
     * @inheritDoc
     */
    public function getFailureInstruction()
    {
        return $this->getData(self::FAILURE_INSTRUCTION);
    }

    /**
     * @inheritDoc
     */
    public function setFailureInstruction($failureInstruction)
    {
        return $this->setData(self::FAILURE_INSTRUCTION, $failureInstruction);
    }

    /**
     * @inheritDoc
     */
    public function getImporterCode()
    {
        return $this->getData(self::IMPORTER_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setImporterCode($importerCode)
    {
        return $this->setData(self::IMPORTER_CODE, $importerCode);
    }

    /**
     * @inheritDoc
     */
    public function getImporterCustomsCode()
    {
        return $this->getData(self::IMPORTER_CUSTOMS_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setImporterCustomsCode($importerCustomsCode)
    {
        return $this->setData(self::IMPORTER_CUSTOMS_CODE, $importerCustomsCode);
    }

    /**
     * @inheritDoc
     */
    public function getImporterEmail()
    {
        return $this->getData(self::IMPORTER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setImporterEmail($importerEmail)
    {
        return $this->setData(self::IMPORTER_EMAIL, $importerEmail);
    }

    /**
     * @inheritDoc
     */
    public function getImporterFax()
    {
        return $this->getData(self::IMPORTER_FAX);
    }

    /**
     * @inheritDoc
     */
    public function setImporterFax($importerFax)
    {
        return $this->setData(self::IMPORTER_FAX, $importerFax);
    }

    /**
     * @inheritDoc
     */
    public function getImporterPhone()
    {
        return $this->getData(self::IMPORTER_PHONE);
    }

    /**
     * @inheritDoc
     */
    public function setImporterPhone($importerPhone)
    {
        return $this->setData(self::IMPORTER_PHONE, $importerPhone);
    }

    /**
     * @inheritDoc
     */
    public function getImporterTaxCode()
    {
        return $this->getData(self::IMPORTER_TAX_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setImporterTaxCode($importerTaxCode)
    {
        return $this->setData(self::IMPORTER_TAX_CODE, $importerTaxCode);
    }

    /**
     * @inheritDoc
     */
    public function getImporterVatCode()
    {
        return $this->getData(self::IMPORTER_VAT_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setImporterVatCode($importerVatCode)
    {
        return $this->setData(self::IMPORTER_VAT_CODE, $importerVatCode);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }
}
