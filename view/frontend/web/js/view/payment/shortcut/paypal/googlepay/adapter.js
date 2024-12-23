define(
    [
        'jquery',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/global_helper',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/googlepay/helper',
        'mage/storage',
        'mage/translate',
        'dominateGooglePay',
    ],
    function (
        $,
        globalHelper,
        helper,
        storage,
        $t
    ) {
        'use strict';

        return {
            /**
             * Configuration object.
             *
             * @type {Object}
             * @default null
             */
            config: null,
            paymentData: null,
            shippingAddress: null,
            billingAddress: null,
            chosenShippingMethodCode: null,
            checkoutData: null,
            checkoutType: 'express',
            shippingMethods: {},
            containers: [],
            merchantInfo: null,
            paymentsClient: null,
            allowedPaymentMethods: null,
            baseRequest: {
                apiVersion: 2,
                apiVersionMinor: 0,
            },
            actions: {
                renderAction: 'PayPalGooglePayRenderAction',
                requestRendering: 'PayPalGooglePayRequestRendering',
                triggerExpress: 'PayPalGooglePayTriggerExpress',
                triggerPlaceOrder: 'PayPalGooglePayTriggerPlaceOrder'
            },

            /**
             * Initializes the code to enable website express payment button with Google Pay.
             *
             * @function init
             */
            init: function () {
                if (!window.google || !window.paypal.Googlepay) return;

                this.config.isCheckoutPage ? this.initCheckoutPagePostMessageListeners() : this.setupPayment();
            },

            /**
             * Initializes post message listeners for the checkout page.
             */
            initCheckoutPagePostMessageListeners: function () {
                window.addEventListener('message', event => {
                    const action = event.data.action;

                    switch (action) {
                        case this.actions.requestRendering:
                            this.setupPayment();
                            break;
                        case this.actions.triggerExpress:
                            this.checkoutType = 'express';

                            this.fetchPaymentsClient();
                            this.startCheckoutProcess();
                            break;
                        case this.actions.triggerPlaceOrder:
                            this.checkoutType = 'default';
                            this.checkoutData = JSON.parse(event.data.checkoutData);
                            this.config.grandTotalAmount = event.data.grandTotal

                            this.fetchPaymentsClient();
                            this.startCheckoutProcess();
                            break;
                    }
                }, false);
            },

            /**
             * Fetches the payments client
             *
             * @returns {void}
             */
            setupPayment: function () {
                this.fetchPaymentsClient();
                this.fetchConfiguration().then(() => {
                    this.paymentsClient
                        .isReadyToPay(this.getIsReadyToPayRequest())
                        .then((response) => {
                            if (response.result) {
                                this.config.isCheckoutPage
                                    ? this.triggerCheckoutPageButtonsRendering()
                                    : this.renderWebsiteExpressPaymentButton();
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                        });
                });
            },

            /**
             * Triggers the rendering of the payment buttons on the checkout page.
             *
             * @function triggerCheckoutPageButtonsRendering
             */
            triggerCheckoutPageButtonsRendering: function() {
                document.getElementById(this.config.checkoutIframeId).contentWindow.postMessage({
                    action: this.actions.renderAction,
                }, '*');
            },

            /**
             * Fetches the payments client for Google Pay.
             */
            fetchPaymentsClient: function () {
                if (!this.config.isCheckoutPage && this.paymentsClient) return;

                try {
                    const params = {
                        environment: this.config.paypalEnvironment === 'live' ? 'PRODUCTION' : 'TEST',
                        paymentDataCallbacks: {
                            onPaymentAuthorized: this.onPaymentAuthorized.bind(this),
                        },
                    }

                    if (this.checkoutType === 'express' && !this.config.isVirtual) {
                        params.paymentDataCallbacks.onPaymentDataChanged = this.onPaymentDataChanged.bind(this)
                    }

                    this.paymentsClient = new window.google.payments.api.PaymentsClient(params);
                } catch (paymentsClientError) {
                    console.error('Error while fetching Google Pay client.', paymentsClientError);
                }
            },

            /**
             * Fetches the Google Pay configuration and sets the merchant information and allowed payment methods.
             * @returns {Promise<void>} - A promise that is resolved when the configuration is successfully fetched and set, or rejected with an error.
             */
            fetchConfiguration: async function () {
                if (this.allowedPaymentMethods && this.merchantInfo) return;

                try {
                    const googlePayConfig = await window.paypal.Googlepay().config();

                    this.merchantInfo          = googlePayConfig.merchantInfo;
                    this.allowedPaymentMethods = googlePayConfig.allowedPaymentMethods;
                } catch (configurationError) {
                    console.error('Error while fetching Google Pay configuration.', configurationError);
                }
            },

            /**
             * Retrieves the IsReadyToPay request object.
             *
             * @returns {Object} The IsReadyToPay request object.
             */
            getIsReadyToPayRequest: function() {
                return Object.assign({}, this.baseRequest, {
                    allowedPaymentMethods: this.allowedPaymentMethods,
                });
            },

            /**
             * Renders the Google Pay button on the website.
             *
             * @function renderWebsiteExpressPaymentButton
             * @memberOf [object]
             *
             * @description This function renders the Google Pay button on the website by inserting it into the specified container(s).
             * It also handles the check for existing button and prevents duplicate rendering.
             *
             * @returns {boolean} Returns true if the container does not exist or if the button is already inserted.
             * Returns false if the button is successfully inserted into the container.
             */
            renderWebsiteExpressPaymentButton: function() {
                let buttonClass = 'dominate-paypal-googlepay-btn';

                // Insert Google Pay button into the correct container
                this.containers.forEach(id => {
                    let container = $('#' + id);

                    // Check if container exists or if it already has Apple Pay button inserted
                    if (!container.length || container.find('.' + buttonClass).length > 0) return true;

                    let button = this.paymentsClient.createButton({
                        onClick: this.startCheckoutProcess.bind(this),
                        buttonSizeMode: 'fill',
                    });

                    $(button).addClass(buttonClass);
                    container.append(button);

                    // Render only one Google Pay button per function call
                    // Otherwise Google Pay will throw an error stating that the payment session is already initiated
                    return false;
                });
            },

            /**
             * Starts the checkout process.
             *
             * @returns {Promise<void>} A promise that resolves when the checkout process is started.
             */
            startCheckoutProcess: async function () {
                if (!this.config.isCheckoutAllowed) {
                    alert($t('Guest checkout is disabled. Please Login or Create an Account.'));
                    return;
                }

                // Get All Available Regions from Magento
                globalHelper.getAvailableRegions();

                try {
                    const paymentDataRequest = this.getPaymentDataRequest();

                    this.paymentsClient.loadPaymentData(paymentDataRequest);
                } catch (error) {
                    console.error('Error initiating Google Pay:', error);
                    alert($t('Sorry, but something went wrong.'));
                }
            },

            getPaymentDataRequest: function () {
                const paymentDataRequest = Object.assign({}, this.baseRequest);

                paymentDataRequest.merchantInfo          = this.merchantInfo;
                paymentDataRequest.allowedPaymentMethods = this.allowedPaymentMethods;
                paymentDataRequest.emailRequired         = true;

                if (this.checkoutType === 'default' || this.config.isVirtual) {
                    paymentDataRequest.callbackIntents = ["PAYMENT_AUTHORIZATION"];
                } else {
                    paymentDataRequest.callbackIntents = ["SHIPPING_ADDRESS", "SHIPPING_OPTION", "PAYMENT_AUTHORIZATION"];
                    paymentDataRequest.shippingAddressRequired   = true;
                    paymentDataRequest.shippingOptionRequired    = true;
                    paymentDataRequest.shippingAddressParameters = {
                        allowedCountryCodes: this.config.allowedCountryCodes,
                        phoneNumberRequired: true,
                    };
                }

                paymentDataRequest.transactionInfo = helper.getPaymentRequest(this.config, this.checkoutType);

                return paymentDataRequest;
            },

            /**
             * Handles the authorization of a payment.
             *
             * @returns {Promise} A promise that resolves to an object containing the transaction state.
             */
            onPaymentAuthorized: function (paymentData) {
                return new Promise((resolve, reject) => {
                    this.processPayment(paymentData)
                        .then(function (data) {
                            resolve(data);
                        })
                        .catch(function (error) {
                            console.error('Payment processing error:', error);

                            const message = (typeof error === 'string')
                                ? error
                                : 'There was an error processing your request. Please try again';

                            resolve({transactionState: 'ERROR',
                                error: {
                                    intent: 'PAYMENT_AUTHORIZATION',
                                    message: message,
                                    reason: 'PAYMENT_DATA_INVALID'
                                }}
                            );
                        });
                });
            },

            /**
             * Handles changes in payment data.
             *
             * @param {Object} intermediatePaymentData - The intermediate payment data object.
             * @returns {Promise} A promise resolved with the updated payment data.
             */
            onPaymentDataChanged: function (intermediatePaymentData) {
                return new Promise((resolve, reject) => {
                    // Resolve immediately in case there are no applicable addresses pre-saved in Google Wallet
                    if (intermediatePaymentData.callbackTrigger === "INITIALIZE"
                        && typeof intermediatePaymentData.shippingAddress === 'undefined'
                    ) {
                        resolve({});
                    }

                    this.processUpdate(intermediatePaymentData)
                        .then(function (data) {
                            resolve(data);
                        })
                });
            },

            /**
             * Processes update for an intermediate payment data object.
             * @param {Object} intermediatePaymentData - The intermediate payment data object to process.
             * @returns {Promise} - A promise that resolves to an updated payment data request object.
             */
            processUpdate: async function (intermediatePaymentData) {
                let callbackTrigger          = intermediatePaymentData.callbackTrigger,
                    paymentDataRequestUpdate = {};

                if (callbackTrigger === "INITIALIZE" || callbackTrigger === "SHIPPING_ADDRESS") {
                    const newShippingOptionParameters = await this.selectShippingAddress(intermediatePaymentData);

                    if (newShippingOptionParameters.shippingOptions.length === 0) {
                        paymentDataRequestUpdate.error = {
                            reason: "SHIPPING_ADDRESS_UNSERVICEABLE",
                            message: $t("Cannot ship to the selected address"),
                            intent: "SHIPPING_ADDRESS"
                        };
                    } else {
                        paymentDataRequestUpdate.newShippingOptionParameters = newShippingOptionParameters;
                        paymentDataRequestUpdate.newTransactionInfo          = await this.calculateNewTransactionInfo();
                    }
                } else if (callbackTrigger === "SHIPPING_OPTION") {
                    this.chosenShippingMethodCode = intermediatePaymentData.shippingOptionData.id;

                    paymentDataRequestUpdate.newTransactionInfo = await this.calculateNewTransactionInfo();
                }

                return paymentDataRequestUpdate;
            },

            /**
             * Sets the shipping address for the payment.
             *
             * @param {Object} paymentData - The payment data.
             */
            selectShippingAddress: async function (paymentData) {
               this.setShippingAddress(paymentData);

                try {
                    const shippingMethodsResponse = await storage.post(
                        globalHelper.getApiUrl('estimate-shipping-methods', this.config),
                        globalHelper.getApiAddressInfo('estimate-shipping-methods', this)
                    );

                    const shippingData    = helper.getFormattedShippingMethods(shippingMethodsResponse);
                    const shippingMethods = shippingData.optionsArray;
                    const newShippingOptionParameters = {
                        defaultSelectedOptionId: shippingMethods.length === 0 ? "shipping_option_unselected" : shippingMethods[0].id,
                        shippingOptions: shippingMethods
                    };

                    if (shippingMethods.length > 0) {
                        this.shippingMethods          = shippingData.methodsDataObject;
                        this.chosenShippingMethodCode = shippingMethods[0].id;
                    }

                    return newShippingOptionParameters;
                } catch (error) {
                    console.error('Failed to fetch shipping methods:', error);
                    alert($t('Error fetching shipping methods.'));
                }
            },

            /**
             * Calculates new transaction information based on the selected shipping method.
             * @async
             *
             * @returns {Promise<{
             *   totalPrice: *,
             *   totalPriceLabel: *,
             *   totalPriceStatus: string,
             *   currencyCode: *,
             *   displayItems: [{price: *, label: *, type: string}]
             * }>}
             * Returns a promise that resolves to an object containing the following properties:
             *  - totalPrice: The total price of the transaction.
             *  - totalPriceLabel: The label to display for the total price.
             *  - totalPriceStatus: The status of the total price.
             *  - currencyCode: The currency code of the transaction.
             *  - displayItems: An array of display items, each containing a price, label, and type.
             */
            calculateNewTransactionInfo: async function() {
                try {
                    // Fetch updated totals based on the selected shipping method
                    const updatedTotalsResponse = await storage.post(
                        globalHelper.getApiUrl('totals-information', this.config),
                        globalHelper.getApiAddressInfo('totals-information', this)
                    );

                    return helper.getTotalsArray(updatedTotalsResponse);
                } catch (error) {
                    console.error('Failed to update shipping methods and totals:', error);
                    alert($t('Error updating shipping methods and totals.'));
                }
            },

            /**
             * Process the payment using Google Pay.
             *
             * @param {Object} paymentData - The payment data.
             * @returns {Promise<Object>} - The transaction state.
             */
            processPayment: async function (paymentData) {
                if (this.checkoutType === 'express') {
                    if (!this.config.isVirtual) {
                        this.setShippingAddress(paymentData);
                    }

                    this.setBillingAddress(paymentData);

                    const apiMethodType = this.config.isVirtual ? 'billing-address' : 'shipping-information';

                    // Set shipping and billing addresses
                    await storage.post(
                        globalHelper.getApiUrl(apiMethodType, this.config),
                        globalHelper.getApiAddressInfo(apiMethodType, this)
                    );
                }

                // Initiate order creation
                const createOrderData = await globalHelper.fetchJson(
                    `${this.config.dominateAppUrl}checkout/payment/paypal/order-create`,
                    globalHelper.getOrderCreateRequest(this.config, 'google_pay')
                );

                if ('status' in createOrderData && createOrderData.status === 'error') {
                    throw globalHelper.formatErrorMessage(createOrderData.message);
                }

                let authResult = false;
                const {status} = await window.paypal.Googlepay().confirmOrder({
                    orderId: createOrderData.order_id,
                    paymentMethodData: paymentData.paymentMethodData,
                });

                if (status === "PAYER_ACTION_REQUIRED") {
                    authResult = await window.paypal.Googlepay()
                        .initiatePayerAction({orderId: createOrderData.order_id})
                        .then(async () => {
                            return true;
                        });
                }

                if (status === "APPROVED" || authResult) {
                    // Final approval
                    const approvalData = await globalHelper.fetchJson(
                        `${this.config.dominateAppUrl}checkout/payment/paypal/handle-approve`,
                        globalHelper.getHandleApproveRequest(this.config, createOrderData, this.checkoutData, 'google_pay')
                    );

                    if (!approvalData || approvalData.error || approvalData.status === 'error') {
                        throw globalHelper.formatErrorMessage(approvalData.message);
                    }

                    globalHelper.redirectToSuccessPage(this.config.successActionUrl, approvalData);

                    return {transactionState: "SUCCESS"};
                } else {
                    throw 'The transaction has been declined. Please try again or use another card.';
                }
            },

            /**
             * Sets the configuration for the object.
             *
             * @param {object} config - The configuration object.
             *        The config object contains the following properties:
             *        - containerId: The ID of the container.
             */
            setConfig: function (config) {
                this.config = config;

                this.containers.push(config.containerId);
            },

            /**
             * Sets the shipping address for the payment.
             *
             * @param {Object} paymentData - The payment data object.
             * @param {string} paymentData.email - The email address associated with the payment.
             * @param {Object} paymentData.shippingAddress - The shipping address for the payment.
             */
            setShippingAddress: function (paymentData) {
                const email   = typeof paymentData.email !== 'undefined' ? paymentData.email : '';
                const address = paymentData.shippingAddress;

                this.shippingAddress = helper.getFormattedAddress(email, address);
            },

            /**
             * Sets the billing address for a payment.
             *
             * @param {object} paymentData - The payment data object.
             * @param {string} paymentData.email - The email address associated with the payment.
             * @param {object} paymentData.paymentMethodData - The payment method data.
             * @param {object} paymentData.paymentMethodData.info - Additional information about the payment method.
             * @param {object} paymentData.paymentMethodData.info.billingAddress - The billing address for the payment method.
             * @returns {void}
             */
            setBillingAddress: function (paymentData) {
                const email   = typeof paymentData.email !== 'undefined' ? paymentData.email : '';
                const address = paymentData.paymentMethodData.info.billingAddress;

                this.billingAddress = helper.getFormattedAddress(email, address);
            },
        };
    }
);
