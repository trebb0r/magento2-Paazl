<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class LayoutProcessor
 * @package Guapa\Paazl\Block\Checkout
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
                    'sortOrder' => (int)$originalStreetElement['sortOrder'] - 4
                ],
                'street_name' =>
                    $this->getCustomField([
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'label' => __('Street'),
                        'customScope' => 'shippingAddress',
                        'dataScope' => 'shippingAddress.street_name',
                        'validation' => ['required-entry' => true, 'min_text_length' => 1, 'max_text_length' => 255],
                        'sortOrder' => $originalStreetElement['sortOrder'] - 1
                    ])
            ];
            $addressElements = array_replace_recursive($addressElements, $elementConfig);

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $addressElements;

            // Custom fields
            $houseNumberElement = $this->getCustomField([
                'label' => __('House number'),
                'component' => 'Guapa_Paazl/js/form/element/house-number',
                'dataScope' => 'shippingAddress.house_number',
                'validation' => ['required-entry' => true, 'min_text_length' => 1, 'max_text_length' => 255],
                'sortOrder' => (int)$originalStreetElement['sortOrder'] - 3,
                'additionalClasses' => ['house_number']
            ]);
            $additionElement = $this->getCustomField([
                'label' => __('House nr. Addition'),
                'dataScope' => 'shippingAddress.house_number_addition',
                'sortOrder' => (int)$originalStreetElement['sortOrder'] - 2,
                'additionalClasses' => ['house_number']
            ]);

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['house_number'] = $houseNumberElement;
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children']['house_number_addition'] = $additionElement;
        }

        return $jsLayout;
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function getCustomField($data = [])
    {
        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.paazl_custom_field',
            'label' => 'Paazl custom field',
            'provider' => 'checkoutProvider',
            'sortOrder' => 10,
            'validation' => [],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
        ];

        return array_replace_recursive($customField, $data);
    }
}
