define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, quote, urlBuilder, storage, url, errorProcessor, customer, fullScreenLoader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Gr4vy_Payment/payment/gr4vy'
            },
            initEmbedPayment: function () {
                // initialize embed checkout form
                gr4vy.setup({
                    gr4vyId: window.checkoutConfig.payment.gr4vy.gr4vy_id,
                    buyerId: window.checkoutConfig.payment.gr4vy.buyer_id,
                    environment: window.checkoutConfig.payment.gr4vy.environment,
                    element: ".container",
                    form: "#co-payment-form",
                    amount: parseInt(parseFloat(window.checkoutConfig.quoteData.grand_total)*100),
                    currency: window.checkoutConfig.quoteData.quote_currency_code,
                    country: window.checkoutConfig.originCountryCode,
                    token: window.checkoutConfig.payment.gr4vy.token,
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
                        var This = this;
                        // send api requests to transaction web api
                        var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/set-payment-information', {});
                        console.log(transaction);
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
                                // success - trigger default placeorder request from magento library
                                //console.log(response);
                                This.placeOrder();
                            }
                        ).fail(
                            function (response) {
                                errorProcessor.process(response);
                                fullScreenLoader.stopLoader(true);
                            }
                        );
                    }
                });
            },
            /**
             * @returns {Object}
             */
            getPaymentMethodData: function (payment,service) {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_type': payment.scheme,
                        'cc_exp_year': payment.expirationDate.substr(-2),
                        'cc_exp_month': payment.expirationDate.substr(0,2),
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
