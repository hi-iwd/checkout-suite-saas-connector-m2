define(
    [
        'uiComponent',
    ],
    function (
        Component,
    ) {
        'use strict';

        return Component.extend({
            /**
             * Button Config
             */
            config: {
                containerId: null,
                checkoutPageUrl: null,
                grandTotalAmount: 0,
                creditStatus: 1,
                venmoStatus: 1,
                btnShape: 'rect',
                btnColor: 'gold'
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                let self = this;

                self._super();

                if (window.paypal) {
                    let paypal = window.paypal;

                    paypal.Buttons({
                        fundingSource: self.creditStatus == 1 || self.venmoStatus == 1 ? '' : 'paypal',
                        style: {
                            layout: 'horizontal',
                            size: 'responsive',
                            shape: self.btnShape,
                            color: self.btnColor,
                            height: 45,
                            fundingicons: false,
                            tagline: false,
                        },

                        createOrder: function(data, actions) {
                            return actions.order.create({
                                purchase_units: [{
                                    amount: {
                                        value: self.grandTotalAmount
                                    }
                                }]
                            });
                        },

                        onApprove: function(data) {
                            window.location.href = self.checkoutPageUrl + '?paypal_order_id=' + data.orderID;
                        }
                    }).render('#' + self.containerId);
                }

                return self;
            },
        });
    }
);