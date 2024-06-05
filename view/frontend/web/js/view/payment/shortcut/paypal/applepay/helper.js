define(
    [
        'jquery',
        'mage/storage',
        'mage/translate',
    ],
    function (
        $,
        storage,
        $t
    ) {
        'use strict';

        return {
            /**
             * Helper lets.
             */
            availableRegions: null,

            /**
             * Format Price Amount.
             *
             * @param value
             * @returns {string}
             */
            formatPrice(value) {
                return parseFloat(value).toFixed(2);
            },

            /**
             * A wrapper around the fetch API that returns the JSON response directly.
             *
             * @param {string} url The URL to fetch.
             * @param {Object} options The options for the fetch request.
             * @returns {Promise<Object>} The JSON response.
             */
            fetchJson: async function fetchJson(url, options = {}) {
                const response = await fetch(url, options);

                return response.json();
            },

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
                let self = this,
                    totalsArray = [];

                totalsArray.push({
                    label: $t('Cart Subtotal'),
                    amount: self.formatPrice(quote.base_subtotal)
                });

                if (quote.base_discount_amount !== 0) {
                    totalsArray.push({
                        label: $t('Discount'),
                        amount: self.formatPrice(quote.base_discount_amount)
                    });
                }

                totalsArray.push({
                    label: $t('Shipping'),
                    amount: self.formatPrice(quote.base_shipping_amount)
                });

                if (quote.tax_amount !== 0) {
                    totalsArray.push({
                        label: $t('Tax'),
                        amount: self.formatPrice(quote.tax_amount)
                    });
                }

                return totalsArray;
            },

            /**
             * Get Available Regions Data from Magento.
             */
            getAvailableRegions: function () {
                let self = this;

                if (self.availableRegions == null) {
                    storage
                        .get(
                            'rest/V1/directory/countries'
                        )
                        .done(function (response) {
                            let countryCode, availableRegionsData;
                            self.availableRegions = {};

                            for (let countryData in response) {
                                if (response.hasOwnProperty(countryData)) {
                                    countryCode = response[countryData].two_letter_abbreviation;
                                    availableRegionsData = response[countryData].available_regions;

                                    if (typeof availableRegionsData !== 'undefined') {
                                        self.availableRegions[countryCode] = availableRegionsData;
                                    }
                                }
                            }
                        }.bind(self));
                }
            },

            /**
             * Get Formatted Address Data.
             *
             * @param address
             * @returns {{emailAddress: string, phoneNumber: *, firstName: *, lastName: *, addressLines: *, cityName: *, regionId: number, regionCode: null, regionName: null, countryCode: string, postalCode: *}}
             */
            getFormattedAddress(address) {
                let region = this.getRegionData(address.countryCode, address.administrativeArea);

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
             * Get Formatted Object for estimate-shipping-methods API call.
             *
             * @param address
             * @returns {{address: {country_id: string, region: *, city: *, postcode: *}}}
             */
            getEstimateShippingMethodsObject: function (address) {
                return {
                    address: {
                        country_id: address.countryCode,
                        region: address.regionCode,
                        city: address.cityName,
                        postcode: address.postalCode
                    }
                };
            },

            /**
             * Get Formatted Object for totals-information API call.
             *
             * @param address
             * @returns {{addressInformation: {address: {countryId: string, region: *, regionId: *, postcode: *}}}}
             */
            getTotalsInfoObject: function (address) {
                return {
                    addressInformation: {
                        address: {
                            countryId: address.countryCode,
                            region: address.regionCode,
                            regionId: address.regionId,
                            postcode: address.postalCode
                        }
                    }
                };
            },


            /**
             * Get Formatted Object for billing-address API call.
             *
             * @param shippingAddress
             * @param billingAddress
             * @returns {{address: {email: (string|string), telephone: (string|*), firstname: *, lastname: *, street: (string|*), city: (string|*), region: (null|*), region_id: *, region_code: *, country_id: string, postcode: *, customer_address_id: number, save_in_address_book: number}}}
             */
            getBillingInfoObject: function (shippingAddress, billingAddress) {
                return {
                    address: {
                        email: shippingAddress.emailAddress,
                        telephone: shippingAddress.phoneNumber,
                        firstname: billingAddress.firstName,
                        lastname: billingAddress.lastName,
                        street: billingAddress.addressLines,
                        city: billingAddress.cityName,
                        region: billingAddress.regionName,
                        region_id: billingAddress.regionId,
                        region_code: billingAddress.regionCode,
                        country_id: billingAddress.countryCode,
                        postcode: billingAddress.postalCode,
                        customer_address_id: 0,
                        save_in_address_book: 0
                    }
                };
            },

            /**
             * Get Formatted Object for shipping-information/billing-address API call.
             *
             * @param shippingAddress
             * @param billingAddress
             * @returns {{addressInformation: {shipping_address: {email: string, telephone: *, firstname: *, lastname: *, street: *, city: *, region: (null|*), region_id: *, region_code: *, country_id: string, postcode: *, same_as_billing: number, customer_address_id: number, save_in_address_book: number}, billing_address: {email: string, telephone: *, firstname: *, lastname: *, street: *, city: *, region: (null|*), region_id: *, region_code: *, country_id: string, postcode: *, same_as_billing: number, customer_address_id: number, save_in_address_book: number}}}}
             */
            getShippingInfoObject: function (shippingAddress, billingAddress) {
                return {
                    addressInformation: {
                        shipping_address: {
                            email: shippingAddress.emailAddress,
                            telephone: shippingAddress.phoneNumber,
                            firstname: shippingAddress.firstName,
                            lastname: shippingAddress.lastName,
                            street: shippingAddress.addressLines,
                            city: shippingAddress.cityName,
                            region: shippingAddress.regionName,
                            region_id: shippingAddress.regionId,
                            region_code: shippingAddress.regionCode,
                            country_id: shippingAddress.countryCode,
                            postcode: shippingAddress.postalCode,
                            same_as_billing: 0,
                            customer_address_id: 0,
                            save_in_address_book: 0
                        },
                        billing_address: {
                            email: shippingAddress.emailAddress,
                            telephone: shippingAddress.phoneNumber,
                            firstname: billingAddress.firstName,
                            lastname: billingAddress.lastName,
                            street: billingAddress.addressLines,
                            city: billingAddress.cityName,
                            region: billingAddress.regionName,
                            region_id: billingAddress.regionId,
                            region_code: billingAddress.regionCode,
                            country_id: billingAddress.countryCode,
                            postcode: billingAddress.postalCode,
                            same_as_billing: 0,
                            customer_address_id: 0,
                            save_in_address_book: 0
                        }
                    }
                };
            },

            /**
             * Get Region Data.
             *
             * @param countryCode
             * @param regionIdentifier
             * @returns {*}
             */
            getRegionData: function (countryCode, regionIdentifier) {
                let self = this;

                if (countryCode && regionIdentifier) {
                    let regionName, regionCode,
                        countryCodeFormatted = countryCode.toUpperCase(),
                        regionIdentifierFormatted = regionIdentifier.trim().toUpperCase(),
                        countryRegions = self.availableRegions[countryCodeFormatted];

                    if (typeof countryRegions !== 'undefined') {
                        for (let regionData in countryRegions) {
                            if (countryRegions.hasOwnProperty(regionData)) {
                                regionName = countryRegions[regionData].name;
                                regionCode = countryRegions[regionData].code;

                                if (regionCode.toUpperCase() === regionIdentifierFormatted || regionName.toUpperCase() === regionIdentifierFormatted) {
                                    return countryRegions[regionData];
                                }
                            }
                        }
                    }
                }

                return {id: 0, code: null, name: null};
            },

            /**
             * Get Formatted Shipping Data.
             *
             * @param shippingMethods
             * @returns {{shippingContactSelectionArray: Array, shippingMethodsDataObject}}
             */
            getFormattedShippingMethods: function (shippingMethods) {
                let self = this,
                    methodData = {},
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
                                amount: self.formatPrice(shippingMethods[shipMethod].amount)
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

            /**
             * Constructs a request object for creating an order.
             *
             * @param {Object} config - The configuration object.
             * @param {string} config.dominateApiKey - The API key for the Dominate service.
             * @param {string} config.quoteId - The ID of the quote for the order.
             * @param {string} config.customerToken - The token for the customer.
             *
             * @returns {Object} - The request object.
             * @throws {Error} - If any of the required parameters are missing.
             */
            getOrderCreateRequest: function (config) {
                return {
                    method: 'post',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        api_key: config.dominateApiKey,
                        quote_id: config.quoteId,
                        customer_token: config.customerToken,
                        payment_source: 'apple_pay'
                    })
                };
            },

            /**
             * Generates request object for handling approve request.
             *
             * @param {Object} config - Configuration object.
             * @param {Object} createOrderData - Data for creating order.
             * @param {Object} checkoutData - Data for checkout.
             * @returns {Object} - Request object for handling approve request.
             */
            getHandleApproveRequest: function (config, createOrderData, checkoutData) {
                let body = {
                    api_key: config.dominateApiKey,
                    quote_id: config.quoteId,
                    customer_token: config.customerToken,
                    orderID: createOrderData.order_id,
                    customerEmail: createOrderData.customer_email,
                    cart_items: createOrderData.cart_items,
                    order_details: createOrderData.order_details,
                    shipping_method: createOrderData.shipping_method,
                    payment_method: 'apple_pay'
                };

                if (checkoutData) $.extend(body, checkoutData);

                return {
                    method: 'post',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(body)
                };
            },

            /**
             * Redirects the user to the success page with the provided order data.
             *
             * @param {string} successActionUrl - The URL of the success page.
             * @param {object} orderData - The data of the order.
             * @param {number} orderData.order_id - The ID of the order.
             * @param {string} orderData.order_increment_id - The increment ID of the order.
             * @param {string} orderData.order_status - The status of the order.
             * @param {number} orderData.quote_id - The ID of the quote.
             * @param {string} orderData.payment_method - The payment method used for the order.
             */
            redirectToSuccessPage: function (successActionUrl, orderData) {
                const successParams = new URLSearchParams({
                    order_id: orderData.order_id,
                    order_increment_id: orderData.order_increment_id,
                    order_status: orderData.order_status,
                    quote_id: orderData.quote_id,
                    payment_method: orderData.payment_method
                }).toString();

                window.location.href = `${successActionUrl}?${successParams}`;
            },

            /**
             * Formats an error message.
             *
             * @param {Object} error - The error object or error message.
             * @returns {string} - The formatted error message.
             */
            formatErrorMessage: function (error) {
                if (typeof error === 'string') {
                    try {
                        error = JSON.parse(error);
                    } catch(e) {
                        return error;
                    }
                }

                if (error && error.details && error.details.length) {
                    const detail = error.details[0];

                    return `${this.formatIssue(detail.issue)}: ${detail.description}`;
                }

                return error.message || 'Something went wrong';
            },

            /**
             * Formats the issue name by converting it from snake_case to title case.
             *
             * @param {string} issue - The issue name in snake_case format.
             * @returns {string} - The formatted issue name in title case.
             */
            formatIssue: function (issue) {
                return issue.replace(/_/g, ' ')
                    .toLowerCase()
                    .replace(/\b[a-z]/g, char => char.toUpperCase());
            }
        }
    }
);
