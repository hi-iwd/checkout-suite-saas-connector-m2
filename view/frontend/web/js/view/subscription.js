define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    // 'https://unpkg.com/@paypal/paypal-js@4.0.6/dist/iife/paypal-js.min.js'
], function (Component, customerData, $) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();

            let _self = this;
            customerData.reload(['subscription']).then(function (){
                let currentProduct = [];
                _self.subscription = customerData.get('subscription');
                if (_self.subscription().items.length > 0) {
                    _self.subscription().items.filter(function (item) {
                        window.currentProductSku.filter(function (product) {
                            if (product.sku == item.sku) {
                                currentProduct.push(item);
                            }
                        });
                    })
                }

                if (currentProduct.length > 0 && window.currentProductSku.length == 1) {
                    _self.initBtn(currentProduct);
                } else if(currentProduct.length > 0 && window.currentProductSku.length > 1) {
                    _self.confInit();
                }
            });

        },

        addButton: function (btnInfo) {
            document.querySelector('#paypal-subscription-button').dataset.planId = btnInfo.plan_id;
            paypal.Buttons({
                style: {
                    shape: window.subscription_btn.btn_shape,
                    color: window.subscription_btn.btn_color,
                    layout: 'vertical',
                    label: 'subscribe'
                },
                createSubscription: function (data, actions) {
                    var obj = {
                        /* Creates the subscription */
                        plan_id: btnInfo.plan_id
                    };
                    if (btnInfo.quantity_supported == 1) {
                        obj.quantity = $("input[name='qty']").val();
                    }
                    return actions.subscription.create(obj);
                },
                onApprove: function (data, actions) {
                    setTimeout(function () {
                        customerData.set('messages', {
                            messages: [{
                                type: 'success',
                                text: 'Successfully subscribed'
                            }]
                        });
                    }, 1000);
                }
            }).render('#paypal-subscription-button');
        },

        initBtn: function (currentProduct) {
            let _self = this;
            let btnInfo = this.getButtonInfo(currentProduct);
            if (typeof window.paypal === 'undefined') {
                window.paypalLoadScript(
                    {
                        "client-id": btnInfo.client_id,
                        "commit": "false",
                        "intent": "capture",
                        "components": "buttons,messages,applepay",
                        "vault": "true",
                        "merchant-id": btnInfo.merchant_id,
                        "data-partner-attribution-id": btnInfo.partner_attribution_id
                    }
                ).then((paypal) => {
                    _self.addButton(btnInfo);
                });
            } else {
                this.addButton(btnInfo);
            }
        },

        getButtonInfo(currentProduct) {
            return currentProduct[0];
        },

        confInit() {

            var id = null;
            let _self = this;

            if ($('div.swatch-opt').data('mageSwatchRenderer')) {

                $(document).on('click touchstart', '.swatch-option', function () {
                    var id = null;
                    $('#paypal-subscription-button').html('')
                    let list = $('div.swatch-opt').data('mageSwatchRenderer').options.jsonConfig.index;
                    var product = new Object();
                    var currentProduct = [];
                    $('div.swatch-attribute').each(function () {
                        product[$(this).attr('attribute-id')] = $(this).attr('option-selected');
                    });

                    $.each($('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index, function (index, value) {
                        var difference = Object.keys(product).filter(k => product[k] !== value[k]);
                        if (difference.length == 0) {
                            id = index;
                            _self.subscription().items.filter(function (item) {
                                window.currentProductSku.filter(function (product) {
                                    if (product.id == id && product.sku == item.sku) {
                                        currentProduct.push(item);

                                    }
                                });
                            });

                            if (currentProduct.length > 0)  {
                                _self.initBtn(currentProduct);
                            }

                        }
                    });

                })
            }
            return id;
        }
    });
});
