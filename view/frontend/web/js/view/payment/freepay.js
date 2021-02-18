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
                type: 'freepay_gateway',
                component: 'FreePay_Gateway/js/view/payment/method-renderer/freepay'
            }
        );

        return Component.extend({});
    }
);