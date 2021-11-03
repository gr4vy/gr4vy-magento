define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'mage/translate',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert'
    ],
    function (Component, quote, urlBuilder, storage, url, $t, errorProcessor, customer, globalMessageList, fullScreenLoader, alertModal) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Gr4vy_Magento/payment/gr4vy'
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
            initEmbedPayment: function () {
                var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/get-embed-token', {});
                var payload = { cartId: quote.getQuoteId() };
                var This = this;
                storage.post( serviceUrl, JSON.stringify(payload)).done(
                    function (response) {
                        var embed_token = response[0];
                        var amount = response[1];
                        var buyer_id = response[2];
                        gr4vy.setup({
                            gr4vyId: window.checkoutConfig.payment.gr4vy.gr4vy_id,
                            buyerId: buyer_id,
                            environment: window.checkoutConfig.payment.gr4vy.environment,
                            element: ".container",
                            form: "#co-payment-form",
                            amount: parseInt(parseFloat(amount)*100),
                            currency: window.checkoutConfig.quoteData.quote_currency_code,
                            country: window.checkoutConfig.originCountryCode,
                            token: embed_token,
                            intent: window.checkoutConfig.payment.gr4vy.intent,
                            onEvent: (eventName, data) => {
                                if (eventName === 'agumentError') {
                                    console.log(data)
                                }
                                if (eventName === 'transactionCreated') {
                                    console.log(data)
                                }
                                if (eventName === 'transactionFailed') {
                                    console.log(data)
                                }
                                if (eventName === 'apiError') {
                                    console.log(data)
                                }
                            },
                            onComplete: (transaction) => {
                                // send api requests to transaction web api
                                var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/set-payment-information', {});
                                console.log(transaction);
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
                                        // success - trigger default placeorder request from magento library
                                        This.placeOrder();
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
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader(true);
                    }
                );

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
            /**
             * @returns {String}
             */
            getCode: function () {
                return 'gr4vy';
            },
        });
    }
);
