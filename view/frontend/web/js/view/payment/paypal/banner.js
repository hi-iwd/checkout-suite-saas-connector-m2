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
            config: null,

            /**
             * Initializes the module.
             *
             * This function initializes the module by rendering the banner and subscribing to changes in the customer's cart.
             * Whenever the cart data changes, the banner is re-rendered.
             */
            initialize: function () {
                this._super();
                this.cart = customerData.get('cart');

                this.renderBanner();

                this.cart.subscribe(() => {
                    this.renderBanner();
                });
            },

            /**
             * Renders a banner for displaying PayPal credit messaging using the provided configuration and subtotal amount.
             * Retrieves the element by ID and updates the credit message with the subtotal amount.
             * If the credit message element does not exist, generates a new credit message based on the config and amount.
             */
            renderBanner: function () {
                const element = $('.iwd-paypal-banner');
                const amount = this.cart().subtotalAmount;

                if (!this.config) return;

                const creditMsgElement = element.find('[data-pp-amount]');

                if (creditMsgElement.length) {
                    if (amount == 0) {
                        creditMsgElement.remove();
                    } else {
                        creditMsgElement.attr('data-pp-amount', amount);
                    }
                } else if (amount !== undefined && amount != 0) {
                    const creditMsg = window.merchantConfigurators?.generateMessagingCodeSnippet({
                        messageConfig: this.config,
                        productPrice: amount
                    });

                    element.prepend(creditMsg).addClass('block-promo');
                }
            },
        });
    }
);