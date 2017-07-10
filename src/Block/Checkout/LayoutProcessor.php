<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class LayoutProcessor
 * @package Paazl\Shipping\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $addressElements = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

        if (isset($addressElements['postcode']) && isset($addressElements['street'])) {
            $originalStreetElement = $addressElements['street'];
            unset($addressElements['street']);

            // Config of existing fields to overwrite
            $elementConfig = [
                'postcode' => [
                    'sortOrder' => (int)$originalStreetElement['sortOrder'] - 4,
                ],
            ];
            $addressElements = array_replace_recursive($addressElements, $elementConfig);

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $addressElements;
        }

        return $jsLayout;
    }
}
