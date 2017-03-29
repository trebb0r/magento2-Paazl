/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Guapa_Paazl/js/model/shipping-rates-validator',
        'Guapa_Paazl/js/model/shipping-rates-validation-rules',
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('paazl', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('paazl', shippingRatesValidationRules);

        return Component;
    }
);
