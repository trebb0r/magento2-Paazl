<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Helper\Request;

class Order extends Generic
{
    const XML_PATH_ORDER_REFERENCE_ADD_PREFIX = 'paazl/order/add_prefix';
    const XML_PATH_ORDER_REFERENCE_PREFIX = 'paazl/order/reference_prefix';

    /**
     * @param $request
     * @return array
     */
    public function prepareProducts($request)
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
        //@todo Values from store-config
        $storeData = [
            'unitPriceCurrency' => 'EUR'
        ];

        $products = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $productData = [];
                $productData['quantity'] = $item->getQty();
                $productData['unitPriceCurrency'] = $item->getQuoteCurrencyCode();

                foreach ($attributes as $nodeName => $attributeCode) {
                    $productData[$nodeName] = $item->getProduct()->getData($attributeCode);
                }

                $productData = array_merge($productData, $storeData);
                $products[] = $productData;
            }
        }

        /**
         * promotionAbsolute
         * promotion
         */

        return $products;
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
}
