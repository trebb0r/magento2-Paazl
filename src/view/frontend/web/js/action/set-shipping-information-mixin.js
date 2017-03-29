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
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            if (shippingAddress.customAttributes === undefined) {
                shippingAddress.customAttributes = {};
            }
            if (shippingAddress.extension_attributes === undefined) {
                shippingAddress.extension_attributes = {};
            }
            shippingAddress['extension_attributes']['house_number'] = shippingAddress.customAttributes['house_number'];
            shippingAddress['extension_attributes']['house_number_addition'] = shippingAddress.customAttributes['house_number_addition'];

            shippingAddress['city'] = shippingAddress.city;
            shippingAddress['street'][0] = shippingAddress['extension_attributes']['street_name'];
            shippingAddress['street'][1] = shippingAddress.customAttributes['house_number'];
            shippingAddress['street'][2] = shippingAddress.customAttributes['house_number_addition'];
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});
