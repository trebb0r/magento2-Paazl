<?php

/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Model;

class PaazlManagement implements \Paazl\Shipping\Api\PaazlManagementInterface
{

    const XML_PATH_ORDER_REFERENCE_ADD_PREFIX = 'paazl/order/add_prefix';
    const XML_PATH_ORDER_REFERENCE_PREFIX = 'paazl/order/reference_prefix';
    const XML_PATH_WEIGHT_CONVERSION_RATIO = 'paazl/locale/weight_conversion';
    const XML_PATH_ASSURED_AMOUNT = 'paazl/order/assured_amount';
    const XML_PATH_SINGLE_LABEL_PER_ORDER = 'paazl/order/single_label_per_order';

    /** @var float */
    protected $weightConversion;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Paazl\Shipping\Model\Api\RequestBuilder
     */
    protected $_requestBuilder;

    /**
     * @var \Paazl\Shipping\Model\Api\RequestManager
     */
    protected $_requestManager;

    /** @var RateRequest */
    protected $_request;

    /** @var array */
    protected $_paazlData = [];

    /** @var int */
    protected $_quoteId;

    /** @var string */
    protected $_shippingOptionKey;

    /** @var \Paazl\Shipping\Helper\Utility\Address */
    protected $_addressHelper;

    /** @var \Paazl\Shipping\Helper\Request\Order */
    protected $_orderHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory
     */
    protected $quoteAddressRateCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * PaazlManagement constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder
     * @param \Paazl\Shipping\Model\Api\RequestManager $requestManager
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     * @param \Paazl\Shipping\Helper\Request\Order $orderHelper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $quoteAddressRateCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        \Paazl\Shipping\Helper\Utility\Address $addressHelper,
        \Paazl\Shipping\Helper\Request\Order\Proxy $orderHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $quoteAddressRateCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_addressHelper = $addressHelper;
        $this->_orderHelper = $orderHelper;
        $this->quoteFactory = $quoteFactory;
        $this->quoteAddressRateCollectionFactory = $quoteAddressRateCollectionFactory;
        $this->timezoneInterface = $timezoneInterface;
    }


    /**
     * @return array
     */
    public function getMapping()
    {
        //@todo Mapping
        $attributes = [
            'packagesPerUnit' => 'packagesPerUnit',
            'matrix' => 'matrix',
            'weight' => 'weight',
            'width' => 'width',
            'height' => 'height',
            'length' => 'length',
            'volume' => 'volume',
            'code' => 'sku',
            'description' => 'customs_message',
            'countryOfManufacture' => 'country_of_manufacture',
            'unitPrice' => 'price_incl_tax',
            'hsTariffCode' => 'hsTariffCode',
            'processingDays' => 'processingDays'
        ];

        return $attributes;
    }

    /**
     * @return string
     */
    public function getReferencePrefix()
    {
        $prefix = '';
        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_ORDER_REFERENCE_ADD_PREFIX)) {
            $prefix = trim((string)$this->_scopeConfig->getValue(self::XML_PATH_ORDER_REFERENCE_PREFIX));
        }
        return $prefix;
    }

    /**
     * @param $weight
     * @return float
     */
    public function getConvertedWeight($weight)
    {
        if (is_null($this->weightConversion)) {
            $weightConversion = $this->_scopeConfig->getValue(self::XML_PATH_WEIGHT_CONVERSION_RATIO);
            $this->weightConversion = (!is_null($weightConversion)) ? (float)$weightConversion : (float)1;
        }

        return (float)$weight * $this->weightConversion;
    }

    /**
     * @param $order
     * @return mixed
     */
    public function processOrderCommitRequest($order)
    {
        $shippingMethod = $order->getShippingMethod(true);
        $shippingAddress = $order->getShippingAddress();
        /**
         * @var $quote \Magento\Quote\Model\Quote
         */
        $quote = $this->quoteFactory->create()->setStoreId($order->getStoreId())->load($order->getQuoteId());

        $quoteAddress = $quote->getShippingAddress();

        $rateCollection = $this->quoteAddressRateCollectionFactory->create();
        $rateCollection->addFieldToFilter('address_id',  array('eq' => $quoteAddress->getId()));
        $rateCollection->addFieldToFilter('code',  array('eq' => $quoteAddress->getShippingMethod()));

        $rate = $rateCollection->fetchItem();

        $extOrderId = $this->getReferencePrefix() . $order->getIncrementId();

        $assuredAmount = 0;
        if (strpos($shippingMethod->getMethod(), 'HIGH_LIABILITY') !== false) {
            $assuredAmount = (int)$this->_scopeConfig->getValue(self::XML_PATH_ASSURED_AMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId());
        }

        $requestData = [
            'context' => $this->getReferencePrefix() . $order->getQuoteId(),
            'body' => [
                'orderReference' => $extOrderId, // Final reference
                'pendingOrderReference' => $this->getReferencePrefix() . $order->getQuoteId(), // Temporary reference
                'totalAmount' => $order->getBaseSubtotalInclTax() * 100, // In cents
                'customerEmail' => $order->getCustomerEmail(),
                'customerPhoneNumber' => $shippingAddress->getTelephone(),
                'shippingMethod' => [
                    'type' => 'delivery',
                    'identifier' => null,
                    'option' => $shippingMethod->getMethod(),
                    'orderWeight' => $this->getConvertedWeight($order->getWeight()),
                    'description' => 'Delivery', //@todo Find out what description is expected
                    'assuredAmount' => $assuredAmount,
                    'assuredAmountCurrency' => 'EUR'
                ],
                'shippingAddress' => [
                    'customerName' => $shippingAddress->getName(),
                    'street' => $shippingAddress->getStreetLine(1),
                    'housenumber' => $shippingAddress->getStreetLine(2),
                    'addition' => $shippingAddress->getStreetLine(3),
                    'zipcode' => $shippingAddress->getPostcode(),
                    'city' => $shippingAddress->getCity(),
                    'province' => (strlen((string)$shippingAddress->getRegionCode()) < 3)
                        ? $shippingAddress->getRegionCode()
                        : null,
                    'country' => $shippingAddress->getCountryId()
                ]
            ]
        ];

        if ($this->_scopeConfig->getValue(self::XML_PATH_SINGLE_LABEL_PER_ORDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId())) {
            $requestData['body']['shippingMethod']['maxLabels'] = 1;
        }

        // Service points
        if ($rate && $rate['identifier'] != '') {
            $requestData['body']['shippingMethod']['identifier'] = $rate['identifier'];
            $requestData['body']['shippingMethod']['type'] = 'servicepoint';
            $requestData['body']['shippingMethod']['option'] = $rate['paazl_option'];
            $notification = $rate['paazl_notification'];
            if (strpos($notification, '@') !== false) {
                $requestData['body']['shippingMethod']['servicepointNotificationEmail'] = $notification;
            }
            else {
                $requestData['body']['shippingMethod']['servicepointNotificationMobile'] = $notification;
            }
        }
        // Preferred delivery date
        if ($rate && $rate['paazl_preferred_date'] != '') {
            $requestData['body']['shippingMethod']['preferredDeliveryDate'] = $rate['paazl_preferred_date'];
        }

        $orderCommitRequest = $this->_requestBuilder->build('PaazlOrderCommitRequest', $requestData);
        $response = $this->_requestManager->doRequest($orderCommitRequest)->getResponse();

        return $response;
    }

    /**
     * @param $dateTime
     * @return mixed
     */
    public function processListOrdersRequest($dateTime)
    {
        $requestData = [
            'context' => $dateTime->format('Ymd'),
            'body' => [
                'changedSince' => $dateTime->format('Y-m-d'),
            ]
        ];
        $listOrdersRequest = $this->_requestBuilder->build('PaazlListOrdersRequest', $requestData);
        $response = $this->_requestManager->doRequest($listOrdersRequest)->getResponse();

        return $response;
    }

    /**
     * Prepare (API) requests
     * @param RateRequest $request
     * @return $this
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
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
                    'extendedDeliveryDateDetails' => true,
                    'shippingOption' => null,
                    'deliveryDateRange' => null
                ]
            ];
            $shippingOptionRequest = $this->_requestBuilder->build('PaazlShippingOptionRequest', $data);
            $this->_shippingOptionKey = $shippingOptionRequest->getRequestKey();
            $this->_paazlData['requests']['shippingOption'] = $shippingOptionRequest;
        }

        // Get PaazlPerfect Url
        $data = [
            'context' => $this->_getQuoteId(),
            'body' => [
                'orderReference' => $this->_getQuoteId(),
            ],
        ];

        $checkoutRequest = $this->_requestBuilder->build('PaazlCheckoutRequest', $data);
        $this->_paazlData['requests']['checkoutRequest'] = $checkoutRequest;

        return $this->_paazlData;
    }

    /**
     * @param $request
     * @return array
     */
    protected function _getAddressData($request)
    {
        foreach ($request->getAllItems() as $item) {
            $houseNumber = '';
            $address = $item->getAddress();
            $extensionAttributes = $address->getExtensionAttributes();
            if (!is_null($extensionAttributes)) {
                $houseNumber = $extensionAttributes->getHouseNumber();
                $addition = $extensionAttributes->getHouseNumberAddition();
            }
            // Try to get information from address?
            if ($houseNumber == '') {
                $houseNumber = $address->getStreetLine(2);
                $addition = $address->getStreetLine(3);
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
    public function _getQuoteId()
    {
        if (is_null($this->_quoteId) && !is_null($this->_request)) {
            if ($this->_request->getAllItems()) {
                foreach ($this->_request->getAllItems() as $item) {
                    $this->_quoteId = $this->getReferencePrefix() . (string)$item->getQuoteId();
                    break;
                }
            }
        }

        return $this->_quoteId;
    }

    /**
     * @return array
     */
    public function getPaazlData()
    {
        return $this->_paazlData;
    }

    /**
     * @param $paazlData
     */
    public function setPaazlData($paazlData)
    {
        $this->_paazlData = $paazlData;
    }

    /**
     * @return string
     */
    public function getShoppingOptionKey()
    {
        return $this->_shippingOptionKey;
    }

    /**
     * @return RateRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return array
     */
    public function getShippingOptions()
    {
        $requestKey = (string)$this->getShoppingOptionKey();
        $this->_paazlData = $this->getPaazlData();

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

            return $shippingOptions;
        }

        return [];
    }
}