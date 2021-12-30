define([
    'uiComponent',
    'jquery'
], function (Component, $) {
    'use strict';

    var _this;

    return Component.extend({

        config: {
            client_id: null,
            merchant_id: null
        },

        initialize: function () {
            _this = this;
            _this._super();

            if (typeof window.paypal === 'undefined') {
                let _self = this,
                    credit_msg = $(".iwd-paypal-product-credit-ms");

                if (credit_msg.length > 0) {
                    credit_msg.insertBefore("#product-addtocart-button");
                }

                window.paypalLoadScript(
                    {
                        "client-id": _self.config.client_id,
                        "commit": "false",
                        "intent": "authorize",
                        "components": "buttons,messages",
                        "vault": "false",
                        "enable-funding": "paylater,venmo",
                        "merchant-id": _self.config.merchant_id,
                        "currency": _self.config.currency,
                    }
                ).then(() => {
                    _self.addButton();
                });
            } else {
                this.addButton();
            }
            this.swatchOptions();
            this.bundleOptions();
        },

        addButton: function () {
            let container = '.iwd-paypal-product-credit-ms',
                _self = this;
            setTimeout(function () {
                $(container).html();
                paypal.Messages({
                    amount: _self.getValue(),
                    pageType: 'payment',
                    style: {
                        layout: 'text',
                        logo: _self.config.logoConfig,
                        text: {
                            color: _self.config.color
                        }
                    },
                }).render(container);
            }, 300);
        },

        swatchOptions: function () {
            let _self = this;
            $(".swatch-option").on("click", function () {
                _self.addButton();
            });

            $(".product-options-wrapper input").on("change click", function () {
                _self.addButton();
            });
        },

        getValue: function () {
            var val = $('.product-info-price .price').text(),
                conf_price = '.price-configured_price .price';

            if ($(conf_price).length) {
                val = $(conf_price).text();
            }

            if (isNaN(val)) {
                val = val.replace(/[^0-9\.]/g, '');
                if (val.split('.').length > 2)
                    val = val.replace(/\.+$/, "");
            }

            return parseFloat(val).toFixed(2);
        },

        bundleOptions: function () {
            let _self = this;
            $("#bundle-slide").on("click", function () {
                _self.addButton();
            });
        }

    });
});
