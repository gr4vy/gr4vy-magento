/*jshint browser:true jquery:true*/
define([
    'mage/utils/wrapper',
    'Gr4vy_Magento/js/model/config',
    'Magento_Checkout/js/model/full-screen-loader'
], function (wrapper, config, loader) {
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

            return originalGetPaymentInformation().then(function () {
                loader.stopLoader();
            });
        });
    };
});
