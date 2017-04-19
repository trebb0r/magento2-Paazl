<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Helper\Request;

use Magento\Framework\App\Helper\Context;

class Order extends Generic
{
    const XML_PATH_ORDER_REFERENCE_ADD_PREFIX = 'paazl/order/add_prefix';
    const XML_PATH_ORDER_REFERENCE_PREFIX = 'paazl/order/reference_prefix';

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Order constructor.
     * @param Context $context
     * @param \Paazl\Shipping\Model\PaazlManagement $_paazlManagement
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->_paazlManagement = $_paazlManagement;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }


    /**
     * @param $request
     * @return array
     */
    public function prepareProducts($request)
    {
        //@todo Mapping
        $attributes = $this->_paazlManagement->getMapping();

        $storeData = [
            'unitPriceCurrency' => $this->scopeConfig->getValue('currency/options/base', \Magento\Store\Model\ScopeInterface ::SCOPE_STORE)
        ];

        $namespace = 'http://www.paazl.com/schemas/matrix';
        $products = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $productData = [];
                $productData['quantity'] = $item->getQty();
                $productData['unitPriceCurrency'] = $item->getQuoteCurrencyCode();

                $product = $this->productFactory->create()->load($item->getProductId());

                foreach ($attributes as $nodeName => $attributeCode) {
                    $productData[$nodeName] = $item->getProduct()->getData($attributeCode);
                    $productData[$nodeName] = $product->getData($attributeCode);
                }

                $productData = array_merge($productData, $storeData);

                array_walk($productData, array('\Paazl\Shipping\Helper\Request\order', 'soapvar'));

                $soapVar = new \SoapVar($productData,SOAP_ENC_OBJECT,NULL,NULL,'product',$namespace);

                // Remove empty values or the order and update call don't work in Paazl?
                //$productData = array_filter($productData, function($value) { return !is_null($value) && $value !== ''; });

                $products[] = $soapVar;
            }
        }

        array_walk($products, array('\Paazl\Shipping\Helper\Request\order', 'soapvarObj'));

        return new \SoapVar($products,SOAP_ENC_OBJECT,null,null,'products',$namespace);
    }

    public function soapvar(&$item, $key) {
        $namespace = 'http://www.paazl.com/schemas/matrix';
        $item = new \SoapVar($item, XSD_STRING,NULL,NULL,$key,$namespace);
    }

    public function soapvarObj(&$item, $key) {
        $namespace = 'http://www.paazl.com/schemas/matrix';
        $item = new \SoapVar($item, SOAP_ENC_OBJECT,NULL,NULL,'product',$namespace);
    }
}
