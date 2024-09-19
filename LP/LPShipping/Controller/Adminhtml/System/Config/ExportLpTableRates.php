<?php

namespace LP\LPShipping\Controller\Adminhtml\System\Config;

use LP\LPShipping\Block\Adminhtml\Carrier\LPTableRates\Grid;
use Magento\Backend\App\Action\Context;
use Magento\Config\Controller\Adminhtml\System\AbstractConfig;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportLpTableRates extends AbstractConfig
{
    /**
     * File response
     *
     * @var FileFactory
     */
    protected $_fileFactory;

    public function __construct(
        Context $context,
        Structure $configStructure,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;

        parent::__construct($context, $configStructure, null);
    }

    /**
     * Export shipping table rates in csv format
     *
     * @return \Magento\Framework\App\ResponseInterface | \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $fileName = 'lp-tables.csv';

        /** @var $gridBlock Grid */
        $gridBlock = $this->_view->getLayout()->createBlock(Grid::class);

        /**
         * Return CSV file from grid
         */
        return $this->_fileFactory->create(
            $fileName,
            $gridBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
