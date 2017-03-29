/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function ($, uiRegistry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }',
            imports: {
                update: '${ $.parentName }.country_id:value'
            },
            selectorsToEnable: ['[name=street_name]', '[name=city]']
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            if (!value) {
                return;
            }
            if (value != 'NL') {
                $.each(this.selectorsToEnable, function (key, selector) {
                    $(selector).prop('disabled', false);
                    $(selector).trigger('change');
                });
            }
        }
    });
});
