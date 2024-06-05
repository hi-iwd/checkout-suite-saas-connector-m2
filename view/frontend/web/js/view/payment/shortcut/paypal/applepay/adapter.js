define(
    [
        'jquery',
        'IWD_CheckoutConnector/js/view/payment/shortcut/paypal/applepay/helper',
        'mage/storage',
        'mage/translate',
    ],
    function (
        $,
        helper,
        storage,
        $t
    ) {
        'use strict';

        return {
            /**
             * Configuration object for the application.
             * @type {null}
             */
            config: null,
            session: null,
            paymentData: null,
            shippingAddress: null,
            billingAddress: null,
            shippingMethodCode: null,
            checkoutData: null,
            checkoutType: 'express',
            shippingMethods: {},
            containers: [],
            actions: {
                renderAction: 'PayPalApplePayRenderAction',
                requestRendering: 'PayPalApplePayRequestRendering',
                triggerExpress: 'PayPalApplePayTriggerExpress',
                triggerPlaceOrder: 'PayPalApplePayTriggerPlaceOrder'
            },

            /**
             * Initializes the Apple Pay logic
             */
            init: function () {
                if (!window.ApplePaySession) {
                    console.warn('This device does not support Apple Pay');
                    return;
                }

                if (!ApplePaySession.canMakePayments()) {
                    console.warn('This device is not capable of making Apple Pay payments');
                    return;
                }

                this.config.isCheckoutPage ? this.initCheckoutPagePostMessageListeners() : this.fetchConfiguration();
            },

            /**
             * Fetches the Apple Pay configuration and performs necessary actions based on the configuration.
             * This function is intended to be used within an object with a "config" property
             * and "triggerCheckoutPageButtonsRendering" and "renderWebsiteExpressPaymentButton" methods.
             *
             * @function fetchConfiguration
             * @returns {void}
             */
            fetchConfiguration: async function () {
                try {
                    const applepayConfig = await window.paypal.Applepay().config();

                    if (!applepayConfig.isEligible) return;

                    this.paymentData = applepayConfig;

                    this.config.isCheckoutPage ? this.triggerCheckoutPageButtonsRendering() : this.renderWebsiteExpressPaymentButton();

                } catch (applepayConfigError) {
                    console.error('Error while fetching Apple Pay configuration.', applepayConfigError);
                }
            },

            /**
             * Triggers the rendering of the payment buttons on the checkout page.
             *
             * @function triggerCheckoutPageButtonsRendering
             */
            triggerCheckoutPageButtonsRendering: function() {
                document.getElementById(this.config.checkoutIframeId).contentWindow.postMessage({
                    action: this.actions.renderAction
                }, '*');
            },

            /**
             * Initializes post message listeners for the checkout page.
             */
            initCheckoutPagePostMessageListeners: function () {
                window.addEventListener('message', event => {
                    const action = event.data.action;

                    switch (action) {
                        case this.actions.requestRendering:
                            this.fetchConfiguration();
                            break;
                        case this.actions.triggerExpress:
                            this.checkoutType = 'express';

                            this.startCheckoutProcess();
                            break;
                        case this.actions.triggerPlaceOrder:
                            this.checkoutType = 'default';
                            this.checkoutData = JSON.parse(event.data.checkoutData);
                            this.config.grandTotalAmount = event.data.grandTotal

                            this.startCheckoutProcess();
                            break;
                    }
                }, false);
            },

            /**
             * Renders the Apple Pay button.
             *
             * @function renderButton
             */
            renderWebsiteExpressPaymentButton: function() {
                let buttonClass = 'dominate-paypal-applepay-btn';

                // Insert Apple Pay button into the correct container
                this.containers.forEach(id => {
                    let container = $('#' + id);

                    // Check if container exists or if it already has Apple Pay button inserted
                    if (!container.length || container.find('.' + buttonClass).length > 0) return true;

                    $('<apple-pay-button>').attr({class: buttonClass, type: 'plain'}).on('click', (e) => {
                        this.startCheckoutProcess();
                    }).appendTo(container);

                    // Render only one Apple Pay button per function call
                    // Otherwise Apple Pay will throw an error stating that the payment session is already initiated
                    return false;
                });
            },

            /**
             * Start Checkout Process within the Safari Apple Pay pop-up.
             */
            startCheckoutProcess: async function () {
                if (!this.config.isCheckoutAllowed) {
                    alert($t('Guest checkout is disabled. Please Login or Create an Account.'));
                    return;
                }

                // Get All Available Regions from Magento
                helper.getAvailableRegions();

                try {
                    this.session = new ApplePaySession(4, helper.getPaymentRequest(this.paymentData, this.config, this.checkoutType));

                    this.session.onvalidatemerchant = async (event) => await this.validateMerchant(event);

                    if (this.checkoutType === 'express' && !this.config.isVirtual) {
                        this.session.onshippingcontactselected = async (event) => await this.selectShippingAddress(event);
                        this.session.onshippingmethodselected = async (event) => await this.selectShippingMethod(event);
                    }

                    this.session.onpaymentauthorized = async (event) => await this.placeOrder(event);

                    this.session.begin();
                } catch (error) {
                    console.error('Error initiating Apple Pay session:', error);
                    alert($t('Sorry, but something went wrong.'));
                }
            },

            /**
             * Validates a merchant using Apple Pay API.
             *
             * @param {Object} event - The event object containing the validation URL.
             * @returns {Promise<void>} - A promise that resolves when the merchant validation is complete.
             * @throws {Error} - If there is an error validating the merchant.
             */
            validateMerchant: async function(event) {
                try {
                    const validateResult = await window.paypal.Applepay().validateMerchant({
                        validationUrl: event.validationURL,
                        displayName: this.config.displayName
                    });

                    this.session.completeMerchantValidation(validateResult.merchantSession);
                } catch (validateError) {
                    this.session.abort();
                    console.error('Error validating merchant: ', validateError);
                    alert($t('Sorry, but something went wrong.'));
                }
            },

            /**
             * Constructs API URL based on user session and specific endpoint.
             *
             * @param endpoint The specific API endpoint to append to the base URL.
             * @returns The full API URL as a string.
             */
            getApiUrl: function (endpoint) {
                const baseUrl = `rest/${this.config.storeCode}/V1/`;
                const cartPath = this.config.isLoggedIn ? 'carts/mine/' : `guest-carts/${this.config.maskedQuoteId}/`;

                return `${baseUrl}${cartPath}${endpoint}`;
            },

            /**
             * Prepares API address info based on the context.
             *
             * @param apiType The type of API call being made.
             * @returns The formatted address information for the API call.
             */
            getApiAddressInfo: function (apiType) {
                let addressInfo = {};

                switch (apiType) {
                    case 'estimate-shipping-methods':
                        addressInfo = helper.getEstimateShippingMethodsObject(this.shippingAddress);
                        break;
                    case 'totals-information':
                        addressInfo = helper.getTotalsInfoObject(this.shippingAddress);
                        break;
                    case 'shipping-information':
                        addressInfo = helper.getShippingInfoObject(this.shippingAddress, this.billingAddress);
                        break;
                    case 'billing-address':
                        addressInfo = helper.getBillingInfoObject(this.shippingAddress, this.billingAddress);
                        break;
                }

                if (apiType !== 'estimate-shipping-methods' && this.shippingMethodCode) {
                    let shippingMethod = this.shippingMethods[this.shippingMethodCode];

                    if (shippingMethod) {
                        addressInfo['addressInformation']['shipping_method_code'] = shippingMethod.method_code;
                        addressInfo['addressInformation']['shipping_carrier_code'] = shippingMethod.carrier_code;
                    }
                }

                return JSON.stringify(addressInfo);
            },

            /**
             * Handles selection of a shipping address in the Apple Pay sheet and fetches available shipping methods.
             *
             * @param event The event object containing the shipping contact selected by the user.
             */
            selectShippingAddress: async function (event) {
                this.setShippingAddress(event.shippingContact);

                try {
                    const shippingMethodsResponse = await storage.post(
                        this.getApiUrl('estimate-shipping-methods'),
                        this.getApiAddressInfo('estimate-shipping-methods')
                    );

                    const shippingData = helper.getFormattedShippingMethods(shippingMethodsResponse);
                    const shippingMethods = shippingData.shippingContactSelectionArray;

                    if (shippingMethods.length === 0) {
                        this.session.abort();
                        alert($t('No shipping methods available for the provided address.'));
                        return;
                    }

                    this.shippingMethods = shippingData.shippingMethodsDataObject;
                    this.shippingMethodCode = shippingMethods[0].identifier;

                    await this.updateShippingMethods(shippingMethods);
                } catch (error) {
                    this.session.abort();
                    console.error('Failed to fetch shipping methods:', error);
                    alert($t('Error fetching shipping methods.'));
                }
            },

            /**
             * Updates the Apple Pay session with the available shipping methods.
             *
             * @param shippingMethods Array of shipping methods formatted for Apple Pay.
             */
            updateShippingMethods: async function(shippingMethods) {
                try {
                    // Fetch updated totals based on the selected shipping method
                    const updatedTotalsResponse = await storage.post(
                        this.getApiUrl('totals-information'),
                        this.getApiAddressInfo('totals-information')
                    );

                    this.session.completeShippingContactSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        shippingMethods,
                        {
                            label: this.config.displayName,
                            amount: helper.formatPrice(updatedTotalsResponse.base_grand_total)
                        },
                        helper.getTotalsArray(updatedTotalsResponse)
                    );
                } catch (error) {
                    this.session.abort();
                    console.error('Failed to update shipping methods and totals:', error);
                    alert($t('Error updating shipping methods and totals.'));
                }
            },

            /**
             * Handles selection of a shipping method in the Apple Pay sheet and updates the checkout totals.
             *
             * @param event The event object containing the shipping method selected by the user.
             */
            selectShippingMethod: async function (event) {
                this.shippingMethodCode = event.shippingMethod.identifier;

                try {
                    const totalsResponse = await storage.post(
                        this.getApiUrl('totals-information'),
                        this.getApiAddressInfo('totals-information')
                    );

                    this.session.completeShippingMethodSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        {
                            label: this.config.displayName,
                            amount: helper.formatPrice(totalsResponse.base_grand_total)
                        },
                        helper.getTotalsArray(totalsResponse)
                    );
                } catch (error) {
                    this.session.abort();
                    console.error('Failed to update totals after shipping method selection:', error);
                    alert($t('Error updating checkout totals.'));
                }
            },

            /**
             * Places an order using the given event data.
             *
             * @param {Object} event - The event object containing payment and contact information.
             * @returns {Promise<void>} - A promise that resolves when the order is created.
             */
            placeOrder: async function (event) {
                try {
                    if (this.checkoutType === 'express') {
                        this.setShippingAddress(event.payment.shippingContact);
                        this.setBillingAddress(event.payment.billingContact);

                        const apiMethodType = this.config.isVirtual ? 'billing-address' : 'shipping-information';

                        // Set shipping or billing address
                        await storage.post(this.getApiUrl(apiMethodType), this.getApiAddressInfo(apiMethodType));
                    }

                    // Initiate order creation
                    const createOrderData = await helper.fetchJson(
                        `${this.config.dominateAppUrl}checkout/payment/paypal/order-create`,
                        helper.getOrderCreateRequest(this.config)
                    );

                    if ('status' in createOrderData && createOrderData.status === 'error') {
                        this.handleOrderCreationError(createOrderData);
                        return;
                    }

                    // Confirm order with PayPal
                    await window.paypal.Applepay().confirmOrder({
                        orderId: createOrderData.order_id,
                        token: event.payment.token,
                        billingContact: event.payment.billingContact
                    });

                    // Final approval
                    const approvalData = await helper.fetchJson(
                        `${this.config.dominateAppUrl}checkout/payment/paypal/handle-approve`,
                        helper.getHandleApproveRequest(this.config, createOrderData, this.checkoutData)
                    );

                    this.handleOrderCreationResponse(approvalData);
                } catch (error) {
                    console.error('Order processing error:', error);
                    this.handleOrderCreationError(error);
                }
            },

            /**
             * Handles the response of order creation.
             *
             * @param {Object} response - The response received from the server.
             * @return {void}
             */
            handleOrderCreationResponse: function (response) {
                if (!response || response.error || response.status === 'error') {
                    this.handleOrderCreationError(response);
                    return;
                }

                this.session.completePayment(ApplePaySession.STATUS_SUCCESS);
                helper.redirectToSuccessPage(this.config.successActionUrl, response);
            },

            /**
             * Handles the error that occurs during order creation.
             *
             * @param {Object} error - The error object thrown during order creation.
             *
             * @return {void}
             */
            handleOrderCreationError: function (error) {
                const errorMessage = helper.formatErrorMessage(error.message);

                console.error('Order Creation Error:', errorMessage);
                this.session.completePayment(ApplePaySession.STATUS_FAILURE);
                alert($t(errorMessage));
            },

            /**
             * @param config
             */
            setConfig: function (config) {
                this.config = config;

                this.containers.push(config.containerId);
            },

            /**
             * @param eventAddress
             */
            setShippingAddress: function (eventAddress) {
                this.shippingAddress = helper.getFormattedAddress(eventAddress);
            },

            /**
             * @param eventAddress
             */
            setBillingAddress: function (eventAddress) {
                this.billingAddress = helper.getFormattedAddress(eventAddress);
            },
        };
    }
);
