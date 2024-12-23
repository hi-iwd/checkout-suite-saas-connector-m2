define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'dominatePayPalMessagingConfigurator',
    ],
    function (
        $,
        Component,
        customerData,
        quote,
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

                quote.totals.subscribe(function (totals) {
                    self.initPayPalCreditMsg('.iwd-cart-sidebar-wrapper .iwd-paypal-credit-msg', totals);
                }, self);
            },

            initPayPalCreditMsg: function (element, totals) {
                let self = this,
                    creditMsgConfig = self.cart().paypal_credit_msg_config,
                    logoConfig = {type: creditMsgConfig.logo_type},
                    amount = (totals && totals.grand_total !== undefined) ? totals.grand_total : creditMsgConfig.grand_total_amount;

                if (creditMsgConfig.logo_type === 'alternative' || creditMsgConfig.logo_type === 'primary') {
                    logoConfig.position = creditMsgConfig.logo_position;
                }

                if (creditMsgConfig.msg_configurator_data) {
                    const creditMsgElement = $(element).find('[data-pp-amount]');

                    if (creditMsgElement.length) {
                        creditMsgElement.attr('data-pp-amount', amount);
                    } else {
                        const creditMsg = window.merchantConfigurators?.generateMessagingCodeSnippet({
                            messageConfig: creditMsgConfig.msg_configurator_data.cart,
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