<?php

namespace LP\LPShipping\Api\Data;

interface CN23Interface
{
    const ORDER_ID                  = 'order_id';
    const CN_PARTS                  = 'cn_parts';
    const EXPORTER_CUSTOMS_CODE     = 'exporter_customs_code';
    const PARCEL_TYPE               = 'parcel_type';
    const LICENSE                   = 'license';
    const CERTIFICATE               = 'certificate';
    const INVOICE                   = 'invoice';
    const NOTES                     = 'notes';
    const FAILURE_INSTRUCTION       = 'failure_instruction';
    const IMPORTER_CODE             = 'importer_code';
    const IMPORTER_CUSTOMS_CODE     = 'importer_customs_code';
    const IMPORTER_EMAIL            = 'importer_email';
    const IMPORTER_FAX              = 'importer_fax';
    const IMPORTER_PHONE            = 'importer_phone';
    const IMPORTER_TAX_CODE         = 'importer_tax_code';
    const IMPORTER_VAT_CODE         = 'importer_vat_code';
    const DESCRIPTION               = 'description';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return int
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return int
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getCnParts();

    /**
     * @param string $cnParts
     * @return string
     */
    public function setCnParts($cnParts);

    /**
     * @return string
     */
    public function getExporterCustomsCode();

    /**
     * @param string $exporterCustomsCode
     * @return string
     */
    public function setExporterCustomsCode($exporterCustomsCode);

    /**
     * @return string
     */
    public function getParcelType();

    /**
     * @param string $parcelType
     * @return string
     */
    public function setParcelType($parcelType);

    /**
     * @return string
     */
    public function getLicense();

    /**
     * @param string $license
     * @return string
     */
    public function setLicense($license);

    /**
     * @return string
     */
    public function getCertificate();

    /**
     * @param string $certificate
     * @return string
     */
    public function setCertificate($certificate);

    /**
     * @return string
     */
    public function getInvoice();

    /**
     * @param string $invoice
     * @return string
     */
    public function setInvoice($invoice);

    /**
     * @return string
     */
    public function getNotes();

    /**
     * @param string $notes
     * @return string
     */
    public function setNotes($notes);

    /**
     * @return string
     */
    public function getFailureInstruction();

    /**
     * @param string $failureInstruction
     * @return string
     */
    public function setFailureInstruction($failureInstruction);

    /**
     * @return string
     */
    public function getImporterCode();

    /**
     * @param string $importerCode
     * @return string
     */
    public function setImporterCode($importerCode);

    /**
     * @return string
     */
    public function getImporterCustomsCode();

    /**
     * @param string $importerCustomsCode
     * @return string
     */
    public function setImporterCustomsCode($importerCustomsCode);

    /**
     * @return string
     */
    public function getImporterEmail();

    /**
     * @param string $importerEmail
     * @return string
     */
    public function setImporterEmail($importerEmail);

    /**
     * @return string
     */
    public function getImporterFax();

    /**
     * @param string $importerFax
     * @return string
     */
    public function setImporterFax($importerFax);

    /**
     * @return string
     */
    public function getImporterPhone();

    /**
     * @param string $importerPhone
     * @return string
     */
    public function setImporterPhone($importerPhone);

    /**
     * @return string
     */
    public function getImporterTaxCode();

    /**
     * @param string $importerTaxCode
     * @return string
     */
    public function setImporterTaxCode($importerTaxCode);

    /**
     * @return string
     */
    public function getImporterVatCode();

    /**
     * @param string $importerVatCode
     * @return string
     */
    public function setImporterVatCode($importerVatCode);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return string
     */
    public function setDescription($description);
}
