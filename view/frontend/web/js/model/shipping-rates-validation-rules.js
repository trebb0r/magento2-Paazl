/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
define(
    [],
    function () {
        'use strict';
        return {
            getRules: function() {
                return {
                    'postcode': {
                        'required': true
                    },
                    'country_id': {
                        'required': true
                    },
                    'house_number': {
                        'required': true
                    },
                    'house_number_addition': {
                    }
                };
            }
        };
    }
);
