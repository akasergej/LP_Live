<?php

declare(strict_types=1);

namespace LP\LPShipping\Controller\Address;

use LP\LPShipping\Helper\ApiHelper;
use Laminas\Http\Response;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Validate extends Action
{
    private $resultJsonFactory;
    private $request;
    private $apiHelper;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        RequestInterface $request,
        ApiHelper $apiHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->apiHelper = $apiHelper;
    }

    public function execute()
    {
        $valid = $this->validateAddress($this->request->getParams());

        return ($this->resultJsonFactory->create())->setData([
            'status' => $valid,
            'errorMessage' => !$valid ? __('Address not found, please check entered address fields.') : '',
        ]);
    }

    private function validateAddress(array $addressData): bool
    {
        $addressFieldsForApi = [
            'name' => $addressData['firstname'],
            'address' => [
                'locality' => $addressData['city'] ?? '',
                'address' => $addressData['address'] ?? '',
                'postalCode' => $addressData['postcode'] ?? '',
                'countryCode' => $addressData['country_id'] ?? '',
            ],
        ];
        $status = $this->apiHelper->validateAddress($addressFieldsForApi);

        return $status && $status === Response::STATUS_CODE_200;
    }
}
