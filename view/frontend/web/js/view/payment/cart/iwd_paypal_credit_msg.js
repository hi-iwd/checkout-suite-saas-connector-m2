define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
    ],
    function (
        $,
        Component,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'IWD_CheckoutConnector/payment/cart/iwd_paypal_credit_msg'
            },

            /**
             * Init
             */
            initialize: function () {
                let self = this;

                self._super();
                self.cart = customerData.get('cart');

                self.cart.subscribe(function() {
                    self.initPayPalCreditMsg('.iwd-paypal-credit-msg');
                });
            },

            initPayPalCreditMsg: function (element = '.iwd-paypal-credit-msg:not([data-pp-id])') {
                let self = this;

                if (window.paypal) {
                    let paypal = window.paypal,
                        creditMsgConfig = self.cart().paypal_credit_msg_config,
                        logoConfig = {type: creditMsgConfig.logo_type},
                        amount = self.cart().subtotalAmount;

                    if(creditMsgConfig.logo_type === 'alternative' || creditMsgConfig.logo_type === 'primary') {
                        logoConfig.position = creditMsgConfig.logo_position;
                    }
                    if($('.iwd-paypal-credit-msg').closest(".iwd-cart-sidebar-wrapper").length > 0) {
                        amount = creditMsgConfig.grand_total_amount;
                    }

                    if(amount > 0) {
                        paypal.Messages({
                            amount: amount,
                            pageType: 'cart',
                            style: {
                                layout: 'text',
                                logo: logoConfig,
                                text: {
                                    color: creditMsgConfig.text_color
                                }
                            },
                        }).render(element);
                    }
                }
            },
        });
    }
);