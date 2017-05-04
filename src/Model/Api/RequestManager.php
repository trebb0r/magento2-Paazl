<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Api;

use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Paazl\Shipping\Helper\Log as LogHelper;
use Paazl\Shipping\Model\Api\Request as PaazlRequest;

class RequestManager
{
    /** Webshop Identifier config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_WEBSHOP_ID       = 'paazl/api/webshop_id';
    /** Password for API calls config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_PASSWORD         = 'paazl/api/password';
    /** Staging/Production config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_API_TYPE         = 'paazl/api/api_type';
    /** Debug mode config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_API_DEBUG        = 'paazl/debug/log';

    const XML_PATH_STORECONFIGURATION_PAAZL_DISPLAY_REAL_ERR = 'paazl/debug/display_real_error_msg';
    const XML_PATH_STORECONFIGURATION_PAAZL_CUSTOM_ERR       = 'paazl/debug/custom_error_msg';
    /** Staging/Production URL config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_PRODUCTION_WSDL  = 'paazl/api_advanced/production_url';
    const XML_PATH_STORECONFIGURATION_PAAZL_STAGING_WSDL     = 'paazl/api_advanced/staging_url';

    /** @var string $webshopId */
    protected $webshopId;

    /** @var string $token */
    protected $password;

    /** @var mixed */
    protected $apiType;

    /** @var bool */
    protected $debugMode;

    /** @var  */
    private $clientFactory;

    /** @var ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @var \Magento\Framework\Api\SimpleDataObjectConverter */
    protected $objectConverter;

    /** @var \Paazl\Shipping\Model\Api\PaazlSoapError */
    protected $paazlError;

    /** @var LogHelper */
    protected $log;

    /**
     * RequestManager constructor.
     * @param ClientFactory $clientFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter
     * @param PaazlSoapError $paazlError
     * @param LogHelper $log
     */
    public function __construct(
        ClientFactory $clientFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter,
        \Paazl\Shipping\Model\Api\PaazlSoapError $paazlError,
        LogHelper $log
    ) {
        $this->clientFactory = $clientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->objectConverter = $objectConverter;
        $this->paazlError = $paazlError;
        $this->log = $log;

        $this->debugMode = $this->scopeConfig->isSetFlag(self::XML_PATH_STORECONFIGURATION_PAAZL_API_DEBUG, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE);
        $this->apiType = $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_API_TYPE, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE);
        $this->webshopId = $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_WEBSHOP_ID, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE);
        $this->password =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_PASSWORD, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE);
        $this->showRealErrorMsg =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_DISPLAY_REAL_ERR, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE);
        $this->customErrorMsg =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_CUSTOM_ERR, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE);

    }

    /**
     * @param \Paazl\Shipping\Model\Api\Request $requestObject
     * @return \Paazl\Shipping\Model\Api\Request
     */
    public function doRequest(PaazlRequest $requestObject)
    {
        $errors = [];
        $clientConfig = $requestObject->getClientConfig();

        $options = (isset($clientConfig['options']))
            ? array_merge(['trace' => true], $clientConfig['options'])
            : ['trace' => true];

        $wsdl = $this->getWsdl($requestObject);
        $this->prepareFixedElements($requestObject);

        $client = $this->clientFactory->create(
            $wsdl,
            $options
        );

        try {
            if ($requestObject->getHeaders()) {
                $client->__setSoapHeaders($requestObject->getHeaders());
            }

            $response = $client->__soapCall(
                $requestObject->getMethod(),
                [$requestObject->getBody()]
            );

            if (isset($response->error) && !in_array($response->error->code, [1003,1053])) { // 1053 = missing permission for Paazl Perfect, 1003 = An order with this reference already exists
                $paazlError = [
                    'log_type'  =>  'Paazl Error',
                    'log_code'  =>  $response->error->code,
                    'message'   =>  $this->paazlError->getMessageByCode($response->error->code)
                ];
                //@todo Config
                if ($paazlError['log_code'] != 1004) { // 1004 = zipcode + house number combination is incorrect.
                    $this->log->write($paazlError);
                }
                $errors[] = $paazlError;
            }
            if ($this->debugMode) {
                $requestObject->setLastRequest($client->__getLastRequest());

                $paazlLog = [
                    'log_type'  =>  'Paazl Request: ' . $requestObject->getMethod(),
                    'log_code'  =>  0,
                    'message'   =>  print_r($requestObject->getBody(), true)
                ];
                $this->log->write($paazlLog);

                $paazlLog = [
                    'log_type'  =>  'Paazl Response: ' . $requestObject->getMethod(),
                    'log_code'  =>  0,
                    'message'   =>  print_r($response, true)
                ];
                $this->log->write($paazlLog);
            }

            // Response handling by custom "handler" classes?
            $responseArray = $this->objectConverter->convertStdObjectToArray($response);
            $requestObject->setResponse($responseArray);
        } catch (\Exception $e) {
            $errors = [$e->getMessage()];
            $requestObject->setLastRequest($client->__getLastRequest());
        }

        $requestObject->setErrors($errors);
        if (count($errors)) {
            if ($this->showRealErrorMsg) {
                $throwMsg = $errors[0]['message'];
            }
            else {
                $throwMsg = $this->customErrorMsg;
            }

            throw new \Magento\Framework\Exception\LocalizedException(
                __($throwMsg)
            );
        }

        return $requestObject;
    }

    /**
     * @param $requestObject
     * @return mixed
     */
    private function getWsdl($requestObject)
    {
        $clientConfig = $requestObject->getClientConfig();

        $wsdlConfigPath = ($this->apiType)
            ? (isset($clientConfig['wsdl'])) ? : self::XML_PATH_STORECONFIGURATION_PAAZL_PRODUCTION_WSDL
            : (isset($clientConfig['wsdl_staging'])) ? : self::XML_PATH_STORECONFIGURATION_PAAZL_STAGING_WSDL;

        $wsdl = $this->scopeConfig->getValue($wsdlConfigPath);

        return $wsdl;
    }

    /**
     * @param $requestObject
     */
    private function prepareFixedElements(&$requestObject)
    {
        $clientConfig = $requestObject->getClientConfig();
        $context = (string)$requestObject->getContext();

        $body = $requestObject->getBody();
        if ($requestObject->getMethod() == 'listOrders') {
            $context = date('Ymd');
        }

        $body['hash'] = sha1($this->webshopId . $this->password . $context);
        $body['webshop'] = (isset($clientConfig['webshop'])) ? $clientConfig['webshop'] : $this->webshopId;

        $requestObject->setBody($body);
    }

    /**
     * @param $context
     * @return string
     */
    public function getHash($context) {
        return sha1($this->webshopId . $this->password . $context);
    }
}
