/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
var config = {
    config: {
        mixins: {
            // 'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
            //     'Guapa_Paazl/js/model/shipping-rate-processor/new-address-mixin': true
            // }
            'Guapa_Paazl/js/model/shipping-rate-processor/new-address': {
                'Guapa_Paazl/js/model/shipping-rate-processor/new-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Guapa_Paazl/js/action/set-shipping-information-mixin': true
            }
        }
    },
    'map': {
        '*': {
            'Magento_Checkout/js/model/shipping-rate-processor/new-address':
                'Guapa_Paazl/js/model/shipping-rate-processor/new-address',
            'Magento_Checkout/template/billing-address/details.html':
                'Guapa_Paazl/template/billing-address/details.html'
        }
    }
};
