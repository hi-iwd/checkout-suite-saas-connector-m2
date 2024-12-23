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
             * Generates the payment request object based on the given configuration.
             *
             * @param {Object} config - The configuration object.
             * @param checkoutType - express/default
             * @param {number} config.grandTotalAmount - The total amount of the order.
             * @param {string} config.currencyCode - The currency code.
             * @returns {Object} - The payment request object.
             * @property {Object[]} displayItems - The array of display items to be shown in the payment request.
             * @property {string} displayItems[].label - The label of the display item.
             * @property {string} displayItems[].type - The type of the display item.
             * @property {number} displayItems[].price - The price of the display item.
             * @property {string} currencyCode - The currency code.
             * @property {string} totalPriceStatus - The status of the total price.
             * @property {number} totalPrice - The total price.
             * @property {string} totalPriceLabel - The label of the total price.
             */
            getPaymentRequest: function (config, checkoutType) {
                let paymentRequest =  {
                    currencyCode: config.currencyCode,
                    totalPriceStatus: "FINAL",
                    totalPrice: config.grandTotalAmount,
                    totalPriceLabel: $t('Order Total'),
                };

                if (!config.isVirtual && checkoutType === 'express') {
                    paymentRequest.displayItems = [
                        {
                            label: $t('Subtotal'),
                            type: "SUBTOTAL",
                            price: config.grandTotalAmount,
                        },
                    ];
                }

                return paymentRequest;
            },

            /**
             * Retrieves an array containing the total information for a quote.
             *
             * @param {Object} quote - The quote object.
             * @returns {Object} - An object containing the total information for the quote.
             * @property {Array} displayItems - An array of display items representing different components of the total.
             * @property {string} currencyCode - The currency code for the quote.
             * @property {string} totalPriceStatus - The status of the total price.
             * @property {string} totalPrice - The total price of the quote.
             * @property {string} totalPriceLabel - The label for the total price.
             *
             * @example
             * // Example usage of the getTotalsArray method
             * const quote = {
             *   base_subtotal: 100,
             *   tax_amount: 15,
             *   base_discount_amount: 10,
             *   base_shipping_amount: 5,
             *   base_currency_code: 'USD',
             *   base_grand_total: 110
             * };
             *
             * const totalsArray = getTotalsArray(quote);
             * console.log(totalsArray);
             * // Output:
             * // {
             * //   displayItems: [
             * //     { label: 'Subtotal', type: 'SUBTOTAL', price: '$100' },
             * //     { label: 'Tax', type: 'TAX', price: '$15' },
             * //     { label: 'Discount', type: 'DISCOUNT', price: '$10' },
             * //     { label: 'Shipping', type: 'LINE_ITEM', price: '$5', status: 'FINAL' }
             * //   ],
             * //   currencyCode: 'USD',
             * //   totalPriceStatus: 'FINAL',
             * //   totalPrice: '$110',
             * //   totalPriceLabel: 'Order Total'
             * // }
             */
            getTotalsArray(quote) {
                let displayItems = [
                        {
                            label: $t('Subtotal'),
                            type: "SUBTOTAL",
                            price: globalHelper.formatPrice(quote.base_subtotal),
                        }
                    ];

                if (quote.tax_amount !== 0) {
                    displayItems.push({
                        label: $t('Tax'),
                        type: "TAX",
                        price: globalHelper.formatPrice(quote.tax_amount),
                    });
                }

                if (quote.base_discount_amount !== 0) {
                    displayItems.push({
                        label: $t('Discount'),
                        type: "DISCOUNT",
                        price: globalHelper.formatPrice(quote.base_discount_amount)
                    });
                }

                displayItems.push({
                    label: $t('Shipping'),
                    type: "LINE_ITEM",
                    price: globalHelper.formatPrice(quote.base_shipping_amount),
                    status: "FINAL",
                });

                return {
                    displayItems: displayItems,
                    currencyCode: quote.base_currency_code,
                    totalPriceStatus: "FINAL",
                    totalPrice: globalHelper.formatPrice(quote.base_grand_total),
                    totalPriceLabel: $t('Order Total')
                };
            },

            /**
             * Returns a formatted address object.
             *
             * @param {string} email - The email address associated with the address.
             * @param {object} address - The address object.
             * @param {string} address.name - The full name associated with the address.
             * @param {string} address.address1 - The first line of the address.
             * @param {string} address.address2 - The second line of the address.
             * @param {string} address.countryCode - The country code of the address.
             * @param {string} address.administrativeArea - The administrative area of the address.
             * @param {string} address.locality - The city name of the address.
             * @param {string} address.postalCode - The postal code of the address.
             * @param {string} address.phoneNumber - The phone number associated with the address.
             * @returns {object} - The formatted address object.
             * @property {string} emailAddress - The email address associated with the address.
             * @property {string} firstName - The first name associated with the address.
             * @property {string} lastName - The last name associated with the address.
             * @property {string[]} addressLines - The lines of the address.
             * @property {string} cityName - The city name of the address.
             * @property {string} regionId - The ID of the region associated with the address.
             * @property {string} regionCode - The code of the region associated with the address.
             * @property {string} regionName - The name of the region associated with the address.
             * @property {string} countryCode - The country code of the address.
             * @property {string} postalCode - The postal code of the address.
             * @property {string} phoneNumber - The phone number associated with the address.
             */
            getFormattedAddress(email, address) {
                let fullName = typeof address.name !== 'undefined' ? address.name : '',
                    [firstName, lastName] = fullName.split(' ').length > 1 ? fullName.split(' ', 2) : [fullName, ''],
                    address1 = typeof address.address1 !== 'undefined' ? address.address1 : '',
                    address2 = typeof address.address2 !== 'undefined' ? address.address2 : '',
                    region   = globalHelper.getRegionData(address.countryCode, address.administrativeArea);

                return {
                    emailAddress: email,
                    firstName: firstName,
                    lastName: lastName,
                    addressLines: [address1, address2],
                    cityName: address.locality,
                    regionId: region.id,
                    regionCode: region.code,
                    regionName: region.name,
                    countryCode: address.countryCode,
                    postalCode: address.postalCode,
                    phoneNumber: typeof address.phoneNumber !== 'undefined' ? address.phoneNumber : '',
                };
            },

            /**
             * Returns formatted shipping methods data.
             * @param {Object} shippingMethods - An object containing the shipping methods.
             * @returns {Object} - An object containing the formatted shipping methods data.
             */
            getFormattedShippingMethods: function (shippingMethods) {
                let methodData = {},
                    shippingMethodsDataObject = {},
                    shippingOptionsArray = [];

                if (typeof shippingMethods !== 'undefined') {
                    for (let shipMethod in shippingMethods) {
                        if (shippingMethods.hasOwnProperty(shipMethod) && shippingMethods[shipMethod].available) {
                            shippingMethodsDataObject[shippingMethods[shipMethod].method_code] = {
                                method_code: shippingMethods[shipMethod].method_code,
                                carrier_code: shippingMethods[shipMethod].carrier_code
                            };
                            methodData = {
                                id: shippingMethods[shipMethod].method_code,
                                label: globalHelper.formatPrice(shippingMethods[shipMethod].amount) + ': ' + shippingMethods[shipMethod].method_title,
                                description: shippingMethods[shipMethod].carrier_title,
                            };
                            shippingOptionsArray.push(methodData);
                        }
                    }
                }

                return {
                    optionsArray: shippingOptionsArray,
                    methodsDataObject: shippingMethodsDataObject
                };
            },
        }
    }
);
