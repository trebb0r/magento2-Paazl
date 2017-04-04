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

    /** @var string */
    protected $_shippingOptionKey;

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
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_addressHelper = $addressHelper;
        $this->_orderHelper = $orderHelper;
        $this->_paazlManagement = $_paazlManagement;
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

        $this->setRequest($request);
        $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        $this->_checkoutSession->setPaazlData($this->_paazlData);

        return $this->getResult();
    }

    /**
     * Prepare (API) requests
     * @param RateRequest $request
     * @return $this
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;
        $this->_paazlData['requests'] = [];

        $addressData = $this->_getAddressData($request);

        // Prepare order(update) request data
        $products = $this->_orderHelper->prepareProducts($request);
        $orderRequestData = [
            'context' => $this->_getQuoteId(),
            'body' => [
                'orderReference' => $this->_getQuoteId(),
                'products' => $products
            ]
        ];

        if ($this->_paazlData['orderReference'] != $this->_getQuoteId()) {
            // Create (temporary) order as a reference
            $orderRequest = $this->_requestBuilder->build('PaazlOrderRequest', $orderRequestData);
            $this->_paazlData['requests']['orderRequest'] = $orderRequest;
        } else {
            // Update (temporary) order
            $updateOrderRequest = $this->_requestBuilder->build('PaazlUpdateOrderRequest', $orderRequestData);
            $this->_paazlData['requests']['updateOrderRequest'] = $updateOrderRequest;
        }
        // "address" request
        if (!is_null($addressData['postcode']) && !is_null($addressData['house_number']) && $addressData['country_id'] == 'NL') {
            $dutchPostcode = $this->_addressHelper->isDutchPostcode($addressData['postcode'], true);
            if ($dutchPostcode !== false) {
                // Dutch address-request
                $data = [
                    'context' => $this->_getQuoteId(),
                    'body' => [
                        'orderReference' => $this->_getQuoteId(),
                        'zipcode' => $dutchPostcode,
                        'housenumber' => $addressData['house_number'],
                        'addition' => $addressData['house_number_addition']
                    ],
                ];
                $identifier = $addressData['postcode']
                    . '_' . $addressData['house_number'] . '_'
                    . $addressData['house_number_addition'] . '_'
                    . $addressData['country_id'];
                $addressRequest = $this->_requestBuilder->build('PaazlAddressRequest', $data);
                $addressRequest->setIdentifier($identifier);
                $this->_paazlData['requests']['addressRequest'] = $addressRequest;
            }
        }
        // "shippingOption" request
        if (!is_null($addressData['country_id'])) {
            $data = [
                'context' => $this->_getQuoteId(),
                'body' => [
                    'orderReference' => $this->_getQuoteId(),
                    'postcode' => $addressData['postcode'],
                    'country' => (string)$request->getDestCountryId(),
                    'extendedDeliveryDateDetails' => false,
                    'shippingOption' => null,
                    'deliveryDateRange' => null
                ]
            ];
            $shippingOptionRequest = $this->_requestBuilder->build('PaazlShippingOptionRequest', $data);
            $this->_shippingOptionKey = $shippingOptionRequest->getRequestKey();
            $this->_paazlData['requests']['shippingOption'] = $shippingOptionRequest;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = [];
        $requestKey = (string)$this->_shippingOptionKey;

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
            $this->_paazlData['orderReference'] = $this->_getQuoteId();
        }

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
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($methodData['title']);
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

    /**
     * @param $request
     * @return array
     */
    protected function _getAddressData($request)
    {
        foreach ($request->getAllItems() as $item) {
            $address = $item->getAddress();
            $extensionAttributes = $address->getExtensionAttributes();
            if (!is_null($extensionAttributes)) {
                $houseNumber = $extensionAttributes->getHouseNumber();
                $addition = $extensionAttributes->getHouseNumberAddition();
            }
            break;
        }

        $addressData = [
            'street_parts' => $this->_addressHelper->getMultiLineStreetParts($request->getDestStreet()),
            'postcode' => $request->getDestPostcode(),
            'country_id' => $request->getDestCountryId(),
            'house_number' => (isset($houseNumber)) ? $houseNumber : null,
            'house_number_addition' => (isset($addition)) ? $addition : null
        ];

        return $addressData;
    }

    /**
     * @return int
     */
    private function _getQuoteId()
    {
        if (is_null($this->_quoteId) && !is_null($this->_request)) {
            if ($this->_request->getAllItems()) {
                foreach ($this->_request->getAllItems() as $item) {
                    $this->_quoteId = $this->_paazlManagement->getReferencePrefix() . (string)$item->getQuoteId();
                    break;
                }
            }
        }
        
        return $this->_quoteId;
    }
}
