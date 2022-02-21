define(
    [
        'jquery',
        'uiComponent',
        'IWD_CheckoutConnector/js/libs/iframeResizer'
    ],
    function (
        $,
        Component,
        iframeResize
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

                let changeUrlAction = function(event) {
                    if (event.data.changeUrlAction === 'edit_cart') {
                        window.location.href = self.editCartUrl;
                    }
                    else if (event.data.changeUrlAction === 'authenticate') {
                        let data = { username: event.data.login, password: event.data.password };

                        $.ajax({
                            dataType : "json",
                            method: "POST",
                            url: self.loginUrl,
                            data: JSON.stringify(data)
                        }).done(function (response) {
                            if (response.errors) {
                                sendMessage(response.message);
                            } else {
                                location.reload();
                            }
                        }).fail(function () {
                            let msg = 'Could not authenticate. Please try again later';
                            sendMessage(msg);
                        });
                    }
                    else if(event.data.changeUrlAction === 'reset_pass'){
                        window.location.href = self.resetPasswordUrl;
                    }
                };

                let sendMessage = function(msg) {
                    document.getElementById(self.checkoutIframeId).contentWindow.postMessage({
                        'action': 'sendMassage',
                        'message': msg
                    }, '*');
                };

                let actionSuccess = function(event) {
                    if (event.data.actionSuccess) {
                        let successUrl = self.successActionUrl,
                            successParams = event.data.actionSuccess;

                        window.location.href = successUrl+'?'+successParams;
                    }
                };

                if (window.addEventListener) {
                    window.addEventListener("message", changeUrlAction, false);
                    window.addEventListener("message", actionSuccess, false);
                } else if (window.attachEvent) {
                    window.attachEvent("onmessage", changeUrlAction);
                    window.attachEvent("onmessage", actionSuccess);
                }

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