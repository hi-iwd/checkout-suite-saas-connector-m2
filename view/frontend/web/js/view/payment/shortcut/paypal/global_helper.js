define(
    [
        'jquery',
        'mage/storage',
    ],
    function (
        $,
        storage
    ) {
        'use strict';

        return {
            /**
             * Represents the available regions.
             *
             * @type {null}
             */
            availableRegions: null,

            /**
             * Formats the given value to a string with 2 decimal places.
             *
             * @param {number} value - The value to be formatted.
             * @returns {string} The formatted value as a string with 2 decimal places.
             */
            formatPrice(value) {
                return parseFloat(value).toFixed(2);
            },

            /**
             * Fetches JSON data from a given URL with optional fetch options.
             * Returns a Promise that resolves to the parsed JSON data.
             *
             * @param {string} url - The URL to fetch JSON from.
             * @param {Object} [options] - Additional fetch options (optional).
             * @returns {Promise<any>} - A Promise that resolves to the parsed JSON data.
             */
            fetchJson: async function fetchJson(url, options = {}) {
                const response = await fetch(url, options);

                return response.json();
            },

            /**
             * Returns the API URL for the specified endpoint.
             *
             * @param {string} endpoint - The endpoint for which the API URL is required.
             * @param {object} config - The configuration object containing storeCode, isLoggedIn, and maskedQuoteId.
             * @returns {string} The API URL for the specified endpoint.
             */
            getApiUrl: function (endpoint, config) {
                const baseUrl = `rest/${config.storeCode}/V1/`;
                const cartPath = config.isLoggedIn ? 'carts/mine/' : `guest-carts/${config.maskedQuoteId}/`;

                return `${baseUrl}${cartPath}${endpoint}`;
            },

            /**
             * Retrieves the address information based on the API type and adapter.
             * @param {string} apiType - The type of API request.
             * @param {object} adapter - The adapter object containing shipping and billing addresses.
             * @returns {string} - The address information in JSON string format.
             */
            getApiAddressInfo: function (apiType, adapter) {
                let addressInfo = {};

                switch (apiType) {
                    case 'estimate-shipping-methods':
                        addressInfo = this.getEstimateShippingMethodsObject(adapter.shippingAddress);
                        break;
                    case 'totals-information':
                        addressInfo = this.getTotalsInfoObject(adapter.shippingAddress);
                        break;
                    case 'shipping-information':
                        addressInfo = this.getShippingInfoObject(adapter.shippingAddress, adapter.billingAddress);
                        break;
                    case 'billing-address':
                        addressInfo = this.getBillingInfoObject(adapter.shippingAddress, adapter.billingAddress);
                        break;
                }

                if (apiType !== 'estimate-shipping-methods' && adapter.chosenShippingMethodCode) {
                    let shippingMethod = adapter.shippingMethods[adapter.chosenShippingMethodCode];

                    if (shippingMethod) {
                        addressInfo['addressInformation']['shipping_method_code'] = shippingMethod.method_code;
                        addressInfo['addressInformation']['shipping_carrier_code'] = shippingMethod.carrier_code;
                    }
                }

                return JSON.stringify(addressInfo);
            },

            /**
             * Retrieves the list of available regions for each country.
             * The data is fetched from the server and stored in the availableRegions property of the current object.
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
             * Generates an object with the formatted address for estimating shipping methods.
             *
             * @param {object} address - The address object to format.
             * @param {string} address.countryCode - The country code of the address.
             * @param {string} address.regionCode - The region code of the address.
             * @param {string} address.cityName - The city name of the address.
             * @param {string} address.postalCode - The postal code of the address.
             * @returns {object} - The address object with formatted address properties.
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
             * Generates an object containing address information for a given address.
             *
             * @param {Object} address - The address object.
             * @param {string} address.countryCode - The country code of the address.
             * @param {string|null} address.regionCode - The region code of the address. Can be null.
             * @param {number|string} address.regionId - The ID of the region.
             * @param {string} address.postalCode - The postal code of the address.
             *
             * @returns {Object} - The object containing address information.
             * @returns {Object} addressInformation - The object containing address information.
             * @returns {Object} address - The address object.
             * @returns {string} address.countryId - The country ID of the address.
             * @returns {string|null} address.region - The region of the address. Can be null.
             * @returns {number|string} address.regionId - The ID of the region.
             * @returns {string} address.postcode - The postal code of the address.
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
             * Returns a structured object that contains the billing information based on the given shipping address and billing address.
             *
             * @param {object} shippingAddress - The shipping address object.
             * @param {object} billingAddress - The billing address object.
             * @returns {object} - The billing info object.
             */
            getBillingInfoObject: function (shippingAddress, billingAddress) {
                return {
                    address: {
                        email: billingAddress.emailAddress,
                        telephone: billingAddress.phoneNumber,
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
             * Returns an object containing shipping and billing address information.
             *
             * @param {object} shippingAddress - The shipping address object.
             * @param {object} billingAddress - The billing address object.
             *
             * @returns {object} - The address information object.
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
                            email: billingAddress.emailAddress,
                            telephone: billingAddress.phoneNumber,
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

                return {id: 0, code: null, name: regionIdentifier};
            },

            /**
             * Generates a request object for creating an order.
             *
             * @param {object} config - The configuration object.
             * @param {string} payment_source - The payment source.
             * @returns {object} The request object.
             */
            getOrderCreateRequest: function (config, payment_source) {
                return {
                    method: 'post',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        api_key: config.dominateApiKey,
                        quote_id: config.quoteId,
                        customer_token: config.customerToken,
                        payment_source: payment_source
                    })
                };
            },

            /**
             * Generates a request object for handling the approve action.
             *
             * @param {object} config - The configuration object.
             * @param {object} createOrderData - The data for creating an order.
             * @param {object} checkoutData - The additional checkout data.
             * @param {string} payment_method - The payment method.
             * @returns {object} - The request object.
             */
            getHandleApproveRequest: function (config, createOrderData, checkoutData, payment_method) {
                let body = {
                    api_key: config.dominateApiKey,
                    quote_id: config.quoteId,
                    customer_token: config.customerToken,
                    orderID: createOrderData.order_id,
                    customerEmail: createOrderData.customer_email,
                    cart_items: createOrderData.cart_items,
                    order_details: createOrderData.order_details,
                    shipping_method: createOrderData.shipping_method,
                    payment_method: payment_method
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
