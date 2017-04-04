/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
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
    'map': {
        '*': {
            'Magento_Checkout/js/model/shipping-rate-processor/new-address':
                'Paazl_Shipping/js/model/shipping-rate-processor/new-address',
            'Magento_Checkout/template/billing-address/details.html':
                'Paazl_Shipping/template/billing-address/details.html'
        }
    }
};
