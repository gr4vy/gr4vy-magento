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
                                        console.log(data)
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
                                             This.cancelCustomOrder(This.orderId);
                                         }
                                    }
                                     if (
                                         eventName === 'transactionFailed'
                                         || eventName === 'apiError'
                                         || eventName === 'argumentError'
                                     ) {
                                         This.cancelCustomOrder(This.orderId);
                                         fullScreenLoader.stopLoader();
                                         This.initEmbedPayment();
                                         console.log(data)
                                    }
                                },
                                onBeforeTransaction: async () => {
                                    try {
                                        await This.customPlaceOrder();
                                        console.log('onBeforeTransaction updated with fix');
                                        return {
                                            externalIdentifier: This.incrementId,
                                        };
                                    } catch (error) {
                                        fullScreenLoader.stopLoader();
                                    }
                                },
                                onComplete: (transaction) => {
                                    // send api requests to transaction web api
                                    var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/set-payment-information', {});
                                    //console.log(transaction);
                                    var payload = {
                                        cartId: quote.getQuoteId(),
                                        paymentMethod: This.getPaymentMethodData(transaction.paymentMethod),
                                        methodData: This.getGr4vyPaymentMethodData(transaction.paymentMethod),
                                        serviceData: This.getGr4vyPaymentServiceData(transaction.paymentService),
                                        transactionData: This.getGr4vyTransactionData(transaction)
                                    };
                                    return storage.post(
                                        serviceUrl,
                                        JSON.stringify(payload)
                                    ).done(
                                        function (response) {
                                            This.processGr4vyResponse();
                                            window.location.replace(url.build(
                                                config.successPageUrl
                                            ));

                                        }
                                    ).fail(
                                        function (response) {
                                            errorProcessor.process(response);
                                            This.displayMessage(response);
                                            fullScreenLoader.stopLoader(true);
                                        }
                                    );
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
             * Place order in Magento before gr4vy transaction
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
                    }
                });
            },
            /**
             * @returns {Object}
             */
            getPaymentMethodData: function (payment,service) {
                // bypass error when there is no expirationDate
                var expirationDate = payment.expirationDate ?? '12/25';
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_type': payment.scheme,
                        'cc_exp_year': expirationDate.substr(-2),
                        'cc_exp_month': expirationDate.substr(0,2),
                        'cc_last_4': payment.label
                    },
                };

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyTransactionData: function (transaction) {
                var data = {
                    method_id: transaction.paymentMethod.id,
                    buyer_id: transaction.buyer.id,
                    service_id: transaction.paymentService.id,
                    status: transaction.status,
                    amount: transaction.amount,
                    captured_amount: transaction.capturedAmount,
                    refunded_amount: transaction.refundedAmount,
                    currency: transaction.currency,
                    gr4vy_transaction_id: transaction.id,
                    environment: transaction.environment
                }

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyPaymentMethodData: function (payment) {
                var data = {
                    method_id: payment.id,
                    method: payment.method,
                    label: payment.label,
                    scheme: payment.scheme,
                    external_identifier: payment.externalIdentifier,
                    expiration_date: payment.expirationDate,
                    approval_url: payment.approvalUrlc
                };

                return data;
            },
            /**
             * @returns {Object}
             */
            getGr4vyPaymentServiceData: function (service) {
                var data = {
                    service_id: service.id,
                    method: service.method,
                    payment_service_definition_id: service.payment_service_definition_id,
                    display_name: service.type
                };

                return data;
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
