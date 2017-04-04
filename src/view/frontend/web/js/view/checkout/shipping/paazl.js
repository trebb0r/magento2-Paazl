/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

define(
    [
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        Component,
        _,
        shippingService,
        quote,
        $,
        checkoutData
    ) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;

                shippingService.getShippingRates().subscribe(function (rates) {
                    var dataProcessed = false;
                    _.each(rates, function (rate) {
                        if (!dataProcessed) {
                            if (rate.hasOwnProperty('extension_attributes')) {
                                if (rate['extension_attributes'].hasOwnProperty('paazl_data')) {
                                    dataProcessed = true;
                                    try {
                                        var paazlData = JSON.parse(rate['extension_attributes']['paazl_data']);
                                        if (paazlData.hasOwnProperty('addressRequest')) {
                                            var currentPostcode = quote.shippingAddress().postcode;
                                            var addressFromData = checkoutData.getShippingAddressFromData();
                                            var houseNumber = '';
                                            var houseNumberAddition = '';
                                            if (addressFromData.hasOwnProperty('house_number')) {
                                                houseNumber = addressFromData.house_number;
                                            }
                                            if (addressFromData.hasOwnProperty('house_number_addition')) {
                                                houseNumberAddition = addressFromData.house_number_addition;
                                            }
                                            var requestIdentifier = currentPostcode + '_' + houseNumber
                                                + '_' + houseNumberAddition + '_' + addressFromData.country_id;

                                            var address = {};
                                            _.each(paazlData['addressRequest'], function (request) {
                                                if (request.identifier == requestIdentifier) {
                                                    address = request.address;
                                                }
                                            });

                                            self.processResult(address);
                                        }
                                    } catch (err) {
                                    }
                                }
                            }
                        }
                    });
                    return this;
                });
            },

            processResult: function (address) {
                var isValid = true;
                var dataKeys = ['city', 'street'];
                _.each(dataKeys, function (i) {
                    if (!_.has(address, i)) {
                        isValid = false;
                    }
                });

                var elements = {
                    streetName: $("input[name='street_name']"),
                    street: $("input[name='street[0]']"),
                    houseNumber: $("input[name='street[1]']"),
                    houseNrAddition: $("input[name='street[2]']"),
                    city: $("input[name='city']")
                };

                var addressFromData = checkoutData.getShippingAddressFromData();
                var shippingData = {};

                if (isValid) {
                    // Disable elements and add result data
                    elements.streetName.val(address.street);
                    elements.street.val(address.street);
                    elements.houseNumber.val(address.housenumber);
                    elements.houseNrAddition.val(address.addition);
                    elements.city.val(address.city);
                    this.disableElements(elements);

                    shippingData = {
                        city: address.city,
                        street_name: address.street,
                        street: {
                            0: address.street,
                            1: address.housenumber,
                            2: address.addition
                        }
                    };
                } else {
                    // Enable elements
                    this.enableElements(elements, []); //  ['street', 'city']
                    var streetName = '';
                    if (addressFromData.street_name !== undefined) {
                        streetName = addressFromData.street_name;
                    }
                    shippingData = {
                        city: addressFromData.city,
                        street_name: streetName,
                        street: {
                            0: streetName,
                            1: addressFromData.house_number,
                            2: addressFromData.house_number_addition
                        }
                    };
                }

                shippingData = $.extend(addressFromData, shippingData);
                checkoutData.setShippingAddressFromData(shippingData);
            },

            disableElements: function (elements) {
                _.each(elements, function (e) {
                    if (!e.is('[data-locked]')) {
                        e.prop('disabled', true);
                        e.trigger('change');
                    }
                });
            },

            enableElements: function (elements, fieldsToClear) {
                _.each(elements, function (e, key) {
                    e.prop('disabled', false);
                    e.trigger('change');
                    if (_.contains(fieldsToClear, key)) {
                        e.val('');
                    }
                });
            },

            setElementDisabled: function (element, state, triggerEvent) {
                if (!element.is('[data-locked]')) {
                    element.prop('disabled', state);

                    if (triggerEvent) {
                        element.trigger('change');
                    }
                }
            }
        });
    }
);