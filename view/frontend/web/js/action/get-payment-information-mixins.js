/*jshint browser:true jquery:true*/
define([
    'mage/utils/wrapper',
    'mage/storage',
    'Magento_Checkout/js/model/payment/renderer-list',
    'Magento_Checkout/js/model/quote',
    'Gr4vy_Magento/js/model/config',
    'Magento_Checkout/js/model/full-screen-loader',
    'jquery'
], function (wrapper, storage, renderer, quote, config, loader, $) {
    'use strict';

    /**
     * Prevent the customer to Place Order after adding/removing a coupon,
     * giftcard, rewards points, etc.. as it affects order totals
     */
    return function (gr4vyGetPaymentInformation) {
        return wrapper.wrap(gr4vyGetPaymentInformation, function (originalGetPaymentInformation) {
            if (!config.enabled) {
                return originalGetPaymentInformation();
            }

            if (config.hasErrors()) {
                return originalGetPaymentInformation();
            }

            loader.startLoader();

            if (quote.isVirtual()) {
                var ajax_params = {
                    billing_country_id: quote.billingAddress().countryId,
                    billing_company: quote.billingAddress().company
                };
            }
            else {
                var ajax_params = {
                    shipping_country_id: quote.shippingAddress().countryId,
                    shipping_company: quote.shippingAddress().company
                };
            }

            storage.post(config.reloadConfigUrl, JSON.stringify(ajax_params), false, 'application/json')
                .done(function (result) {
                    var removeEntries = [];

                    // Removing Gr4vy
                    renderer.each(function (value, index) {
                        if (value.type.startsWith('gr4vy')) {
                            removeEntries.push(value);
                        }
                    });

                    $.each(removeEntries, function (index, entry) {
                        renderer.remove(entry);
                    })

                    // Reinit Gr4vy Payment
                    $.each(result['payment'], function (index, entry) {
                        if (index.startsWith('gr4vy')) {
                            window.checkoutConfig.payment.gr4vy = entry;
                            config.token(entry.token);
                            config.amount(entry.total_amount);
                            config.cartItems(entry.items);
                            config.intent(entry.intent);

                            renderer.push({
                                type: index,
                                component: 'Gr4vy_Magento/js/view/payment/method-renderer/gr4vy-method'
                            });
                        }
                    });
                });

            return originalGetPaymentInformation().then(function () {
                loader.stopLoader();
            });
        });
    };
});
