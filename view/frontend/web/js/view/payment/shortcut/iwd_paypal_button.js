define(
    [
        'jquery',
        'uiComponent',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/applepay/adapter',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/googlepay/adapter',
        'mage/translate'
    ],
    function (
        $,
        Component,
        ApplePayAdapter,
        GooglePayAdapter,
        $t
    ) {
        'use strict';

        return Component.extend({
            /**
             * Configuration object for the application.
             *
             * @typedef {Object} Config
             * @property {string} containerId - The ID of the container element for the application.
             * @property {string} checkoutIframeId - The ID of the checkout iframe element.
             * @property {string} checkoutPageUrl - The URL of the checkout page.
             * @property {string} successActionUrl - The URL to redirect the user after a successful action.
             * @property {string} dominateApiKey - The API key for the Dominate service.
             * @property {string} dominateAppUrl - The URL of the Dominate application.
             * @property {string} customerToken - The customer token for authentication.
             * @property {number} quoteId - The ID of the quote.
             * @property {number} maskedQuoteId - The masked ID of the quote.
             * @property {boolean} isVirtual - Flag indicating if the order is virtual.
             * @property {string} displayName - The display name of the store.
             * @property {boolean} isLoggedIn - Flag indicating if the customer is logged in.
             * @property {boolean} isCheckoutPage - Flag indicating if the current page is the checkout page.
             * @property {boolean} isCheckoutAllowed - Flag indicating if checkout is allowed.
             * @property {string} storeCode - The store code.
             * @property {string} currencyCode - The currency code.
             * @property {number} grandTotalAmount - The total amount of the order.
             * @property {string[]} allowedCountryCodes - The list of allowed country codes for shipping.
             * @property {boolean} paypalStatus - Flag indicating if PayPal is enabled.
             * @property {string} paypalEnvironment - The environment for PayPal integration.
             * @property {boolean} creditStatus - Flag indicating if credit card payment is enabled.
             * @property {boolean} venmoStatus - Flag indicating if Venmo payment is enabled.
             * @property {boolean} applepayStatus - Flag indicating if Apple Pay is enabled.
             * @property {boolean} googlepayStatus - Flag indicating if Google Pay is enabled.
             * @property {string} btnShape - The shape of the payment button.
             * @property {string} btnColor - The color of the payment button.
             */
            config: {
                containerId: null,
                checkoutIframeId: null,
                checkoutPageUrl: null,
                successActionUrl: null,
                dominateApiKey: null,
                dominateAppUrl: null,
                customerToken: null,
                quoteId: 0,
                maskedQuoteId: 0,
                isVirtual: false,
                displayName: $t('Store'),
                isLoggedIn: false,
                isCheckoutPage: false,
                isCheckoutAllowed: true,
                storeCode: "default",
                currencyCode: 'USD',
                grandTotalAmount: 0,
                allowedCountryCodes: ['US'],
                paypalStatus: false,
                paypalEnvironment: 'live',
                creditStatus: false,
                venmoStatus: false,
                applepayStatus: false,
                googlepayStatus: false,
                btnShape: 'rect',
                btnColor: 'gold',
            },

            /**
             * The initialize function is used to initialize the payment methods.
             * It checks the configuration for PayPal status and if it is enabled, it initializes PayPal payment method.
             * If the isCheckoutPage is false, it also initializes the Express Payments.
             * It also checks the configuration for Apple Pay status and if it is enabled, it initializes Apple Pay payment method.
             * It also checks the configuration for Google Pay status and if it is enabled, it initializes Google Pay payment method.
             *
             * @returns {object} The current instance of the class.
             */
            initialize: function () {
                this._super();

                if (this.config.paypalStatus && window.paypal) {
                    if (!this.config.isCheckoutPage) {
                        this.initExpressPayments();
                    }

                    if (this.config.applepayStatus) {
                        this.initApplePay();
                    }

                    if (this.config.googlepayStatus) {
                        this.initGooglePay();
                    }
                }

                return this;
            },

            /**
             * Initializes the PayPal Express Payments feature.
             *
             * The function sets up the PayPal Express Checkout button and handles the payment process.
             */
            initExpressPayments: function () {
                let self = this,
                    clickedFundingSource = 'paypal';

                window.paypal.Buttons({
                    fundingSource: self.config.creditStatus || self.config.venmoStatus ? '' : 'paypal',
                    style: {
                        layout: 'horizontal',
                        size: 'responsive',
                        shape: self.config.btnShape,
                        color: self.config.btnColor,
                        height: 45,
                        fundingicons: false,
                        tagline: false,
                    },

                    onClick: function(data)  {
                        clickedFundingSource = data.fundingSource;
                    },

                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: self.config.grandTotalAmount
                                }
                            }]
                        });
                    },

                    onApprove: function(data) {
                        window.location.href = self.config.checkoutPageUrl + '?paypal_order_id=' + data.orderID
                            + '&paypal_funding_source=' + clickedFundingSource;
                    }
                }).render('#' + self.config.containerId);
            },

            /**
             * Initialize Apple Pay adapter
             *
             * This function sets the configuration for Apple Pay adapter and initializes it.
             *
             * @function initApplePay
             * @returns {void}
             */
            initApplePay: function () {
                ApplePayAdapter.setConfig(this.config);
                ApplePayAdapter.init();
            },

            /**
             * Initialize Google Pay adapter
             *
             * This function sets the configuration for Google Pay adapter and initializes it.
             *
             * @function GooglePayAdapter
             * @returns {void}
             */
            initGooglePay: function () {
                GooglePayAdapter.setConfig(this.config);
                GooglePayAdapter.init();
            }
        });
    }
);