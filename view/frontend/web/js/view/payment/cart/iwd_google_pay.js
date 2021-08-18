define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'mage/url',
        'mage/storage',
    ],
    function (
        $,
        Component,
        customerData,
        urlBuilder,
        storage
    ) {
        'use strict';


        var _this;

        return Component.extend({

            config: {},

            gpay_data: {},

            initialize() {
                _this = this;
                _this._super();
                _this.initGooglePay();
            },

            initGooglePay: function () {
                let self = this,
                    script = document.createElement('script');
                script.src = "https://pay.google.com/gp/p/js/pay.js";
                script.onload = function () {
                    self.googlePay();
                };

                document.head.appendChild(script);
            },

            googlePay: function () {

                var self = _this;

                if( self.gpay_data.merchantInfo == undefined){
                    return;
                }
                const baseRequest = {
                    apiVersion: 2,
                    apiVersionMinor: 0
                };

                const allowedCardNetworks = ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"];

                const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];

                const tokenizationSpecification = {
                    type: 'PAYMENT_GATEWAY',
                    parameters: {
                        'gateway': 'spreedly',
                        'gatewayMerchantId': 'FhhkHPlcdsZJ4Yuonoo5wpe6QtM'
                    }
                };

                const baseCardPaymentMethod = {
                    type: 'CARD',
                    parameters: {
                        allowedAuthMethods: allowedCardAuthMethods,
                        allowedCardNetworks: allowedCardNetworks,
                        billingAddressRequired : true,
                        billingAddressParameters : {
                            format: 'FULL',
                            phoneNumberRequired: true
                        }
                    }
                };

                const cardPaymentMethod = Object.assign(
                    {},
                    baseCardPaymentMethod,
                    {
                        tokenizationSpecification: tokenizationSpecification
                    }
                );

                let paymentsClient = null;

                let options = {
                    button: null
                };

                function getGoogleIsReadyToPayRequest() {
                    return Object.assign(
                        {},
                        baseRequest,
                        {
                            allowedPaymentMethods: [baseCardPaymentMethod]
                        }
                    );
                }

                function getGooglePaymentDataRequest() {
                    const paymentDataRequest = Object.assign({}, baseRequest);
                    paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
                    paymentDataRequest.merchantInfo = {
                        merchantId: self.gpay_data.merchantInfo.merchantId,
                        merchantName: self.gpay_data.merchantInfo.merchantName,
                    };

                    if(self.config.virtual){
                        paymentDataRequest.emailRequired = true;
                        paymentDataRequest.callbackIntents = ["PAYMENT_AUTHORIZATION"];
                    }else{
                        paymentDataRequest.callbackIntents = ["SHIPPING_ADDRESS", "SHIPPING_OPTION", "PAYMENT_AUTHORIZATION"];
                        paymentDataRequest.emailRequired = true;
                        paymentDataRequest.shippingAddressRequired = true;
                        paymentDataRequest.shippingAddressParameters = getGoogleShippingAddressParameters();
                        paymentDataRequest.shippingOptionRequired = true;
                    }

                    paymentDataRequest.transactionInfo = getGoogleTransactionInfo;

                    return paymentDataRequest;
                }


                function getGooglePaymentsClient() {
                    if (paymentsClient === null) {
                        let params = {
                            environment: self.gpay_data.merchantInfo.environment,
                            merchantInfo: {
                                merchantName: self.gpay_data.merchantInfo.merchantName,
                                merchantId: self.gpay_data.merchantInfo.merchantId
                            }
                        };
                        if(self.config.virtual){
                            params.paymentDataCallbacks = {
                                onPaymentAuthorized: onPaymentAuthorized,
                            };
                        }else{
                            params.paymentDataCallbacks = {
                                onPaymentAuthorized: onPaymentAuthorized,
                                onPaymentDataChanged: onPaymentDataChanged
                            };
                        }
                        paymentsClient = new google.payments.api.PaymentsClient(params);
                    }
                    return paymentsClient;
                }

                function onPaymentAuthorized(paymentData) {
                    return new Promise(function (resolve, reject) {
                        // handle the response
                        processPayment(paymentData)
                            .then(function () {
                                resolve({transactionState: 'SUCCESS'});
                            })
                            .catch(function () {
                                resolve({
                                    transactionState: 'ERROR',
                                    error: {
                                        intent: 'PAYMENT_AUTHORIZATION',
                                        message: 'Insufficient funds',
                                        reason: 'PAYMENT_DATA_INVALID'
                                    }
                                });
                            });
                    });
                }

                function calculateNewTransactionInfo(shippingOptionId) {
                    let newTransactionInfo = getGoogleTransactionInfo();

                    let shippingCost = getShippingCosts()[shippingOptionId];
                    newTransactionInfo.displayItems.push({
                        type: "LINE_ITEM",
                        label: "Shipping cost",
                        price: shippingCost,
                        status: "FINAL"
                    });

                    let totalPrice = 0.00;
                    newTransactionInfo.displayItems.forEach(displayItem => totalPrice += parseFloat(displayItem.price));
                    newTransactionInfo.totalPrice = totalPrice.toString();

                    return newTransactionInfo;
                }


                function onPaymentDataChanged(intermediatePaymentData) {

                    return new Promise(function (resolve, reject) {

                        var transactionUrl = 'rest/default/V1/googlepay/guest/' + self.config.quote_id + '/transaction-information';

                        let payload = {
                            'data': intermediatePaymentData
                        };
                        return storage.post(
                            urlBuilder.build(transactionUrl),
                            JSON.stringify(payload),
                            false
                        ).done(
                            function (result) {
                                resolve(result[0]);
                            }
                        ).fail(
                            function (response) {
                                if (response.responseText != null && response.responseText.indexOf("[{\"transactionState\":\"SUCCESS\"}]") !== -1) {
                                    resolve(
                                        {
                                            transactionState: 'SUCCESS',
                                        }
                                    );
                                } else {
                                    console.error(response);
                                    resolve(
                                        {
                                            transactionState: 'ERROR',
                                            error: {
                                                intent: 'PAYMENT_AUTHORIZATION',
                                                message: 'Unable to process payment with provided payment credentials',
                                                reason: 'PAYMENT_DATA_INVALID'
                                            }
                                        }
                                    );
                                }
                            }
                        );

                    });
                }

                function onGooglePayLoaded() {

                    const paymentsClient = getGooglePaymentsClient();
                    paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest())
                        .then(function (response) {
                            if (response.result) {
                                addGooglePayButton();
                            }
                        })
                        .catch(function (err) {
                            console.error(err);
                        });
                }

                function addGooglePayButton() {
                    const paymentsClient = getGooglePaymentsClient();
                    var button;

                    var gPayButtons = self.config.button;
                    gPayButtons.forEach(function (element) {
                        $(".google-pay-cart").each(function (index, ele) {
                            $(this).html('');
                            button = paymentsClient.createButton({
                                buttonColor: 'black',
                                buttonType: 'plain',
                                buttonSizeMode: 'fill',
                                onClick: onGooglePaymentButtonClicked
                            });
                            $('<div/>', {
                                id: "google-pay-container-minicart-" + index + '-' + element,
                                class: "google-pay-iwd-button",
                                'data-type': element
                            }).append(button).appendTo(this);

                        });

                    });

                }

                function getGoogleTransactionInfo() {
                    return self.gpay_data.transactionInfo;
                }

                function getGoogleShippingAddressParameters() {
                    return {
                        allowedCountryCodes: self.gpay_data.allowedCountryCodes,
                        phoneNumberRequired: true,
                    };
                }

                function onGooglePaymentButtonClicked() {
                    options.button = $(this).closest('.google-pay-iwd-button').data('type');
                    const paymentDataRequest = getGooglePaymentDataRequest();
                    paymentDataRequest.transactionInfo = getGoogleTransactionInfo();
                    const paymentsClient = getGooglePaymentsClient();
                    paymentsClient.loadPaymentData(paymentDataRequest);

                }

                function processPayment(paymentData) {
                    return new Promise(function (resolve, reject) {
                        if (!$('#googlePayLoader').length) {
                            let div = '<div id="googlePayLoader"></div>';
                            $('body').append(div);
                            $('#googlePayLoader').loader({
                                icon: window.loader.icon
                            }).loader('show');
                        }
                        paymentData.paymentToken = options.button;
                        paymentData.apiKey = self.config.api_key;
                        paymentData.quoteId = self.config.quote_id;
                        paymentData.customer_token = self.config.customer_token;
                        paymentData.connector = true;

                        setTimeout(function () {
                            $.ajax({
                                url: self.config.app_url,
                                type: 'POST',
                                data: {paymentData: paymentData, session_id: $('#session_id').val()},
                                dataType: 'json',
                                success: function (data) {
                                    if (data.error) {
                                        let messageEl = document.getElementById('errors-' + instance);
                                        $('#googlePayLoader').loader('hide');
                                        messageEl.innerHTML += data.error + "<br/>";
                                        messageEl.style.color = "red";

                                        return data;
                                    }
                                    if (data.order_id) {
                                        let successUrl = self.config.base_url + '/iwd_checkout/index/success',
                                            successParams = 'order_id=' + data.order_id
                                                + '&order_increment_id=' + data.order_increment_id
                                                + '&order_status=' + data.order_status
                                                + '&quote_id=' + data.quote_id;

                                        window.location.href = successUrl + '?' + successParams;
                                    }
                                    return data;
                                }, error: function (response) {
                                    $('#googlePayLoader').loader('hide');
                                    console.log(response.error);
                                }
                            });
                            resolve({});
                        }, 3000);
                    });

                }

                onGooglePayLoaded();

            },

        });
    }
);
