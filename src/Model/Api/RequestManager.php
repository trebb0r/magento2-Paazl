<?php
/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Paazl\Shipping\Model\Api;

use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Paazl\Shipping\Helper\Log as LogHelper;
use Paazl\Shipping\Model\Api\Request as PaazlRequest;

class RequestManager
{
    /** Webshop Identifier config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_WEBSHOP_ID      = 'paazl/api/webshop_id';
    /** Password for API calls config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_PASSWORD        = 'paazl/api/password';
    /** Staging/Production config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_API_TYPE        = 'paazl/api/api_type';
    /** Debug mode config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_API_DEBUG       = 'paazl/debug/log';
    /** Staging/Production URL config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_PRODUCTION_WSDL = 'paazl/api_advanced/production_url';
    const XML_PATH_STORECONFIGURATION_PAAZL_STAGING_WSDL    = 'paazl/api_advanced/staging_url';

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

        $this->debugMode = $this->scopeConfig->isSetFlag(self::XML_PATH_STORECONFIGURATION_PAAZL_API_DEBUG);
        $this->apiType = $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_API_TYPE);
        $this->webshopId = $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_WEBSHOP_ID);
        $this->password =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_PASSWORD);
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

            if(isset($response->error)) {
                $paazlError = [
                    'log_type'  =>  'Paazl Error',
                    'log_code'  =>  $response->error->code,
                    'message'   =>  $this->paazlError->getMessageByCode($response->error->code)
                ];
                //@todo Config
                if ($paazlError['log_code'] != 1004) {
                    $this->log->write($paazlError);
                }
                $errors[] = $paazlError;
            }
            if ($this->debugMode) {
                $requestObject->setLastRequest($client->__getLastRequest());
            }

            // Response handling by custom "handler" classes?
            $responseArray = $this->objectConverter->convertStdObjectToArray($response);
            $requestObject->setResponse($responseArray);
        } catch (\Exception $e) {
            $errors = [$e->getMessage()];
            $requestObject->setLastRequest($client->__getLastRequest());
            //throw $e;
        }

        $requestObject->setErrors($errors);
        if (count($errors)) {
            //$this->log->write($errors);
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

        $body['hash'] = sha1($this->webshopId . $this->password . $context);
        $body['webshop'] = (isset($clientConfig['webshop'])) ? $clientConfig['webshop'] : $this->webshopId;

        $requestObject->setBody($body);
    }
}
