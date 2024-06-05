define(
    [
        'jquery',
        'uiComponent',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/applepay/adapter',
        'mage/translate'
    ],
    function (
        $,
        Component,
        ApplePayAdapter,
        $t
    ) {
        'use strict';

        return Component.extend({
            /**
             * Button Config
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
                creditStatus: true,
                venmoStatus: true,
                applepayStatus: false,
                btnShape: 'rect',
                btnColor: 'gold',
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                let self = this;

                self._super();

                if (window.paypal) {
                    if (!self.config.isCheckoutPage) {
                        self.initExpressPayments();
                    }

                    if (self.config.applepayStatus) {
                        self.initApplePay();
                    }
                }

                return self;
            },

            /**
             * Initializes the Express Payments feature.
             *
             * This function sets up the PayPal payment buttons and handles the logic for creating and approving orders.
             *
             * @function initExpressPayments
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
            }
        });
    }
);