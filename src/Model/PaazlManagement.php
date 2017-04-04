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
    protected $scopeConfig;

    /**
     * PaazlManagement constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
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
        if ($this->scopeConfig->isSetFlag(self::XML_PATH_ORDER_REFERENCE_ADD_PREFIX)) {
            $prefix = trim((string)$this->scopeConfig->getValue(self::XML_PATH_ORDER_REFERENCE_PREFIX));
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
            $weightConversion = $this->scopeConfig->getValue(self::XML_PATH_WEIGHT_CONVERSION_RATIO);
            $this->weightConversion = (!is_null($weightConversion)) ? (float)$weightConversion : (float)1;
        }

        return (float)$weight * $this->weightConversion;
    }
}