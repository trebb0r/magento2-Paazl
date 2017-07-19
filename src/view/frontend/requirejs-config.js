/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
var config = {
    config: {
        mixins: {
            // 'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
            //     'Paazl_Shipping/js/model/shipping-rate-processor/new-address-mixin': true
            // }
            'Paazl_Shipping/js/model/shipping-rate-processor/new-address': {
                'Paazl_Shipping/js/model/shipping-rate-processor/new-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Paazl_Shipping/js/action/set-shipping-information-mixin': true
            }
        }
    },
    map: {
        '*': {
            fileElement: 'Paazl_Shipping/file-element',
            'Magento_Checkout/js/model/shipping-rate-processor/new-address':
                'Paazl_Shipping/js/model/shipping-rate-processor/new-address',
            'Magento_Checkout/template/billing-address/details.html':
                'Paazl_Shipping/template/billing-address/details.html'
        }
    }
};
