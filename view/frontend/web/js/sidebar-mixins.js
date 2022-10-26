define( [
    'jquery',
    'Magento_Checkout/js/action/get-payment-information'
], function ($, getPaymentInformationAction) {
    'use strict';

    var mixin = {
        _updateItemQtyAfter: function (elem) {
            this._super(elem);

            getPaymentInformationAction();
        },

        _removeItemAfter: function (elem) {
            this._super(elem);

            getPaymentInformationAction();
        },
    };

    return function(target) {
        return $.widget('mage.sidebar', target, mixin );
    }
});
