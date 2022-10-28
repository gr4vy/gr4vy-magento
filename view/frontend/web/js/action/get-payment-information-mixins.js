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

            var gr4vy_entry = {
                type: 'gr4vy',
                component: 'Gr4vy_Magento/js/view/payment/method-renderer/gr4vy-method'
            };

            renderer.remove(gr4vy_entry);
            renderer.push(gr4vy_entry);
            config.reloadEmbed(new Date().getTime());

            return originalGetPaymentInformation().then(function () {
                loader.stopLoader();
            });
        });
    };
});
