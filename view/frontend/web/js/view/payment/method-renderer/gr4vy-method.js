define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
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
                        if (eventName === 'transactionCreated') {
                            console.log(data.id)
                        }
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
