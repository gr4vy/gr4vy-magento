define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'gr4vy',
                component: 'Gr4vy_Payment/js/view/payment/method-renderer/gr4vy-method'
            }
        );
        return Component.extend({});
    }
);