<?php

namespace LP\LPShipping\Helper;

use LP\LPShipping\Api\CN22RepositoryInterface;
use LP\LPShipping\Api\CN23RepositoryInterface;
use LP\LPShipping\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class ShippingTemplate extends AbstractHelper
{
    // Default constants for CN data
    const CN_PARCEL_TYPE = 'sell';
    const CN_PARCEL_DESCRIPTION = 'Sell';

    // Special drawing right coefficient
    const SDR_COF = 1.786;

    /**
     * @var ScopeConfigInterface $_config
     */
    protected $_config;

    /**
     * @var CN22RepositoryInterface $_CN22Repository
     */
    protected $_CN22Repository;

    /**
     * @var CN23RepositoryInterface $_CN23Repository
     */
    protected $_CN23Repository;

    public function __construct(
        CN22RepositoryInterface $CN22Repository,
        CN23RepositoryInterface $CN23Repository,
        Context $context,
        ScopeConfigInterface $config
    ) {
        $this->_config                      = $config;
        $this->_CN22Repository              = $CN22Repository;
        $this->_CN23Repository              = $CN23Repository;

        parent::__construct($context);
    }

    /**
     * @param $price
     *
     * @return float|int
     */
    public function getSDRValue($price)
    {
        return self::SDR_COF * $price;
    }

    /**
     * @return array
     */
    protected function getEuCountries()
    {
        $countries = $this->_config->getValue('general/country/eu_countries');

        return explode(',', $countries);
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    protected function getCNDataDefaults($order)
    {
        $cnParts = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $cnParts [] = [
                'summary' => $item->getName(),
                'amount' => $item->getPrice(),
                'currencyCode' => $order->getBaseCurrencyCode(),
                'weight' => max(($item->getWeight() / 1000 * intval($item->getQtyOrdered())), 1),
                'quantity' => intval($item->getQtyOrdered()),
                'hsCode' => '',
                'countryCode' => $this->_config->getValue(Config::CONFIG_PATH_SENDER_COUNTRY, ScopeInterface::SCOPE_WEBSITE),
            ];
        }

        return [
            'contentType' => self::CN_PARCEL_TYPE,
            'contentDescription' => self::CN_PARCEL_DESCRIPTION,
            'parts' => $cnParts
        ];
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isCN22($order)
    {
        return !in_array($order->getShippingAddress()->getCountryId(), $this->getEuCountries())
            && $this->getSDRValue($order->getGrandTotal()) < 300;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isCN23($order)
    {
        return !in_array($order->getShippingAddress()->getCountryId(), $this->getEuCountries())
            && $this->getSDRValue($order->getGrandTotal()) > 300;
    }

    /**
     * @param Order $order
     *
     * @return array|null
     */
    public function getCnData($order)
    {
        // CN22
        if ($this->isCN22($order)) {
            $CN22 = $this->_CN22Repository->getByOrderId($order->getId());
            if ($CN22->getId()) {
                $CN22FormData = [
                    'contentType' => $CN22->getParcelType(),
                    'contentDescription' => $CN22->getParcelDescription(),
                    'parts' => json_decode($CN22->getCnParts(), true),
                ];
            } else {
                $CN22FormData = $this->getCNDataDefaults($order);
            }

            return $CN22FormData;
        }

        // CN23
        if ($this->isCN23($order)) {
            $CN23 = $this->_CN23Repository->getByOrderId($order->getId());
            if ($CN23->getId()) {
                $CN23FormData = [
                    'contentType' => $CN23->getParcelType(),
                    'contentDescription' => $CN23->getDescription(),
                    'failureInstruction' => $CN23->getFailureInstruction(),
                    'importer' => [
                        'taxCode' => $CN23->getImporterTaxCode(),
                        'vatCode' => $CN23->getImporterVatCode(),
                        'code' => $CN23->getImporterCode(),
                        'contact' => [
                            'phone' => $CN23->getImporterPhone(),
                            'email' => $CN23->getImporterEmail(),
                            'fax' => $CN23->getImporterFax(),
                        ],
                        'customsRegistrationNo' => $CN23->getImporterCustomsCode(),
                    ],
                    'exporter' => [
                        'customsRegistrationNo' => $CN23->getExporterCustomsCode(),
                    ],
                    'documents' => [
                        'license' => $CN23->getLicense(),
                        'certificate' => $CN23->getCertificate(),
                        'invoice' => $CN23->getInvoice(),
                        'notes' => $CN23->getNotes(),
                    ],
                    'parts' => json_decode($CN23->getCnParts(), true),
                ];
            } else {
                $CN23FormData = $this->getCNDataDefaults($order);
            }

            return $CN23FormData;
        }

        return null;
    }
}
