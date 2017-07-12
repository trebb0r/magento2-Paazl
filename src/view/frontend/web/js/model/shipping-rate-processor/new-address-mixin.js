/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
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
            var streetName = $("input[name='custom_attributes[street_name]']").val();
            var houseNumber = $("input[name='custom_attributes[house_number]']").val();
            var houseNumberAddition = $("input[name='custom_attributes[house_number_addition]']").val();
            var postcode = $("input[name='postcode']").val();

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
            if (postcode === undefined) {
                if (localStorageData) {
                    if (localStorageData.postcode !== undefined) postcode = localStorageData.postcode;
                }
            }

            if (address.customAttributes === undefined) {
                address.customAttributes = {};
            }
            if (address.extension_attributes === undefined) {
                address.extension_attributes = {};
            }
            address.customAttributes.street_name = streetName;
            address.customAttributes.house_number = houseNumber;
            address.customAttributes.house_number_addition = houseNumberAddition;
            address.postcode = postcode;

            return originalAction(address);
        });

        return defaultProcessor;
    };
});
