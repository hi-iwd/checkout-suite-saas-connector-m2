define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'dominatePayPalMessagingConfigurator',
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
                let self = this,
                    creditMsgConfig = self.cart().paypal_credit_msg_config,
                    logoConfig = {type: creditMsgConfig.logo_type},
                    amount = self.cart().subtotalAmount;

                if (creditMsgConfig.logo_type === 'alternative' || creditMsgConfig.logo_type === 'primary') {
                    logoConfig.position = creditMsgConfig.logo_position;
                }

                if (amount == 0) return;

                if (creditMsgConfig.msg_configurator_data) {
                    const creditMsgElement = $(element).find('[data-pp-amount]');

                    if (creditMsgElement.length) {
                        creditMsgElement.attr('data-pp-amount', amount);
                    } else {
                        const creditMsg = window.merchantConfigurators?.generateMessagingCodeSnippet({
                            messageConfig: creditMsgConfig.msg_configurator_data.cart_preview,
                            productPrice: amount
                        });

                        $(element).prepend(creditMsg);
                    }
                } else if (window.paypal && creditMsgConfig.status === 1) {
                    window.paypal.Messages({
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
            },
        });
    }
);