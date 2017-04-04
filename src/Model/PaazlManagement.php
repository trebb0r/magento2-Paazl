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

    /**
     * PaazlManagement constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder
     * @param \Paazl\Shipping\Model\Api\RequestManager $requestManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
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
            'description' => 'name',
            'countryOfManufacture' => 'country_of_manufacture',
            'unitPrice' => 'price_including_tax',
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

        $extOrderId = $this->paazlManagement->getReferencePrefix() . $order->getIncrementId();

        $assuredAmount = 0;
        if (strpos($shippingMethod->getMethod(), 'HIGH_LIABILITY') !== false) {
            $assuredAmount = (int)$this->_scopeConfig->getValue(self::XML_PATH_ASSURED_AMOUNT, StoreScopeInterface::SCOPE_STORE, $order->getStoreId());
        }

        $requestData = [
            'context' => $this->paazlManagement->getReferencePrefix() . $order->getQuoteId(),
            'body' => [
                'orderReference' => $extOrderId, // Final reference
                'pendingOrderReference' => $this->paazlManagement->getReferencePrefix() . $order->getQuoteId(), // Temporary reference
                'totalAmount' => $order->getBaseSubtotalInclTax() * 100, // In cents
                'customerEmail' => $order->getCustomerEmail(),
                'customerPhoneNumber' => $shippingAddress->getTelephone(),
                'shippingMethod' => [
                    'type' => 'delivery', //@todo Service points
                    'identifier' => null, //@todo Service points
                    'option' => $shippingMethod->getMethod(),
                    'orderWeight' => $this->paazlManagement->getConvertedWeight($order->getWeight()),
                    'maxLabels' => 1, //@todo Support for shipments having multiple packages
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
        $orderCommitRequest = $this->_requestBuilder->build('PaazlOrderCommitRequest', $requestData);
        $response = $this->_requestManager->doRequest($orderCommitRequest)->getResponse();

        return $response;
    }
}