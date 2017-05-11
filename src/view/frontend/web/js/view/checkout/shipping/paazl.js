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
        'Magento_Checkout/js/checkout-data',
        'Magento_Ui/js/lib/view/utils/dom-observer',
        'Paazl_Shipping/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Customer/js/model/address-list',
        'Magento_Customer/js/model/customer'
    ],
    function (
        Component,
        _,
        shippingService,
        quote,
        $,
        checkoutData,
        domObserver,
        shippingRateProcessorNewAddress,
        rateRegistry,
        addressList,
        customer
    ) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                var paazlPerfectLoaded = false;
                self.deliveryType = false;

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
                                            var addressInfo = self.getAddressInfo();

                                            var requestIdentifier = addressInfo['postcode'] + '_' + addressInfo['house_number']
                                                + '_' + addressInfo['house_number_addition'] + '_' + addressInfo['country_id'];

                                            var address = {};
                                            _.each(paazlData['addressRequest'], function (request) {
                                                if (request.identifier == requestIdentifier) {
                                                    address = request.address;
                                                }
                                            });

                                            self.processResult(address);
                                        };
                                        if (paazlData.hasOwnProperty('checkoutRequest')) {
                                            var key = Object.keys(paazlData['checkoutRequest'])[0];
                                            var url = paazlData['checkoutRequest'][key]['url'];

                                            if (self.deliveryType == 'home') {
                                                // select delivery radio option
                                                domObserver.get('input[delivery-type="home"]',function () {
                                                    $('input[delivery-type="servicePoint"]').attr('checked', false);
                                                    $('input[delivery-type="home"]').attr('checked', 'checked');
                                                    $('input[delivery-type="home"]').click();
                                                });
                                            }
                                            if (self.deliveryType == 'servicePoint') {
                                                // select pickup radio option
                                                domObserver.get('input[delivery-type="servicePoint"]',function () {
                                                    $('input[delivery-type="home"]').attr('checked', false);
                                                    $('input[delivery-type="servicePoint"]').attr('checked', 'checked');
                                                    $('input[delivery-type="servicePoint"]').click();
                                                });
                                            }


                                            if (self.paazlPerfectLoaded != true) {
                                                // For logged in users create a dummy email input form
                                                if ($('#customer-email').length == 0) {
                                                    $('body').append(
                                                        '<input id="customer-email"/>'
                                                    );
                                                }

                                                // load the url and show
                                                $('input[name="postcode"]').attr('data-pcm-input', 'consigneePostalCode');
                                                $('select[name="country_id"]').attr('data-pcm-input', 'consigneeCountryCode');
                                                $('#customer-email').attr('data-pcm-input', 'notificationEmailAddress');
                                                $('input[name="telephone"]').attr('data-pcm-input', 'notificationPhoneNumber');
                                                $('#checkout-locale').val(window.checkoutConfig.locale);
                                                $('#checkout-locale').attr('data-pcm-input', 'locale');

                                                $.getScript(url, function() {
                                                    // @todo: add callback function for save
                                                    domObserver.get('.paazlperfect-link',function () {
                                                        $('.paazlperfect-link').click(function (e) {
                                                            e.preventDefault();

                                                            var addressInfo = self.getAddressInfo();
                                                            $('input[name="postcode"]').val(addressInfo['postcode']);
                                                            $('select[name="country_id"]').val(addressInfo['country_id']);
                                                            $('#customer-email').val(addressInfo['email']);
                                                            $('input[name="telephone"]').val(addressInfo['telephone']);

                                                            var methodCode = $(this).attr('method_code');
                                                            if (methodCode == 'SERVICE_POINT') {
                                                                $('#checkout-paazl-type').val('servicePoint');
                                                            } else {
                                                                $('#checkout-paazl-type').val('home');
                                                            }

                                                            $('#checkout-paazl-type').attr('data-pcm-input', 'deliveryType');

                                                            PaazlCheckoutModuleLoader.show($.proxy(self.handlePaazlPerfect, self));
                                                        });
                                                    }.bind(this));
                                                });
                                                self.paazlPerfectLoaded = true;
                                            }
                                        };
                                    } catch (err) {
                                    }
                                }
                            }
                        }
                    });
                    return this;
                });
            },

            getAddressInfo: function () {
                var addressInfo = new Array();

                var houseNumber = '';
                var houseNumberAddition = '';
                var currentCountryId = '';
                var currentTelephone = '';
                var currentEmail = '';
                var currentPostcode = quote.shippingAddress().postcode;
                var addressFromData = checkoutData.getShippingAddressFromData();
                var selectedAddress = checkoutData.getSelectedShippingAddress();
                if (selectedAddress) {
                    // Logged in user with selected address
                    var selectedAddress = checkoutData.getSelectedShippingAddress();
                    addressList.some(function (address) {
                        if (selectedAddress == address.getKey()) {
                            addressFromData = address;
                        }
                    });

                    if (addressFromData.hasOwnProperty('houseNumber')) {
                        houseNumber = addressFromData.houseNumber;
                    }
                    else if (addressFromData.street.length >= 2) {
                        houseNumber = addressFromData.street[1];
                    }
                    if (addressFromData.hasOwnProperty('houseNumberAddition')) {
                        houseNumberAddition = addressFromData.houseNumberAddition;
                    }
                    else if (addressFromData.street.length >= 3) {
                        houseNumberAddition = addressFromData.street[2];
                    }
                    var requestIdentifier = currentPostcode + '_' + houseNumber
                        + '_' + houseNumberAddition + '_' + addressFromData.countryId;
                    currentCountryId = addressFromData.countryId;
                    currentTelephone = addressFromData.telephone;
                    currentEmail = addressFromData.email ? addressFromData.email : customer.customerData.email;
                    currentPostcode = addressFromData.postcode;
                }
                else {
                    // Logged out user or new-address
                    if (addressFromData.hasOwnProperty('house_number')) {
                        houseNumber = addressFromData.house_number;
                    }
                    else if (addressFromData.street.length >= 2) {
                        houseNumber = addressFromData.street[1];
                    }
                    if (addressFromData.hasOwnProperty('house_number_addition')) {
                        houseNumberAddition = addressFromData.house_number_addition;
                    }
                    else if (addressFromData.street.length >= 3) {
                        houseNumberAddition = addressFromData.street[2];
                    }
                    var requestIdentifier = currentPostcode + '_' + houseNumber
                        + '_' + houseNumberAddition + '_' + addressFromData.country_id;
                    currentCountryId = addressFromData.country_id;
                    currentTelephone = addressFromData.telephone;
                    currentEmail = addressFromData.email ? addressFromData.email : customer.customerData.email;
                    currentEmail = currentEmail ? currentEmail : $('#customer-email').val();
                    currentPostcode = addressFromData.postcode;
                }

                addressInfo['country_id'] = currentCountryId;
                addressInfo['telephone'] = currentTelephone;
                addressInfo['email'] = currentEmail;
                addressInfo['postcode'] = currentPostcode;
                addressInfo['house_number'] = houseNumber;
                addressInfo['house_number_addition'] = houseNumberAddition;

                return addressInfo;
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
            },

            handlePaazlPerfect: function (data) {
                var self = this;
                self.deliveryType = data.deliveryType;

                console.log(data);

                // set email and phone when entered in paazl perfect.
                $('input[data-pcm-input="notificationEmailAddress"]').val(data.notificationEmailAddress).keyup();
                $('input[data-pcm-input="notificationPhoneNumber"]').val(data.notificationPhoneNumber).keyup();

                // Clear the rateRegistry cache so new rates will be retrieved
                rateRegistry.set(quote.shippingAddress().getCacheKey(), null);
                shippingRateProcessorNewAddress.getRates(quote.shippingAddress());
            }
        });
    }
);