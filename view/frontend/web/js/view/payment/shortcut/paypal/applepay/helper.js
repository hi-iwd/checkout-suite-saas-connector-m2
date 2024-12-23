define(
    [
        'jquery',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/global_helper',
        'mage/storage',
        'mage/translate',
    ],
    function (
        $,
        globalHelper,
        storage,
        $t
    ) {
        'use strict';

        return {
            /**
             * Generates a payment request object based on provided payment data, configuration, and checkout type.
             *
             * @param {Object} paymentData - Data related to the payment such as country code, supported networks, and merchant capabilities.
             * @param {Object} config - Configuration data such as currency code, display name, and grand total amount.
             * @param {string} checkoutType - Type of checkout, e.g. express or default.
             *
             * @returns {Object} - Payment request object.
             */
            getPaymentRequest: function (paymentData, config, checkoutType) {
                let paymentRequest = {
                    countryCode: paymentData.countryCode,
                    merchantCapabilities: paymentData.merchantCapabilities,
                    supportedNetworks: paymentData.supportedNetworks,
                    currencyCode: config.currencyCode,
                    total: {
                        label: config.displayName,
                        amount: config.grandTotalAmount
                    },
                    requiredBillingContactFields: ['postalAddress', 'name']
                };

                // Add Shipping Address Selection for Non-Virtual Quote and Express Checkout
                if (checkoutType === 'express') {
                    paymentRequest.requiredShippingContactFields = ['email', 'phone'];

                    if (!config.isVirtual) {
                        paymentRequest['requiredShippingContactFields'].unshift('postalAddress', 'name');
                    }
                }

                return paymentRequest;
            },

            /**
             * Get Quote Totals Data Array.
             *
             * @param quote
             * @returns {Array}
             */
            getTotalsArray(quote) {
                let totalsArray = [];

                totalsArray.push({
                    label: $t('Cart Subtotal'),
                    amount: globalHelper.formatPrice(quote.base_subtotal)
                });

                if (quote.base_discount_amount !== 0) {
                    totalsArray.push({
                        label: $t('Discount'),
                        amount: globalHelper.formatPrice(quote.base_discount_amount)
                    });
                }

                totalsArray.push({
                    label: $t('Shipping'),
                    amount: globalHelper.formatPrice(quote.base_shipping_amount)
                });

                if (quote.tax_amount !== 0) {
                    totalsArray.push({
                        label: $t('Tax'),
                        amount: globalHelper.formatPrice(quote.tax_amount)
                    });
                }

                return totalsArray;
            },

            /**
             * Get Formatted Address Data.
             *
             * @param address
             * @returns {{emailAddress: string, phoneNumber: *, firstName: *, lastName: *, addressLines: *, cityName: *, regionId: number, regionCode: null, regionName: null, countryCode: string, postalCode: *}}
             */
            getFormattedAddress(address) {
                let region = globalHelper.getRegionData(address.countryCode, address.administrativeArea);

                return {
                    emailAddress: address.emailAddress ? address.emailAddress : '',
                    phoneNumber: address.phoneNumber ? address.phoneNumber : '',
                    firstName: address.givenName ? address.givenName : address.familyName,
                    lastName: address.familyName ? address.familyName : address.givenName,
                    addressLines: address.addressLines ? address.addressLines : '',
                    cityName: address.locality ? address.locality : '',
                    regionId: region.id,
                    regionCode: region.code,
                    regionName: region.name,
                    countryCode: address.countryCode ? address.countryCode.toUpperCase() : '',
                    postalCode: address.postalCode ? address.postalCode : ''
                };
            },

            /**
             * Get Formatted Shipping Data.
             *
             * @param shippingMethods
             * @returns {{shippingContactSelectionArray: Array, shippingMethodsDataObject}}
             */
            getFormattedShippingMethods: function (shippingMethods) {
                let methodData = {},
                    shippingMethodsDataObject = {},
                    shippingContactSelectionArray = [];

                if (typeof shippingMethods !== 'undefined') {
                    for (let shipMethod in shippingMethods) {
                        if (shippingMethods.hasOwnProperty(shipMethod) && shippingMethods[shipMethod].available) {
                            shippingMethodsDataObject[shippingMethods[shipMethod].method_code] = {
                                method_code: shippingMethods[shipMethod].method_code,
                                carrier_code: shippingMethods[shipMethod].carrier_code
                            };
                            methodData = {
                                identifier: shippingMethods[shipMethod].method_code,
                                label: shippingMethods[shipMethod].method_title,
                                detail: shippingMethods[shipMethod].carrier_title,
                                amount: globalHelper.formatPrice(shippingMethods[shipMethod].amount)
                            };
                            shippingContactSelectionArray.push(methodData);
                        }
                    }
                }

                return {
                    shippingContactSelectionArray: shippingContactSelectionArray,
                    shippingMethodsDataObject: shippingMethodsDataObject
                };
            },
        }
    }
);
