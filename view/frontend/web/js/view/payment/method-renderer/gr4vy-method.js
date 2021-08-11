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
                    element: ".container",
                    form: "#co-payment-form",
                    amount: parseInt(parseFloat(window.checkoutConfig.quoteData.grand_total)*100),
                    currency: window.checkoutConfig.quoteData.quote_currency_code,
                    country: window.checkoutConfig.originCountryCode,
                    token: window.checkoutConfig.payment.gr4vy.token,
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
                        var serviceUrl = urlBuilder.createUrl('/gr4vy-payment/transaction', {});
                        var transaction_params = {
                            transaction: {
                                method_id: transaction.paymentMethod.id,
                                buyer_id: transaction.buyer.id,
                                service_id: transaction.paymentService.id,
                                status: transaction.status,
                                amount: transaction.amount,
                                captured_amount: transaction.captured_amount,
                                refunded_amount: transaction.refunded_amount,
                                currency: transaction.currency,
                                gr4vy_transaction_id: transaction.id,
                                environment: transaction.environment
                            }
                        };
                        return storage.post(
                            serviceUrl,
                            JSON.stringify(transaction_params)
                        ).done(
                            function (response) {
                                // success - trigger default placeorder request from magento library
                                console.log(response);
                                This.placeOrder();
                            }
                        ).fail(
                            function (response) {
                                console.log(response);
                                errorProcessor.process(response);
                                fullScreenLoader.stopLoader(true);
                            }
                        );
                    }
                });
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.gr4vy.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.gr4vy.description;
            },
        });
    }
);
