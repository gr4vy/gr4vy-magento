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
                    element: ".container",
                    form: "#co-payment-form",
                    amount: window.checkoutConfig.quoteData.grand_total,
                    currency: window.checkoutConfig.quoteData.quote_currency_code,
                    country: window.checkoutConfig.originCountryCode,
                    token: window.checkoutConfig.payment.gr4vy.token,
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
