define(
    [
        'Magento_Checkout/js/view/payment/default',
        'FreePay_Gateway/js/action/redirect-on-success'
    ],
    function (Component, freepayRedirect) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'FreePay_Gateway/payment/form',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,

            /**
             * @return {exports}
             */
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },

            /**
             * @return {*}
             */
            isPaymentReady: function () {
                return this.paymentReady();
            },

            getCode: function() {
                return 'freepay_gateway';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                };
            },
            afterPlaceOrder: function() {
                freepayRedirect.execute();
            },
            getPaymentLogo: function () {
                return window.checkoutConfig.payment.freepay_gateway.paymentLogo;
            },
        });
    }
);