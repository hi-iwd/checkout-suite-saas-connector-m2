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
                productionDomainName: null,
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
                self.initApplePay();

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
            },

            /**
             * Init Apple Pay Logic
             */
            initApplePay: function () {
                if(window.addEventListener){
                    window.addEventListener('message', this.applePayManager, false);
                }
            },

            /**
             * Apple Pay Manager
             */
            applePayManager: function (event) {
                let self = this;

                if(event.data.type === 'isAppleAvailable') {
                    if (window.ApplePaySession) {
                        let merchantIdentifier = event.data.merchantIdentifier,
                            promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);

                        self.productionDomainName = event.data.productionDomainName;

                        promise.then(function (canMakePayments) {
                            if (canMakePayments) {
                                if(window.location.protocol == 'https:'){
                                    sendPostMessageToIframe({type:'isAppleAvailable',isAppleAvailable:1});
                                    console.log('hi, I can do ApplePay');
                                }else{
                                    console.log('HTTPS protocol is required to Apple Pay');
                                }
                            } else {
                                //skip for now
                                //sendPostMessageToIframe({type:'isAppleAvailable',isAppleAvailable:2});
                                console.log('ApplePay is possible on this browser, but not currently activated.');
                            }
                        });
                    } else {
                        //skip for now
                        //sendPostMessageToIframe({type:'isAppleAvailable',isAppleAvailable:0});
                        console.log('ApplePay is not available on this browser');
                    }
                }

                if(event.data.type === 'initApplePay') {
                    let session = new ApplePaySession(event.data.applePaySessionConfig.version,event.data.applePaySessionConfig.paymentRequest),
                        paymentRequest = event.data.applePaySessionConfig.paymentRequest,
                        appleConfig = event.data.appleConfig;

                    self.productionDomainName = appleConfig.PRODUCTION_DOMAINNAME;

                    // Merchant Validation
                    session.onvalidatemerchant = function (event) {
                        let promise = performValidation(event.validationURL);
                        promise.then(function (merchantSession) {
                            session.completeMerchantValidation(merchantSession);
                        });
                    }

                    function performValidation(valURL) {
                        return new Promise(function(resolve, reject) {
                            let xhr = new XMLHttpRequest();
                            xhr.onload = function() {
                                let textResponse = this.responseText;
                                let data = JSON.parse(textResponse);
                                resolve(data);
                            };
                            xhr.onerror = reject;
                            xhr.open('GET',self.productionDomainName + '/spreedly/apple-pay/validation?host='+window.location.host+'&u=' + valURL);
                            xhr.send();
                        });
                    }

                    session.onpaymentmethodselected = function(event) {
                        let newTotal = {
                            type: 'final',
                            label: paymentRequest.total.label,
                            amount: paymentRequest.total.amount
                        };

                        let newLineItems =[
                            {
                                type: 'final',
                                label: paymentRequest.lineItems[0].label,
                                amount: paymentRequest.lineItems[0].amount
                            },
                            {
                                type: 'final',
                                label: paymentRequest.lineItems[1].label,
                                amount: paymentRequest.lineItems[1].amount
                            },
                            {
                                type: 'final',
                                label: paymentRequest.lineItems[2].label,
                                amount: paymentRequest.lineItems[2].amount
                            },
                            {
                                type: 'final',
                                label: paymentRequest.lineItems[3].label,
                                amount: paymentRequest.lineItems[3].amount
                            }
                        ];

                        session.completePaymentMethodSelection( newTotal, newLineItems );

                    }

                    session.onpaymentauthorized = function (event) {
                        let promise = sendPaymentToken(event.payment.token);
                        promise.then(function (success) {
                            let status;
                            if (success){
                                status = ApplePaySession.STATUS_SUCCESS;
                                sendPostMessageToIframe({type:'success',payment:event.payment.token});
                            } else {
                                status = ApplePaySession.STATUS_FAILURE;
                            }
                            session.completePayment(status);
                        });
                    }

                    function sendPaymentToken(paymentToken) {
                        let debug = true;
                        return new Promise(function(resolve, reject) {
                            if ( debug == true )
                                resolve(true);
                            else
                                reject;
                        });
                    }

                    session.oncancel = function(event) {
                        sendPostMessageToIframe({type:'jsLoader',jsLoader:'fadeOut',message:'',session:'oncancel'});
                    }

                    session.begin();
                    sendPostMessageToIframe({type:'jsLoader',jsLoader:'fadeOut',message:'',session:'begin'});

                }
                
                function sendPostMessageToIframe(data){
                    document.getElementById('iwd_checkout_iframe').contentWindow.postMessage(data,self.productionDomainName);
                }
            },
        });
    }
);