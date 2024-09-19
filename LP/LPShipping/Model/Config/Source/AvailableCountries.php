<?php

namespace LP\LPShipping\Model\Config\Source;

use LP\LPShipping\Model\ResourceModel\LPCountries\Collection;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Data\OptionSourceInterface;

class AvailableCountries implements OptionSourceInterface
{
    //LV, ET, LT
    const AVAILABLE_COUNTRIES = [229, 169, 118];

    /**
     * @var Collection $_availableCountryCollection
     */
    private $_availableCountryCollection;

    /**
     * @var CountryFactory $_countryFactory
     */
    private $_countryFactory;

    public function __construct(
        CountryFactory $countryFactory,
        Collection $availableCountryCollection
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_availableCountryCollection = $availableCountryCollection;
    }

    /**
     * Get country name by code from magento
     */
    private function getCountryNameByCode($countryCode): string
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);

        return $country->getName();
    }

    /**
     * For sender information need to add country ID as value from API
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $availableCountries = [];

        // Available LP countries
        foreach ($this->_availableCountryCollection->getItems() as $item) {
            if (!in_array($item->getCountryId(), self::AVAILABLE_COUNTRIES)) {
                continue;
            }
            $availableCountries[] = [
                'label' => $this->getCountryNameByCode($item->getCountryCode()),
                'value' => $item->getCountryId()
            ];
        }

        return $availableCountries;
    }

    /**
     * Need to add country code instead of ID for magento allowed countries
     * Also translated country names because API returns country name only in lithuanian
     * @return array
     */
    public function availableCountries(): array
    {
        $availableCountries = [];

        // Available LP countries with codes instead of ID
        foreach ($this->_availableCountryCollection->getItems() as $item) {
            if (!in_array($item->getCountryId(), self::AVAILABLE_COUNTRIES)) {
                continue;
            }
            $availableCountries[] = [
                'label' => $this->getCountryNameByCode($item->getCountryCode()),
                'value' => $item->getCountryCode()
            ];
        }

        return $availableCountries;
    }
}
