define(
    [
        'jquery',
        'uiComponent'
    ],
    function (
        $,
        Component
    ) {
        'use strict';

        return Component.extend({
            /**
             * Checkout Config
             */
            config: {
                checkoutIframeId: null,
                editCartUrl: null,
                loginUrl: null,
                resetPasswordUrl: null,
                successActionUrl: null,
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                let self = this;

                self._super();

                self.initGaScript();
                self.removePayPalParamFromUrl();

                return self;
            },

            /**
             * Init GA and Send Data to Iframe
             */
            initGaScript: function () {
                let self = this,
                    gaClientId = 0,
                    frameWindow = document.getElementById(self.checkoutIframeId);

                if(window.ga && ga.loaded) {
                    ga(function (tracker) {
                        gaClientId = tracker.get('clientId');
                    })
                }

                if(frameWindow.dataset.loaded === 'true') {
                    self.sendGaClientId(gaClientId);
                }
                else {
                    frameWindow.onload = function () {
                        self.sendGaClientId(gaClientId);
                    };
                }
            },

            /**
             * Send Ga ClientId to Iframe
             */
            sendGaClientId: function (gaClientId) {
                document.getElementById(this.checkoutIframeId).contentWindow.postMessage({
                    'gaClientId': gaClientId
                }, '*');
            },

            /**
             * Remove paypal_order_id & paypal_funding_source params from url
             */
            removePayPalParamFromUrl: function () {
                history.replaceState && history.replaceState(
                    null, '', location.pathname
                        + location.search
                            .replace(/[\?&]paypal_order_id=[^&]+/, '')
                            .replace(/[\?&]paypal_funding_source=[^&]+/, '')
                            .replace(/^&/, '?')
                );
            }
        });
    }
);