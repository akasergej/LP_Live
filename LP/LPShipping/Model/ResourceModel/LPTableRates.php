<?php

namespace LP\LPShipping\Model\ResourceModel;

use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LPTableRates extends AbstractDb
{
    use TableRate;

    /**
     * @var \Magento\Framework\Filesystem $_fileSystem
     */
    private $_fileSystem;

    /**
     * @var \LP\LPShipping\Model\LPTableRatesFactory $tableRatesFactory
     */
    private $tableRatesFactory;

    /**
     * LPTableRates constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \LP\LPShipping\Model\LPTableRatesFactory $LPTableRatesFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Filesystem                     $filesystem,
        \LP\LPShipping\Model\LPTableRatesFactory     $LPTableRatesFactory,
                                                          $connectionName = null
    )
    {
        $this->_fileSystem = $filesystem;
        $this->tableRatesFactory = $LPTableRatesFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lp_table_rates', 'id');
    }

    /**
     * Upload csv file and import to main table
     */
    public function uploadAndImport()
    {
        $tmpPath = $_FILES ['groups']['tmp_name']['lpcarrier']['groups']['lpcarriershipping_lp']
                   ['fields']['import_rates']['value'];
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($tmpPath)) {
            return;
        }

        $file = $this->getCsvFile($tmpPath);
        $data = $this->getData($file);

        if (!empty($data) && $this->validate($data)) {
            // Truncate data
            $this->deleteByCondition([]);
            foreach ($data as $rate) {
                $this->tableRatesFactory->create()
                    ->setCountry($rate['country'])
                    ->setWeightTo($rate['weight_to'])
                    ->setP2hUntrackedPrice($rate['p2h_untracked_price'])
                    ->setP2hTrackedPrice($rate['p2h_tracked_price'])
                    ->setP2hSignedPrice($rate['p2h_signed_price'])
                    ->save();
            }
        }
    }

    /**
     * Get data from csv file
     * @param ReadInterface $file
     * @return array
     */
    private function getData(ReadInterface $file): array
    {
        $data = [];
        $line = 0;
        while (false !== ($csvLine = $file->readCsv())) {
            if (++$line === 1) {
                continue;
            }
            if (empty($csvLine[1])) {
                //try other delimiter
                $csvLine = explode(';', $csvLine[0]);
                //if still not numeric, might be that excel added an extra header to file, skip this line
                if (empty($csvLine[1]) || !is_numeric($csvLine[1])) {
                    continue;
                }
            }

            $data[] = [
                'country'             => empty($csvLine[0]) ? null : strtoupper($csvLine[0]),
                'weight_to'           => $csvLine[1],
                'p2h_untracked_price' => $csvLine[2],
                'p2h_tracked_price'   => $csvLine[3],
                'p2h_signed_price'    => $csvLine[4],
            ];
        }

        return $data;
    }
}
