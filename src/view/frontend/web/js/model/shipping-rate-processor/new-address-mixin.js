/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data'
], function ($, wrapper, checkoutData) {
    'use strict';

    return function (defaultProcessor) {
        defaultProcessor.getRates = wrapper.wrap(defaultProcessor.getRates, function (originalAction, address) {
            var streetName = $("input[name='street_name']").val();
            var houseNumber = $("input[name='house_number']").val();
            var houseNumberAddition = $("input[name='house_number_addition']").val();

            var localStorageData = checkoutData.getShippingAddressFromData();

            if (streetName === undefined) {
                if (localStorageData) {
                    if (localStorageData.street_name !== undefined) streetName = localStorageData.street_name;
                }
            }
            if (houseNumber === undefined) {
                if (localStorageData) {
                    if (localStorageData.house_number !== undefined) houseNumber = localStorageData.house_number;
                }
            }
            if (houseNumberAddition === undefined) {
                if (localStorageData) {
                    if (localStorageData.house_number_addition !== undefined) houseNumber
                        = localStorageData.house_number_addition;
                }
            }

            if (address.customAttributes === undefined) {
                address.customAttributes = {};
            }
            if (address.extension_attributes === undefined) {
                address.extension_attributes = {};
            }
            address.extension_attributes.street_name = streetName;
            address.customAttributes.house_number = houseNumber;
            address.customAttributes.house_number_addition = houseNumberAddition;

            return originalAction(address);
        });

        return defaultProcessor;
    };
});
