<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model;

use Paazl\Shipping\Model\Api\RequestBuilder;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Framework\Xml\Security;
use Paazl\Shipping\Model\Api\Request as PaazlRequest;

/**
 * Paazl shipping
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /** Paazl carrier code */
    const CODE = 'paazl';

    /** @var RateRequest */
    protected $_request;

    /** @var \Magento\Shipping\Model\Rate\Result */
    protected $_result;

    /** @var array */
    protected $_paazlData = [];

    /** @var int */
    protected $_quoteId;

    /** @var string[] */
    protected $_errors = [];

    /** @var array */
    protected $_rates = [];

    /** @var string */
    protected $_code = self::CODE;

    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /** @var RequestBuilder */
    protected $_requestBuilder;

    /** @var Api\RequestManager */
    protected $_requestManager;

    /** @var \Paazl\Shipping\Helper\Utility\Address */
    protected $_addressHelper;

    /** @var \Paazl\Shipping\Helper\Request\Order */
    protected $_orderHelper;

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /**
     * @var \Magento\Quote\Api\Data\ShippingMethodExtensionFactory
     */
    protected $shippingMethodExtensionFactory;

    /**
     * Carrier constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param RequestBuilder $requestBuilder
     * @param Api\RequestManager $requestManager
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     * @param \Paazl\Shipping\Helper\Request\Order $orderHelper
     * @param \Paazl\Shipping\Model\PaazlManagement $_paazlManagement
     * @param \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        \Paazl\Shipping\Helper\Utility\Address $addressHelper,
        \Paazl\Shipping\Helper\Request\Order $orderHelper,
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_addressHelper = $addressHelper;
        $this->_orderHelper = $orderHelper;
        $this->_paazlManagement = $_paazlManagement;
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Collect and get rates
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $paazlData = $this->_checkoutSession->getPaazlData();
        $this->_paazlData = (!is_null($paazlData))
            ? $paazlData
            : ['orderReference' => false, 'requests' => [], 'results' => []];
        $this->_paazlManagement->setPaazlData($this->_paazlData);

        $this->setRequest($request);
        $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        $this->_checkoutSession->setPaazlData($this->_paazlManagement->getPaazlData());

        return $this->getResult();
    }

    /**
     * Prepare (API) requests
     * @param RateRequest $request
     * @return $this
     */
    public function setRequest(RateRequest $request)
    {
        $this->_paazlData = $this->_paazlManagement->setRequest($request);
        $this->_request = $this->_paazlManagement->getRequest();

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = [];
        $requestKey = (string)$this->_paazlManagement->getShoppingOptionKey();
        $this->_paazlData = $this->_paazlManagement->getPaazlData();

        // Read shippingOption response
        if (isset($this->_paazlData['results']['shippingOption'][$requestKey]['shippingOptions'])) {
            $shippingOptionNode =
                $this->_paazlData['results']['shippingOption'][$requestKey]['shippingOptions']['shippingOption'];

            /**
             * <shippingOption type="AVG">
             * <type>AVG</type>
             * <description>AVG</description>
             * <deliverySchemeLineId>0</deliverySchemeLineId>
             * <distributor>TNT</distributor>
             * <price>5.0</price>
             * <deliveryDayOfWeekRange>MONDAY_SATURDAY</deliveryDayOfWeekRange>
             * <cod>false</cod>
             * <insurance>false</insurance>
             * <shipperNotification>false</shipperNotification>
             * <deliveryDates>
             * <deliveryDate>2020-01-01+01:00</deliveryDate>
             * </deliveryDates>
             * </shippingOption>
             */

            $shippingOptions = (isset($shippingOptionNode['type'])) ? [$shippingOptionNode] : $shippingOptionNode;

            foreach ($shippingOptions as $shippingOption) {
                if (isset($shippingOption['servicePoints'])) {
                    //@todo Pickup point logic
                } else {
                    if (isset($shippingOption['distributor'])) {
                        $methods[$shippingOption['type']] = [
                            'distributor' =>  $shippingOption['distributor'],
                            'title' => $shippingOption['distributor'],
                            'price' => $shippingOption['price'],
                            'method' => $shippingOption['type'], // $shippingOption['distributor'] . '_' .
//                            'method_title' => $shippingOption['type']
                        ];
                    } else {
                        $this->_logger->debug('$shippingOption', [$shippingOption]);
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @todo Titles based on config
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'UPS_STANDARD' => __('Standard'),
                'AVG' => __('Daytime delivery'),
                'free' => __('Free')
            ],
            'free_methods' => [
                'free'
            ],
            'carriers' => [
                'kiala',
                'TNT' => 'PostNL',
                'dhl',
                'dhl_express',
                'bpost',
                'mondialrelay',
                'dpd',
                'UPS' => 'UPS'
            ]
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * @return Result
     */
    protected function _getQuotes()
    {
        $this->_result = $this->_rateFactory->create();
        $this->_paazlData = $this->_paazlManagement->getPaazlData();

        if (isset($this->_paazlData['requests'])) {
            if (count($this->_paazlData['requests'])) {
                // Gather previous results from session
                foreach ($this->_paazlData['results'] as $method => $results) {
                    foreach ($results as $dataKey => $result) {
                        $this->_setCachedQuotes($dataKey, $result);
                    }
                }
                foreach ($this->_paazlData['requests'] as $requestMethod => $request) {
                    $response = $this->_getCachedQuotes($request->getRequestKey());
                    if (is_null($response)) {
                        $request = $this->_requestManager->doRequest($request);
                        $response = $request->getResponse();

                        if (!count($request->getErrors()) && !is_null($response)) {
                            if (!is_null($request->getIdentifier())) $response['identifier'] = $request->getIdentifier();
                            $this->_paazlData['results'][$requestMethod][$request->getRequestKey()] = $response;
                            $this->_setCachedQuotes($request->getRequestKey(), $response);
                        }
                    }
                }
            }
            unset ($this->_paazlData['requests']);
        }

        if (isset($this->_paazlData['results']['orderRequest']['success'])) {
            // Set order reference
            $this->_paazlData['orderReference'] = $this->_paazlManagement->_getQuoteId();
        }
        $this->_paazlManagement->setPaazlData($this->_paazlData);

        $freeShippingThreshold = (float)$this->getConfigData('free_shipping_subtotal');
        $allowedMethods = $this->getAllowedMethods();

        foreach ($allowedMethods as $method => $methodData) {
            $methodPrice = (in_array($method, $this->getCode('free_methods')))
                ? 0
                : $methodData['price'];

            if ($freeShippingThreshold > 0) {
                if ($this->_request->getPackageValueWithDiscount() > $freeShippingThreshold) {
                    if (in_array($method, $this->getCode('free_shipping_allowed_methods'))) {
                        $methodPrice = 0;
                    }
                }
            }

            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier(static::CODE);
            //$rate->setCarrierTitle($methodData['title']);
            $rate->setCarrierTitle(static::CODE);
            $rate->setMethod($method);
            $rate->setMethodTitle($methodData['method']);
            $rate->setCost($methodPrice);
            $rate->setPrice($methodPrice);

            $this->_result->append($rate);
        }

        return $this->_result;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        //
    }
}