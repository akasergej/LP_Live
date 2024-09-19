<?php

declare(strict_types=1);

namespace LP\LPShipping\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

class Filter extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    public function __construct(
        Context $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        JsonFactory $jsonFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;

        parent::__construct($context);
    }

    /**
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        $bookmarks = $this->bookmarkManagement->loadByNamespace('lp_shipping_listing');
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getIdentifier() === 'current') {
                $bookmark->setConfig(json_encode($this->applyFilter($this->getRequest()->getParam('filter'), $bookmark->getConfig())));
            }

            $this->bookmarkRepository->save($bookmark);
        }

        $resultJson = $this->jsonFactory->create();

        return $resultJson->setData(['message' => 'Filter applied']);
    }

    /**
     * @param string $filter
     * @param array $config
     * @return array
     */
    private function applyFilter($filter, $config)
    {
        unset($config['current']['filters']['applied']['status']);
        if (empty($filter)) {

            return $config;
        }

        $config['current']['filters']['applied']['status'][] = $filter;

        return $config;
    }
}
