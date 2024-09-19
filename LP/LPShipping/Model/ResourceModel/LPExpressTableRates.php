<?php

namespace LP\LPShipping\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LPExpressTableRates extends AbstractDb
{
    use TableRate;

    /**
     * @var \Magento\Framework\Filesystem $_fileSystem
     */
    private $_fileSystem;

    /**
     * @var \LP\LPShipping\Model\LPExpressTableRatesFactory $tableRatesFactory
     */
    private $tableRatesFactory;

    /**
     * LPExpressRates constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \LP\LPShipping\Model\LPExpressTableRatesFactory $LPExpressTableRatesFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context    $context,
        \Magento\Framework\Filesystem                        $filesystem,
        \LP\LPShipping\Model\LPExpressTableRatesFactory $LPExpressTableRatesFactory,
                                                             $connectionName = null
    )
    {
        $this->_fileSystem = $filesystem;
        $this->tableRatesFactory = $LPExpressTableRatesFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lpexpress_table_rates', 'id');
    }

    /**
     * Upload csv file and import to main table
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws LocalizedException
     */
    public function uploadAndImport()
    {
        $tmpPath = $_FILES ['groups']['tmp_name']['lpcarrier']['groups']['lpcarriershipping_lpexpress']
                   ['fields']['import_rates']['value'];
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($tmpPath)) {
            return $this;
        }

        $file = $this->getCsvFile($tmpPath);
        $data = $this->getData($file);


        if (!empty($data) && $this->validate($data)) {
            // Truncate data
            $this->deleteByCondition([]);

            foreach ($data as $rate) {
                /** @var \LP\LPShipping\Model\LPExpressTableRates $rates */
                $this->tableRatesFactory->create()
                    ->setCountry($rate['country'])
                    ->setWeightTo($rate['weight_to'])
                    ->setH2hHandsPrice($rate['h2h_hands_price'])
                    ->setT2hHandsPrice($rate['t2h_hands_price'])
                    ->setT2tTerminalPrice($rate['t2t_terminal_price'])
                    ->setH2tTerminalPrice($rate['h2t_terminal_price'])
                    ->setT2sTerminalPrice($rate['t2s_terminal_price'])
                    ->setH2pTrackedSignedPrice($rate['h2p_tracked_signed_price'])
                    ->save();
            }
        }

        return null;
    }

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
                'country' => empty($csvLine[0]) ? null : strtoupper($csvLine[0]),
                'weight_to' => $csvLine[1],
                'h2h_hands_price' => $csvLine[2],
                't2h_hands_price' => $csvLine[3],
                't2t_terminal_price' => $csvLine[4],
                'h2t_terminal_price' => $csvLine[5],
                't2s_terminal_price' => $csvLine[6],
                'h2p_tracked_signed_price' => $csvLine[7],
            ];
        }

        return $data;
    }
}
