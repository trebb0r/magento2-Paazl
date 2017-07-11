/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            if (shippingAddress.customAttributes === undefined) {
                shippingAddress.customAttributes = {};
            }

            shippingAddress['city'] = shippingAddress.city;
            if (shippingAddress.customAttributes['house_number']) {
                shippingAddress['street'][0] = shippingAddress.customAttributes['street_name'].value;
                shippingAddress['street'][1] = shippingAddress.customAttributes['house_number'].value;
                if (shippingAddress.customAttributes['house_number_addition']) {
                    shippingAddress['street'][2] = shippingAddress.customAttributes['house_number_addition'].value;
                }
            }
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});
