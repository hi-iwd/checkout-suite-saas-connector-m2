define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
    ],
    function (
        $,
        Component,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'IWD_CheckoutConnector/payment/cart/iwd_apple_pay'
            },

            initialize: function () {
                let self = this;
                self._super();
            },

            initApplePay: function () {
                let self = this,
                    appleSettings = new Object(),
                    applePayWrapper = $('.applePayWrapper'),
                    paymentMethods;

                self.cart = customerData.get('cart');
                appleSettings.host = self.cart().apple_pay;

                if (window.ApplePaySession) {
                    let merchantIdentifier = appleSettings.host.merchant_id,
                        promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
                    promise.then(function (canMakePayments) {
                        if (canMakePayments) {
                            if(window.location.protocol == 'https:'){
                                console.log('hi, I can do ApplePay');
                                if(appleSettings.host.payment_methods != null && appleSettings.host.payment_methods != 'null'){
                                    paymentMethods = Object.entries(JSON.parse(appleSettings.host.payment_methods));
                                    paymentMethods.forEach(([key, value]) => {
                                        let div = '<div data-gateway-type="'+key+'" data-gateway-environment="'+value+'" class="applePay active"></div>';
                                        applePayWrapper.append(div);
                                    });
                                    self.initApplePaySession();
                                }
                            }else{
                                console.log('HTTPS protocol is required to Apple Pay');
                            }
                        } else {
                            console.log('ApplePay is possible on this browser, but not currently activated.');
                        }
                    });
                } else {
                    console.log('ApplePay is not available on this browser');
                }
            },

            initApplePaySession: function(){
                let self = this;
                self.cart = customerData.get('cart');
                let appleSettings = new Object();
                appleSettings.spreedly = {};
                appleSettings.host = self.cart().apple_pay;

                $(document).on('click touch','.applePay', function (evt) {
                    appleSettings.spreedly.gateway_type = $(this).attr('data-gateway-type');
                    appleSettings.spreedly.gateway_environment = $(this).attr('data-gateway-environment');
                    if(!$('#applePayLoader').length){
                        let div = '<div id="applePayLoader"></div>';
                        $('body').append(div);
                        $('#applePayLoader').loader({
                            icon: window.loader.icon
                        });
                    }
                    var paymentRequest = {
                        currencyCode: appleSettings.host.currency_code,
                        countryCode: appleSettings.host.country_code,
                        requiredShippingContactFields: ['name', 'phone', 'postalAddress', 'email'],
                        shippingMethods: [],
                        lineItems: [],
                        total: {
                            label: 'Apple Pay',
                            amount: 0,
                        },
                        supportedNetworks: ['amex', 'masterCard', 'visa' ],
                        merchantCapabilities: [ 'supports3DS', 'supportsEMV', 'supportsCredit', 'supportsDebit' ]
                    }

                    let session = new ApplePaySession(1,paymentRequest);

                    //Merchant Validation
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
                            xhr.open('GET',appleSettings.host.iwd_checkout_app_url + 'spreedly/apple-pay/validation?host='+window.location.host+'&u=' + valURL);
                            xhr.send();
                        });
                    }

                    session.onshippingcontactselected = function(event) {
                        appleSettings.postalAddress = event.shippingContact;
                        let status = ApplePaySession.STATUS_SUCCESS,applePayQuoteConfig = {};

                        if(typeof window.checkoutData != "object"){
                            window.checkoutData = {};
                        }

                        $.ajax({
                            dataType : "json",
                            method: "POST",
                            url: appleSettings.host.iwd_checkout_app_url + "spreedly/apple-pay/mini-cart/delivery",
                            data: appleSettings,
                        }).done(function (response) {
                            appleSettings.shippingMethod = response.newShippingMethods[0];
                            applePayQuoteConfig.newShippingMethods = response.newShippingMethods;
                            applePayQuoteConfig.newTotal = response.newTotal;
                            applePayQuoteConfig.newLineItems = response.newLineItems;
                            window.checkoutData.applePayQuoteConfig = applePayQuoteConfig;
                            session.completeShippingContactSelection(status,applePayQuoteConfig.newShippingMethods,applePayQuoteConfig.newTotal,applePayQuoteConfig.newLineItems);
                        }).fail(function (error) {
                            console.log(error);
                        });
                    }

                    session.onshippingmethodselected = function(event) {
                        appleSettings.shippingMethod = event.shippingMethod;
                        let applePayQuoteConfig = window.checkoutData.applePayQuoteConfig,
                            newTotal = 0,
                            shippingMethod = event.shippingMethod;

                        applePayQuoteConfig.newLineItems.forEach(function (item,index) {
                            if(item.label == 'Shipping'){
                                applePayQuoteConfig.newLineItems[index].amount = parseFloat(shippingMethod.amount);
                            }
                            if(item.label == 'Discount'){
                                newTotal = parseFloat(newTotal) - parseFloat(applePayQuoteConfig.newLineItems[index].amount);
                            }else{
                                newTotal = parseFloat(newTotal) + parseFloat(applePayQuoteConfig.newLineItems[index].amount);
                            }
                        });

                        applePayQuoteConfig.newTotal.amount = newTotal.toFixed(2);

                        session.completeShippingMethodSelection(status,applePayQuoteConfig.newTotal,applePayQuoteConfig.newLineItems);


                    }

                    session.onpaymentmethodselected = function(event) {
                        let applePayQuoteConfig = window.checkoutData.applePayQuoteConfig;
                        session.completePaymentMethodSelection(applePayQuoteConfig.newTotal,applePayQuoteConfig.newLineItems);
                    }

                    session.onpaymentauthorized = function (event) {
                        appleSettings.payment = event.payment;
                        let promise = sendPaymentToken(event.payment.token);
                        promise.then(function (success) {
                            let status;
                            if (success){
                                $.ajax({
                                    dataType : "json",
                                    method: "POST",
                                    url: appleSettings.host.iwd_checkout_app_url + "spreedly/apple-pay/mini-cart/purchase",
                                    data: appleSettings,
                                }).done(function (response) {
                                    console.log(response);
                                    if(!response.order_id){
                                        $('#applePayLoader').trigger('processStop');
                                        return false;
                                    }
                                    let successUrl = window.location.origin + '/iwd_checkout/index/success',
                                        successParams = 'order_id=' + response.order_id
                                            + '&order_increment_id=' + response.order_increment_id
                                            + '&order_status=' + response.order_status
                                            + '&quote_id=' + response.quote_id;

                                    window.location.href = successUrl+'?'+successParams;
                                }).fail(function (error) {
                                    $('#applePayLoader').trigger('processStop');
                                    console.log(error);
                                });
                                status = ApplePaySession.STATUS_SUCCESS;
                            } else {
                                status = ApplePaySession.STATUS_FAILURE;
                            }
                            $('#applePayLoader').trigger('processStart');
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

                    session.oncancel = function(event) {}
                    session.begin();
                })
            },
        });
    }
);
