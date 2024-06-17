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
                                country: config.country,
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
                                     if (eventName === 'transactionCreated') {
                                        if (
                                             (
                                             data['intent']  === 'authorize'
                                             && data['status'] !== 'authorization_succeeded'
                                             ) ||
                                             (
                                                 data['intent']  === 'capture'
                                                 && data['status'] !== 'capture_succeeded'
                                             )
                                        ) {
                                            var payload = {
                                                cartId: quote.getQuoteId(),
                                                paymentMethod: This.getPaymentMethodData(data.paymentMethod),
                                                methodData: This.getGr4vyPaymentMethodData(data.paymentMethod),
                                                serviceData: This.getGr4vyPaymentServiceData(data.paymentService),
                                                transactionData: This.getGr4vyTransactionData(data)
                                            };
                                            This.saveFailedOrder(payload);            
                                        }
                                    }
                                    if (
                                         eventName === 'transactionFailed'
                                         || eventName === 'apiError'
                                         || eventName === 'argumentError'
                                         || eventName === 'transactionCancelled'
                                    ) {
                                        var payload = {
                                            cartId: quote.getQuoteId(),
                                            paymentMethod: This.getPaymentMethodData(data.paymentMethod),
                                            methodData: This.getGr4vyPaymentMethodData(data.paymentMethod),
                                            serviceData: This.getGr4vyPaymentServiceData(data.paymentService),
                                            transactionData: This.getGr4vyTransactionData(data)
                                        };
                                        This.saveFailedOrder(payload);            
                                        fullScreenLoader.stopLoader();
                                        // This.initEmbedPayment();
                                    }
                                },
                                onBeforeTransaction: async () => {
                                    fullScreenLoader.startLoader();
                                    return {
                                        externalIdentifier: This.incrementId,
                                    }
                                },
                                onComplete: (transaction) => {
                                    This.finishGr4vyTransaction(transaction);
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
             * Place order in Magento
             */
            customPlaceOrder: function () {
                var self = this;
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
                            self.processGr4vyResponse();
                            fullScreenLoader.stopLoader();
                        });
            },
            saveFailedOrder: function (payload) {
                var self = this;
                var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/save-failed-order', {});
                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        window.location.replace(url.build(
                            config.successPageUrl.replace("success", "failure")
                        ));
                    }
                ).fail(
                    function (response) {
                        window.location.replace(url.build(
                            config.successPageUrl.replace("success", "failure")
                        ));
                    }
                );
            },
            finishGr4vyTransaction: function (transaction) {
                this.transaction = transaction;
                var self = this;
                var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/set-payment-information', {});
                var payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: this.getPaymentMethodData(transaction.paymentMethod),
                    methodData: this.getGr4vyPaymentMethodData(transaction.paymentMethod),
                    serviceData: this.getGr4vyPaymentServiceData(transaction.paymentService),
                    transactionData: this.getGr4vyTransactionData(transaction)
                };
                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        if (self.transaction.status == "processing" || 
                            self.transaction.status == "capture_pending" || 
                            self.transaction.status == "authorization_succeeded" || 
                            self.transaction.status == "buyer_approval_pending" ||
                            self.transaction.status == "capture_succeeded"
                        ) {
                            self.customPlaceOrder();
                        } else {
                            self.saveFailedOrder(payload);
                        }
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        self.displayMessage(response);
                        fullScreenLoader.stopLoader(true);
                    }
                );
            },            /**
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
            getPaymentMethodData: function (payment,service) {
                // bypass error when there is no expirationDate
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_type': "",
                        'cc_exp_year': "",
                        'cc_exp_month': "",
                        'cc_last_4': ""
                    },
                };
                if (payment) {
                    var expirationDate = payment.expirationDate ?? '12/25';
                    data = {
                        'method': this.getCode(),
                        'additional_data': {
                            'cc_type': payment.scheme,
                            'cc_exp_year': expirationDate.substr(-2),
                            'cc_exp_month': expirationDate.substr(0,2),
                            'cc_last_4': payment.label
                        },
                    };
                }

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyTransactionData: function (transaction) {
                var data = {
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

                if (transaction) {
                    data.status = transaction.status;
                    data.amount = transaction.amount;
                    data.captured_amount = transaction.capturedAmount;
                    data.refunded_amount = transaction.refundedAmount;
                    data.currency = transaction.currency;
                    data.gr4vy_transaction_id = transaction.id;
                    data.environment = transaction.environment;

                    if(transaction.paymentMethod) {
                        data.method_id = transaction.paymentMethod.id;
                    }

                    if(transaction.buyer) {
                        data.buyer_id = transaction.buyer.id;
                    }

                    if(transaction.paymentService) {
                        data.service_id = transaction.paymentService.id;
                    }
                }

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyPaymentMethodData: function (payment) {
                var data = {
                    method_id: "",
                    method: "",
                    label: "",
                    scheme: "",
                    external_identifier: "",
                    expiration_date: "",
                    approval_url: ""
                };

                if (payment) {
                    data = {
                        method_id: payment.id,
                        method: payment.method,
                        label: payment.label,
                        scheme: payment.scheme,
                        external_identifier: payment.externalIdentifier,
                        expiration_date: payment.expirationDate,
                        approval_url: payment.approvalUrlc
                    };
                }

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyPaymentServiceData: function (service) {
                if (service) {
                    var data = {
                        service_id: service.id,
                        method: service.method,
                        payment_service_definition_id: service.payment_service_definition_id,
                        display_name: service.type
                    };

                    return data;
                } else {
                    return {
                        service_id: '',
                        method: '',
                        payment_service_definition_id: '',
                        display_name: ''
                    }
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
