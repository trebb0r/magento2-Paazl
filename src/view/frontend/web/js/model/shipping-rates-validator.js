/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
define(
    [
        'jquery',
        'mageUtils',
        'Paazl_Shipping/js/model/shipping-rates-validation-rules',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'mage/translate'
    ],
    function ($, utils, validationRules, quote, checkoutData, $t) {
        'use strict';
        return {
            validationErrors: [],
            validate: function(address) {
                $.extend(true, {}, quote.shippingAddress(), address);
                var self = this;
                this.validationErrors = [];
                $.each(validationRules.getRules(), function(field, rule) {
                    if (rule.required && utils.isEmpty(address[field])) {
                        var message = $t('Field ') + field + $t(' is required.');
                        self.validationErrors.push(message);
                    }
                });
                return !Boolean(this.validationErrors.length);
            }
        };
    }
);
