define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'iwdBrainTree',
        'iwdApplePay',
        'iwdApplePayDataCol'
    ],
    function (
        $,
        Component,
        customerData,
        braintree,
        applepay,
        iwdApplePayDataCol
    ) {
        'use strict';

        return Component.extend({

            checkoutIframeId: null,
            applePayInstance: null,
            deviceData: null,
            store_name: null,

            initialize: function () {
                this._super();

                if (!window.ApplePaySession) {
                    return;
                }

                if (!ApplePaySession.canMakePayments()) {
                    console.error('This device is not capable of making Apple Pay payments');
                    return;
                }

                this.initPostMsg();
            },

            initApplePay: function (token) {
                let self = this;

                braintree.create({
                    authorization: token
                }).then(function (clientInstance) {
                    iwdApplePayDataCol.create({
                        client: clientInstance
                    }, function (err, dataCollectorInstance) {
                        if (err) {
                            console.error(err);
                            return;
                        }

                        self.deviceData = dataCollectorInstance.deviceData;
                    });

                    return applepay.create({
                        client: clientInstance
                    });
                }).then(function (applePayInstance) {
                    self.applePayInstance = applePayInstance;
                    self.applePayTrigger();
                }).catch(function (err) {
                    console.log(err);
                });
            },

            applePayTrigger: function (payment = false) {
                let self = this;

                if (payment) {
                    var paymentRequest = self.applePayInstance.createPaymentRequest({
                        total: payment,
                        requiredBillingContactFields: ["postalAddress"]
                    });
                    var session = new ApplePaySession(3, paymentRequest);

                    session.onvalidatemerchant = function (event) {
                        self.applePayInstance.performValidation({
                            validationURL: event.validationURL,
                            displayName: payment.label
                        }).then(function (merchantSession) {
                            session.completeMerchantValidation(merchantSession);
                        }).catch(function (validationErr) {
                            console.error('Error validating merchant:', validationErr);
                            session.abort();
                        });
                    };

                    session.onpaymentauthorized = function (event) {
                        self.applePayInstance.tokenize({
                            token: event.payment.token
                        }).then(function (payload) {
                            document.getElementById(self.checkoutIframeId).contentWindow.postMessage({
                                action: 'applePayData',
                                applePay: {
                                    'nonce': payload.nonce,
                                    'billingPostalCode': event.payment.billingContact.postalCode,
                                    'deviceData': self.deviceData
                                }
                            }, '*');

                            session.completePayment(ApplePaySession.STATUS_SUCCESS);
                        }).catch(function (tokenizeErr) {
                            console.error('Error tokenizing Apple Pay:', tokenizeErr);
                            session.completePayment(ApplePaySession.STATUS_FAILURE);
                        });
                    };

                    session.oncancel = event => {
                        console.log('cancel');
                    };

                    session.begin();
                }
            },

            initPostMsg: function () {
                let self = this,
                    postMsgManager = function (event) {
                        if (event.data.action === 'initApplePay') {
                            self.initApplePay(event.data.tokenization_key);
                        }

                        if (event.data.action === 'triggerApplePay') {
                            self.applePayTrigger(event.data.total);
                        }
                    };

                if (window.addEventListener) {
                    window.addEventListener('message', postMsgManager, false);
                }
            },
        });
    }
);
