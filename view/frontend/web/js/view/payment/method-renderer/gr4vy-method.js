define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Gr4vy_Magento/js/model/config',
        'mage/storage',
        'mage/url',
        'mage/translate',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'jquery',
        'gr4vyapi'
    ],
    function (
        Component,
        quote,
        urlBuilder,
        config,
        storage,
        url,
        $t,
        errorProcessor,
        customer,
        customerData,
        globalMessageList,
        setPaymentInformationAction,
        fullScreenLoader,
        alertModal,
        $,
        gr4vy
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Gr4vy_Magento/payment/gr4vy',
                orderId: null,
                incrementId: null
            },
            displayMessage: function(msg) {
                alertModal({
                    title: 'Error',
                    content: msg,
                    actions: {
                        always: function(){
                            window.scrollTo(0,0);
                            globalMessageList.addErrorMessage({
                                message: $t(msg)
                            });
                        }
                    }
                });
            },
            initialize: function() {
                var self = this;

                this._super();

                if (window.checkoutConfig.payment.gr4vy.rendered) {
                    self.initEmbedPayment();
                }
            },
            initEmbedPayment: function () {
                var This = this;
                this.lockBasket();
                // monitor & refresh Gr4vy embed config using ko.observable
                config.reloadEmbed();
                storage.post(config.reloadConfigUrl, JSON.stringify({}), false, 'application/json')
                    .done(function (result) {
                        var config2 = result.payment.gr4vy;

                        // Verify data before setting gr4vy
                        if (config2.token && config2.total_amount && config2.buyer_id) {
                            //bind click event first to place order before triggering gr4vy event
                            gr4vy.setup({
                                gr4vyId: config.gr4vyId,
                                buyerId: config2.buyer_id,
                                environment: config.environment,
                                store: config.store,
                                element: config.element,
                                form: config.form,
                                amount: config2.total_amount,
                                currency: config.currency,
                                country: "AU",
                                locale: config.locale,
                                paymentSource: config.paymentSource,
                                requireSecurityCode: config.requireSecurityCode,
                                theme: config.theme,
                                statementDescriptor: config.statementDescriptor,
                                token: config2.token,
                                intent: config2.intent,
                                cartItems: config2.items,
                                metadata: config.metadata,
                                onEvent: (eventName, data) => {
                                    if (
                                        eventName === 'transactionFailed'
                                        || eventName === 'apiError'
                                        || eventName === 'argumentError'
                                        || eventName === 'transactionCancelled'
                                    ) {
                                        This.processGr4vyOrder(data);
                                    }
                                },
                                onBeforeTransaction: async () => {
                                    fullScreenLoader.startLoader();
                                    console.log('onBeforeTransaction');
                                    return {
                                        externalIdentifier: This.incrementId,
                                    }
                                },
                                onComplete: (transaction) => {
                                    This.processGr4vyOrder(transaction);
                                }
                            });
                        }
                        else {
                            var address_collection = document.querySelectorAll('.gr4vy-payment-method .payment-method-billing-address');
                            address_collection[0].style.display = 'none';

                            var button_collection = document.querySelectorAll('.gr4vy-payment-method .gr4vy-actions-toolbar');
                            button_collection[0].style.display = 'none';

                            var placeholder_collection = document.getElementsByClassName('gr4vy-placeholder');
                            placeholder_collection[0].innerHTML += $t('<span class="gr4vy-checkout-notice">Payment method is not available. Please contact us for support</span>');
                            placeholder_collection[0].style.display = 'block';
                        }

                        // mark payment form rendered once after pageload
                        window.checkoutConfig.payment.gr4vy.rendered = true;
                    });
            },
            /**
             * Lock the basket before initiating transaction
             */
            lockBasket: function () {
                var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/lock-basket', {});
                var payload = {
                    cartId: quote.getQuoteId()
                };
                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        if (response) {
                            console.log("Basket is locked");
                        } else {
                            console.log("Basket is NOT locked");
                        }
                    }
                ).fail(
                    function (response) {
                    }
                );
            },
            /**
             * Process Gr4vy Transaction and Place an Order in Magento
             */
            processGr4vyOrder: function (transaction) {
                var self = this;
                var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/process-gr4vy-transaction', {});
                var payload = {
                    cartId: quote.getQuoteId(),
                    transactionId: transaction.id,
                    paymentMethod: this.getPaymentMethodData(),
                    methodData: this.getGr4vyPaymentMethodData(),
                    serviceData: this.getGr4vyPaymentServiceData(),
                    transactionData: this.getGr4vyTransactionData()
                };
                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (data) {
                        var response = $.parseJSON(data);
                        if (response && response.success) {
                            self.customPlaceOrder();
                        }
                        else {
                            fullScreenLoader.stopLoader(true);
                        }
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        This.displayMessage(response);
                        fullScreenLoader.stopLoader(true);
                    }
                );
            },
            /**
             * Place order in Magento
             */
            customPlaceOrder: function () {
                var self = this;
                fullScreenLoader.startLoader();
                $.ajaxSetup({
                    async: false
                });
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function() {
                            fullScreenLoader.stopLoader();
                        })
                    .done(
                        function(orderId) {
                            self.orderId = orderId;
                            self.setIncrementId(orderId);
                            self.afterPlaceOrder();
                            fullScreenLoader.stopLoader();
                            window.location.replace(url.build(
                                config.successPageUrl
                            ));
                        });
                },
            /**
             * get and set increment id of the last placed order
             */
            setIncrementId: function (lastOrderId) {
                var self = this;
                let params = {orderId: lastOrderId};
                $.ajax({
                    url: BASE_URL + 'gr4vy/checkout/orderdetails',
                    type: 'POST',
                    data: params,
                    async: false,
                    success: function (data) {
                        self.incrementId = data.incrementId;
                    },
                    fail: function (data) {
                    }
                });
            },
            /**
             * Cancel order in Magento if gr4vy transaction fails
             */
            cancelCustomOrder: function (lastOrderId) {
                let formKeyVal = $('input[name="form_key"]').val();
                let params = {orderId: lastOrderId, form_key: formKeyVal};
                $.ajax({
                    url: BASE_URL + 'gr4vy/checkout/cancelorder',
                    type: 'POST',
                    data: params,
                    async: false,
                    success: function (data) {
                    }
                });
            },
            /**
             * Process gr4vy response after successful gr4vy transaction
             */
            processGr4vyResponse: function() {
                let params = {orderId: this.orderId};
                $.ajax({
                    url: BASE_URL + 'gr4vy/checkout/process',
                    type: 'POST',
                    data: params,
                    success: function (data) {
                        if (data && data.success) {
                            window.location.replace(url.build(
                                config.successPageUrl
                            ));    
                        }
                        else {
                            window.location.replace(url.build(
                                config.successPageUrl.replace("success", "failure")
                            ));
                        }
                    },
                    error: function (data, textStatus, errorThrown) {
                        window.location.replace(url.build(
                            config.successPageUrl.replace("success", "failure")
                        ));
                    }
                });
            },
            /**
             * @returns {Object}
             */
            getPaymentMethodData: function () {
                // Data will be set on the server side
                return {
                    'method': "",
                    'additional_data': {
                        'cc_type': "",
                        'cc_exp_year': "",
                        'cc_exp_month': "",
                        'cc_last_4': ""
                    },
                };
            },
            /**
             * @returns {Object}
             */
            getGr4vyTransactionData: function () {
                // Data will be set on the server side
                return {
                    method_id: "",
                    buyer_id: "",
                    service_id: "",
                    status: "",
                    amount: "",
                    captured_amount: "",
                    refunded_amount: "",
                    currency: "",
                    gr4vy_transaction_id: "",
                    environment: ""
                }
            },
            /**
             * @returns {Object}
             */
            getGr4vyPaymentMethodData: function () {
                // Data will be set on the server side
                return {
                    method_id: "",
                    method: "",
                    label: "",
                    scheme: "",
                    external_identifier: "",
                    expiration_date: "",
                    approval_url: ""
                };

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyPaymentServiceData: function () {
                // Data will be set on the server side
                return {
                        service_id: '',
                        method: '',
                        payment_service_definition_id: '',
                        display_name: ''
                }
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.gr4vy.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.gr4vy.description;
            },
            getTitle: function() {
                return window.checkoutConfig.payment.gr4vy.title;
            },
            /**
             * @returns {String}
             */
            getCode: function () {
                return 'gr4vy';
            },
        });
    }
);
