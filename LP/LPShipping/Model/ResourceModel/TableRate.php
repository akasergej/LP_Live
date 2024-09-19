<?php

declare(strict_types=1);

namespace LP\LPShipping\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\File\ReadInterface;

trait TableRate
{
    /**
     * Validate CSV file format
     *
     * @param $data
     * @return bool
     * @throws LocalizedException
     */
    public function validate($data)
    {
        $values = [];
        foreach ($data as $rate) {
            if ($rate['weight_to'] === null || $rate['weight_to'] === '') {
                throw new LocalizedException(
                    __('Weight cell in the csv file cannot be empty.')
                );
            }
            $values[$rate['country']][] = $rate['weight_to'];
        }

        foreach ($values as $weights) {
            // Check if there is any duplicates
            if (count(array_unique($weights)) < count($weights)) {
                throw new LocalizedException(
                    __('Please check for weight duplicates in the csv file.')
                );
            }
        }

        return true;
    }

    /**
     * Delete from main table by condition
     *
     * @param array $condition
     * @return $this
     * @throws LocalizedException
     */
    public function deleteByCondition(array $condition)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable(), $condition);
        $connection->commit();

        return $this;
    }

    /**
     * Open for reading csv file
     *
     * @param $filePath
     * @return ReadInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCsvFile($filePath)
    {
        $pathInfo = pathinfo($filePath);
        $dirName = isset($pathInfo['dirname']) ? $pathInfo ['dirname'] : '';
        $fileName = isset($pathInfo['basename']) ? $pathInfo ['basename'] : '';

        $directoryRead = $this->_fileSystem->getDirectoryReadByPath($dirName);

        return $directoryRead->openFile($fileName);
    }

    public function getRate($quoteWeight, string $shippingMethod, string $receiverCountry)
    {
        $weights = [];
        $mostExpensiveRateForCountry = 0;
        $rates = $this->tableRatesFactory->create()->getCollection()->getData();
        $priceDataForCountryExists = false;
        foreach ($rates as $rate) {
            if ($rate['country'] === $receiverCountry) {
                $priceDataForCountryExists = true;
                break;
            }
        }

        foreach ($rates as $key => $rate) {
            // Search for weight that fits
            // If we have no configuration for requested country, all configuration without a country is game
            if (
                ($rate['country'] === $receiverCountry || (!$priceDataForCountryExists && $rate['country'] === null))
            ) {
                if ($rate['weight_to'] >= $quoteWeight) {
                    $weights[$key] = $rate['weight_to'];
                } else {
                    $mostExpensiveRateForCountry = max($mostExpensiveRateForCountry, $rate[strtolower($shippingMethod) . '_price']);
                }
            }
        }

        if (!empty($weights)) {
            // Result is the minimum weight index
            $result = $rates[array_search(min($weights), $weights)];

            return $result[strtolower($shippingMethod) . '_price'];
        }

        return $mostExpensiveRateForCountry;
    }
}
