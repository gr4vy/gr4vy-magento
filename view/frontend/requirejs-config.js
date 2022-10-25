var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/get-payment-information': {
                'Gr4vy_Magento/js/action/get-payment-information-mixins': true
            },
            'Magento_Checkout/js/action/select-shipping-method': {
                'Gr4vy_Magento/js/action/select-shipping-method-mixins': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Gr4vy_Magento/js/action/create-billing-address-mixins': true
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Gr4vy_Magento/js/action/set-billing-address-mixins': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Gr4vy_Magento/js/action/set-shipping-information-mixins': true
            }
        }
    }
};
