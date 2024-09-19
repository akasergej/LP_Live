<?php

namespace LP\LPShipping\Helper;

use LP\LPShipping\Plugin\SaveConfigPlugin;
use LP\LPShipping\Service\Curl;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Message\ManagerInterface;
use LP\LPShipping\Model\ApiToken;
use LP\LPShipping\Model\ApiTokenFactory;
use LP\LPShipping\Model\Config;

class ApiHelper extends AbstractHelper
{
    const AUTH_DEFAULT_GATEWAY = 'https://api-manosiuntos.post.lt/oauth/token';

    const DEFAULT_GATEWAY = 'https://api-manosiuntos.post.lt/api/v2';

    /**
     * @var Curl $_clientFactory
     */
    protected $_client;

    /**
     * @var Config $_config
     */
    protected $_config;

    /**
     * @var ManagerInterface $_messageManger
     */
    protected $_messageManger;

    /**
     * @var ApiTokenFactory $_apiTokenFactory
     */
    protected $_apiTokenFactory;

    /**
     * @var array $shippingPlansCached
     */
    private $shippingPlansCached;

    public function __construct(
        Context $context,
        Curl $client,
        ManagerInterface $messageManager,
        Config $config,
        ApiTokenFactory $apiTokenFactory
    ) {
        $this->_client = $client;
        $this->_config = $config;
        $this->_messageManger = $messageManager;
        $this->_apiTokenFactory = $apiTokenFactory;

        parent::__construct($context);
    }

    private function getApiGateway(): string
    {
        return self::DEFAULT_GATEWAY;
    }

    private function getAuthGateway()
    {
        return self::AUTH_DEFAULT_GATEWAY;
    }

    /**
     * @param $params
     * @return Curl
     */
    private function tokenRequest($params)
    {
        $this->_client->setOption(CURLOPT_HEADER, 0);
        $this->_client->setOption(CURLOPT_TIMEOUT, 120);
        $this->_client->setOption(CURLOPT_RETURNTRANSFER, true);

        $this->_client->addHeader('Accept', 'application/json');
        $this->_client->post($this->getAuthGateway(), $params);

        return $this->_client;
    }

    /**
     * Send authorized request to API endpoint
     *
     * @param $endpoint
     * @param $params
     * @param string $requestMethod
     * @return Curl|null
     */
    private function doRequest($endpoint, $params = [], $requestMethod = Request::METHOD_GET, $headers = [], bool $failSilently = false)
    {
        try {
            $this->_client->setOption(CURLOPT_HEADER, 0);
            $this->_client->setOption(CURLOPT_TIMEOUT, 10);
            $this->_client->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->_client->setOption(CURLOPT_CUSTOMREQUEST, $requestMethod);

            if (empty($headers)) {
                $this->_client->addHeader('Content-Type', 'application/json');
                $this->_client->addHeader('Accept', 'application/json');
            }

            foreach ($headers as $name => $value) {
                $this->_client->addHeader($name, $value);
            }

            $this->_client->addHeader(
                'Authorization',
                sprintf('%s %s', 'Bearer', $this->getAccessToken())
            );

            $method = strtolower($requestMethod);
            if ($requestMethod === Request::METHOD_GET || $requestMethod === Request::METHOD_DELETE) {
                if (empty($params)) {
                    $this->_client->$method(sprintf('%s/%s', $this->getApiGateway(), $endpoint));
                } else {
                    $this->_client->$method(sprintf('%s/%s/?%s', $this->getApiGateway(), $endpoint, http_build_query($params)));
                }
            } else {
                $this->_client->$method(
                    sprintf('%s/%s', $this->getApiGateway(), $endpoint),
                    json_encode($params)
                );
            }

            return $this->_client;
        } catch (\Exception $e) {
            if (!$failSilently) {
                $this->_messageManger->addErrorMessage($e->getMessage());
            }
        }

        return null;
    }

    /**
     * Get LP EXPRESS full terminal list
     * @return mixed
     */
    public function getLPExpressTerminalList(string $countryCode)
    {
        $request = $this->doRequest('terminal', ['receiverCountryCode' => $countryCode, 'size' => 999]);

        if ($request) {
            $requestResult = json_decode($request->getBody());
            if ($request->getStatus() === 200) {
                return $requestResult;
            } else {
                // Throw error if occurs
                $this->_messageManger->addErrorMessage(
                    $this->formatErrorMessage(
                        'Get Express Terminal List',
                        $request->getStatus(),
                        $requestResult ? $requestResult[0]->error_description : ''
                    )
                );
            }
        }

        return null;
    }

    /**
     * @param $requestData
     * @return int|null
     * @throws \Exception
     */
    public function createShippingItem($requestData)
    {
        $request = $this->doRequest(
            'parcel',
            $requestData,
            Request::METHOD_POST
        );

        if ($request && $request->getStatus() === 200) {
            $response = json_decode($request->getBody());

            if (!$response || !property_exists($response, 'parcelId')) {
                throw new \Exception(
                    __('Something wen\'t wrong. Please try again later.')
                );
            }

            return $response->parcelId;
        } else {
            throw new \Exception(
                __($request ? $request->getBody() : 'Something went wrong, try again')
            );
        }
    }

    /**
     * Get barcode
     * @param $requestId
     * @return string|null
     * @throws \Exception
     */
    public function getBarcode($requestId)
    {
        $request = $this->doRequest(sprintf('%s/%s', 'shipping/status', $requestId));
        if ($request && $request->getStatus() === 200) {
            $response = json_decode($request->getBody());
            if (!property_exists($response->items[0], 'barcode')) {
                throw new \Exception(
                    __('Something wen\'t wrong. Please try again later.')
                );
            }

            return $response->items[0]->barcode;
        } else {
            throw new \Exception(
                __($request ? $request->getBody() : 'Something went wrong, try again')
            );
        }
    }

    /**
     * @param array $shippingItemIds
     * @return string
     * @throws \Exception
     */
    public function initiateShipping($shippingItemIds)
    {
        $request = $this->doRequest(
            'shipping/initiate?processAsync=false',
            $shippingItemIds,
            Request::METHOD_POST
        );

        if ($request && $request->getStatus() === 200) {
            $response = json_decode($request->getBody());

            if (!$response || !property_exists($response, 'requestId')) {
                throw new \Exception(
                    __('Something wen\'t wrong. Please try again later.')
                );
            }

            return $response->requestId;
        } else {
            throw new \Exception(
                __($request ? $request->getBody() : 'Something went wrong, try again')
            );
        }
    }

    /**
     * @param $parcelIds
     * @return string|null
     */
    public function createSticker($parcelIds)
    {
        $request = $this->doRequest('sticker/pdf?parcelIds=' . implode('&parcelIds=', $parcelIds) . '&layout=' . $this->_config->getLabelSize(),
            [],
            Request::METHOD_GET,
            [
                'Content-Type' => 'application/pdf',
                'Accept' => 'application/pdf'
            ]
        );

        return $request && $request->getStatus() === Response::STATUS_CODE_200 ? $request->getBody() : null;
    }

    public function cancelShipment($order): bool
    {
        $parcelsToCancel = [$order->getLpShippingItemId()];
        if ($order->getLpReturnParcelId()) {
            $parcelsToCancel[] = $order->getLpReturnParcelId();
        }
        $response = $this->doRequest(
            'shipping/cancel', ['parcelIds' => $parcelsToCancel], Request::METHOD_POST);

        return $response && $response->getStatus() == Response::STATUS_CODE_200;
    }

    /**
     * @param $parcelIds
     * @return string|null
     */
    public function getManifest($parcelIds)
    {
        $request = $this->doRequest('courier/manifest/pdf',
            [
                'parcelIds' => $parcelIds,
            ],
            Request::METHOD_GET,
            [
                'Content-Type' => 'application/pdf',
                'Accept' => 'application/pdf'
            ]
        );

        return $request && $request->getStatus() === 200 ? $request->getBody() : null;
    }

    /**
     * @param $parcelIds
     * @return string|null
     */
    public function getCnForm($parcelIds)
    {
        $request = $this->doRequest(
            'documents/cn/pdf?parcelIds=' . implode('&parcelIds=', $parcelIds),
            [],
            Request::METHOD_GET,
            [
                'Content-Type' => 'application/pdf',
                'Accept' => 'application/pdf'
            ]
        );

        return $request && $request->getStatus() === Response::STATUS_CODE_200 ? $request->getBody() : null;
    }

    /**
     * @param array $parcelIds
     * @return bool
     */
    public function callCourier($parcelIds)
    {
        $request = $this->doRequest(
            'courier/call',
            [
                'parcelIds' => $parcelIds,
            ],
            Request::METHOD_POST
        );

        return $request && $request->getStatus() === 200;
    }

    /**
     * Used for tracking cronjob
     * @param $barcode
     * @return mixed
     */
    public function getTracking($barcode)
    {
        $request = $this->doRequest(sprintf('%s/%s', 'tracking/byBarcode', $barcode));

        if ($request) {
            if ($request->getStatus() === 200) {
                if ($tracking = json_decode($request->getBody())) {

                    return $tracking != null
                    && property_exists($tracking, 'state')
                    && $tracking->state != 'STATE_NOT_FOUND' ? $tracking : null;
                }

                return null;
            }
        }

        return null;
    }

    public function verifySenderAddress()
    {
        $response = $this->doRequest('address/validate?strict=false', [
            'name' => $this->_config->getSenderName(),
            'address' => [
                'locality' => $this->_config->getSenderCity(),
                'address' => $this->_config->getSenderStreet()
                    ? $this->_config->getSenderStreet() . ' ' . $this->_config->getSenderBuilding()
                    : $this->_config->getSenderAddressLine1()
                ,
                'postalCode' => $this->_config->getSenderPostCode(),
                'countryCode' => SaveConfigPlugin::getCountryCodeById((int) $this->_config->getSenderCountryId()),
            ],
        ], Request::METHOD_POST, [], true);

        if ($response && $response->getStatus() !== 200) {
            $body = json_decode($response->getBody(), true);
            $this->_messageManger->addErrorMessage(
                $this->formatErrorMessage(
                    'Verify Sender Address',
                    $response->getStatus(),
                    $body[0]['error_description'] ?? ''
                )
            );
        }
    }

    /**
     * Refresh token
     */
    public function requestRefreshToken()
    {
        /** @var ApiToken $apiTokenModel */
        $apiTokenModel = $this->_apiTokenFactory->create()->load(1);

        // If token expires
        date_default_timezone_set('Europe/Vilnius');

        // Send refresh token request
        $authResponse = $this->tokenRequest([
            'scope' => 'read+write',
            'grant_type' => 'password',
            'clientSystem' => 'PUBLIC',
            'username' => $this->_config->getApiUsername(),
            'password' => $this->_config->getApiPassword()
        ]);

        if ($authResponse->getStatus() === 200) {
            $accessTokenObject = json_decode($authResponse->getBody());

            if (property_exists($accessTokenObject, 'access_token')) {
                // Save refreshed token
                $apiTokenModel->setAccessToken($accessTokenObject->access_token)
                    ->setRefreshToken($accessTokenObject->refresh_token)
                    ->setExpires(date(
                        'Y-m-d H:i:s',
                        time() + $accessTokenObject->expires_in
                    ))
                    ->setUpdated(date('Y-m-d H:i:s'))
                    ->save();
            } else {
                // Throw error if no access token received
                $this->_messageManger->addErrorMessage($this->formatErrorMessage('Refresh Token', $authResponse->getStatus(), __('Error authorization')));
            }
        } else {
            // Throw error if occurs
            $this->_messageManger->addErrorMessage($this->formatErrorMessage('Refresh Token', $authResponse->getStatus(), __('Error authorization')));
        }
    }

    /**
     * @param $username
     * @param $password
     * @return mixed|null
     */
    public function requestAccessToken($username, $password)
    {
        $response = $this->tokenRequest([
            'scope' => 'read+write',
            'grant_type' => 'password',
            'clientSystem' => 'PUBLIC',
            'username' => $username,
            'password' => $password
        ]);

        if ($response->getStatus() === 200) {
            $authResponse = json_decode($response->getBody());

            if (property_exists($authResponse, 'access_token')) {
                // Return access token object
                return $authResponse;
            }
        } else {
            // Throw error if occurs
            $this->_messageManger->addErrorMessage($this->formatErrorMessage('Request Access Token', $response->getStatus(), __('Error authorization')));
        }

        return null;
    }

    /**
     * Get API access token from database
     * @return mixed
     */
    public function getAccessToken()
    {
        /** @var ApiToken $apiTokenModel */
        $apiTokenModel = $this->_apiTokenFactory->create()->load(1);

        return $apiTokenModel->getAccessToken();
    }

    public function getShippingPlans(): array
    {
        if ($this->shippingPlansCached) {
            return $this->shippingPlansCached;
        }
        $response = $this->doRequest('shipping/plan');
        if ($response && Response::STATUS_CODE_200 === $response->getStatus()) {
            return $this->shippingPlansCached = json_decode($response->getBody(), true);
        }

        return $this->shippingPlansCached = [];
    }

    /**
     * @param string|null $size
     *
     * @return array|null
     */
    public function getShippingAvailable(string $receiverCountryCode, string $planCode, string $parcelType, $weight, $size = null)
    {
        $parameters = compact('receiverCountryCode', 'planCode', 'parcelType', 'weight');
        if ($size) {
            $parameters['size'] = $size;
        }
        $response = $this->doRequest('shipping/available', $parameters);

        if ($response) {
            $parsedResponse = json_decode($response->getBody(), true);
            if ($response->getStatus() === Response::STATUS_CODE_200) {
                return $parsedResponse;
            } else {
                $this->_messageManger->addErrorMessage($this->formatErrorMessage(
                    'Is Shipping Available',
                    $response->getStatus(),
                    $parsedResponse['errors'][0]['error_description'] ?? ''
                ));
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getShippingPlanEstimate(string $receiverCountryCode, string $planCodes, string $parcelTypes)
    {
        $parameters = compact('receiverCountryCode', 'planCodes', 'parcelTypes');
        $response = $this->doRequest('shipping/estimate/plan', $parameters);
        if ($response) {
            $parsedResponse = json_decode($response->getBody());
            if ($response->getStatus() === Response::STATUS_CODE_200) {
                return $parsedResponse;
            } else {
                $this->_messageManger->addErrorMessage($this->formatErrorMessage(
                    'Get Shipping Plan Estimate',
                    $response->getStatus(),
                    $parsedResponse['errors'][0]['error_description'] ?? ''
                ));
            }
        }

        return null;
    }

    public function validateAddress(array $data)
    {
        $response = $this->doRequest('address/validate?strict=false', $data, Request::METHOD_POST, [], true);

        if ($response) {
            return $response->getStatus();
        }

        return null;
    }

    private function formatErrorMessage(string $method, int $statusCode, $message): string
    {
        $toReturn = $method . ' - ' . $statusCode;
        if (!empty($message)) {
            $toReturn .= ' - ' . $message;
        }

        return $toReturn;
    }
}
